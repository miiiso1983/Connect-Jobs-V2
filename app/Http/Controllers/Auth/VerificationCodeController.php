<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Twilio\Rest\Client;

class VerificationCodeController extends Controller
{
    public function show(): View
    {
        return view('auth.verify-code');
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'channel' => 'required|in:email,whatsapp',
            'whatsapp_number' => 'nullable|string|max:30',
        ]);
        /** @var User $user */
        $user = $request->user();
        if ($user->role !== 'jobseeker') return back()->with('status','التحقق مطلوب لحسابات الباحثين فقط.');

        $code = (string)random_int(100000, 999999);
        $user->update([
            'verification_code' => $code,
            'verification_channel' => $request->channel,
            'verification_expires_at' => now()->addMinutes(15),
            'whatsapp_number' => $request->channel==='whatsapp' ? $request->whatsapp_number : null,
            'status' => 'inactive',
        ]);

        if ($request->channel==='email') {
            // Validate recipient email before sending
            $email = trim((string) ($user->email ?? ''));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return back()->with('status','تعذر الإرسال: بريدك غير صالح. حدّث البريد الإلكتروني من الملف الشخصي أو اختر الواتساب.');
            }
            try {
                Mail::to([$email => $user->name])
                    ->queue(new \App\Mail\VerifyCodeMail([
                        'name' => $user->name,
                        'code' => $code,
                    ]));
            } catch (\Throwable $e) {
                Log::error('Failed to queue VerifyCodeMail: '.$e->getMessage(), ['user_id' => $user->id]);
                return back()->with('status','تعذر إرسال البريد حالياً. جرّب لاحقاً أو اختر الواتساب.');
            }
        } else {
            $driver = config('services.whatsapp.driver', env('WHATSAPP_DRIVER', 'meta'));
            $to = $this->normalizeMsisdn($user->whatsapp_number);
            if ($driver === 'twilio') {
                if (!$to || !config('services.twilio.sid') || !config('services.twilio.token') || !config('services.twilio.whatsapp_from')) {
                    return back()->with('status','تعذر إرسال واتساب (Twilio): إعدادات غير مكتملة أو رقم غير صالح.');
                }
                try {
                    $client = new Client(config('services.twilio.sid'), config('services.twilio.token'));
                    $templateSid = config('services.twilio.content_sid');
                    if ($templateSid) {
                        $client->messages->create('whatsapp:'.$to, [
                            'from' => config('services.twilio.whatsapp_from'),
                            'contentSid' => $templateSid,
                            'contentVariables' => json_encode(['1' => $code, '2' => 15]),
                        ]);
                    } else {
                        $client->messages->create('whatsapp:'.$to, [
                            'from' => config('services.twilio.whatsapp_from'),
                            'body' => "رمز التفعيل: {$code} (صالح لمدة 15 دقيقة)",
                        ]);
                    }
                } catch (\Twilio\Exceptions\RestException $e) {
                    $msg = $e->getMessage();
                    if (str_contains($msg, '24-hour')) {
                        return back()->with('status','أرسل كلمة "مرحبا" إلى واتساب +9647778854530 ثم أعد المحاولة خلال 24 ساعة.');
                    }
                    Log::error('Twilio WhatsApp send failed: '.$msg, ['user_id' => $user->id]);
                    return back()->with('status','تعذر إرسال واتساب عبر Twilio حالياً.');
                } catch (\Throwable $e) {
                    Log::error('Twilio WhatsApp send failed: '.$e->getMessage(), ['user_id' => $user->id]);
                    return back()->with('status','تعذر إرسال واتساب عبر Twilio حالياً.');
                }
            } else {
                $token = config('services.whatsapp.token');
                $phoneId = config('services.whatsapp.phone_id');
                if (!$token || !$phoneId || !$to) {
                    return back()->with('status','تعذر إرسال واتساب (Meta): إعدادات غير مكتملة أو رقم غير صالح.');
                }
                $payload = [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => 'template',
                    'template' => [
                        'name' => 'code_notification',
                        'language' => ['code' => 'ar'],
                        'components' => [[
                            'type' => 'body',
                            'parameters' => [[ 'type' => 'text', 'text' => $code ]]
                        ]]
                    ],
                ];
                $resp = Http::withToken($token)
                    ->post("https://graph.facebook.com/v20.0/{$phoneId}/messages", $payload);
                if (!$resp->successful()) {
                    return back()->with('status','فشل إرسال واتساب: '.$resp->body());
                }
            }
        }

        return back()->with('status','تم إرسال رمز التفعيل. تحقق من البريد/الواتساب.');
    }

    private function normalizeMsisdn(?string $raw): ?string
    {
        if (!$raw) return null;
        $raw = preg_replace('/[^0-9+]/','', $raw);
        if (str_starts_with($raw, '0')) {
            $raw = ltrim($raw, '0');
            $raw = config('services.whatsapp.country_prefix','+964') . $raw;
        }
        if (!str_starts_with($raw, '+')) {
            $raw = '+' . $raw;
        }
        // remove leading + for WA API
        return ltrim($raw, '+');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string|max:10']);
        /** @var User $user */
        $user = $request->user();
        if (!$user->verification_code || !$user->verification_expires_at || now()->greaterThan($user->verification_expires_at)) {
            return back()->with('status','انتهت صلاحية الرمز أو غير موجود. أعد الإرسال.');
        }
        if ($user->verification_code !== $request->code) {
            return back()->with('status','رمز غير صحيح.');
        }
        $user->update([
            'verification_code' => null,
            'verification_expires_at' => null,
            'verification_channel' => null,
            'status' => 'active',
        ]);
        return redirect()->route('dashboard')->with('status','تم تفعيل الحساب بنجاح.');
    }
}

