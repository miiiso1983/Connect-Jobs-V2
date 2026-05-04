<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobSeeker;
use App\Models\User;
use App\Models\WhatsAppLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Twilio\Rest\Client;

class WhatsAppNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->input('filter', 'all');
        $province = $request->input('province');
        $search = $request->input('search');

        $query = JobSeeker::with('user')
            ->whereHas('user', fn($q) => $q->where('role', 'jobseeker')
                ->where('status', 'active')
                ->whereNotNull('whatsapp_number')
                ->where('whatsapp_number', '!=', ''));

        // Filter by profile status
        if ($filter === 'incomplete') {
            $query->where('profile_completed', false);
        } elseif ($filter === 'no-cv') {
            $query->where('profile_completed', true)->whereNull('cv_file');
        } elseif ($filter === 'complete') {
            $query->where('profile_completed', true)->whereNotNull('cv_file');
        }

        // Filter by province
        if ($province) {
            $query->where('province', $province);
        }

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('email', 'like', "%{$search}%")
                      ->orWhere('whatsapp_number', 'like', "%{$search}%"));
            });
        }

        $seekers = $query->orderByDesc('id')->paginate(50)->withQueryString();

        // Load latest WhatsApp log for each user
        $userIds = $seekers->pluck('user_id')->toArray();
        $latestLogs = WhatsAppLog::whereIn('user_id', $userIds)
            ->where('status', 'sent')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('user_id')
            ->map(fn($logs) => $logs->first());

        $provinces = \App\Models\MasterSetting::where('setting_type', 'province')->pluck('value');

        // Stats
        $baseQuery = fn() => JobSeeker::whereHas('user', fn($q) => $q->where('role', 'jobseeker')
            ->where('status', 'active')
            ->whereNotNull('whatsapp_number')
            ->where('whatsapp_number', '!=', ''));

        $stats = [
            'total' => $baseQuery()->count(),
            'incomplete' => $baseQuery()->where('profile_completed', false)->count(),
            'no_cv' => $baseQuery()->where('profile_completed', true)->whereNull('cv_file')->count(),
            'complete' => $baseQuery()->where('profile_completed', true)->whereNotNull('cv_file')->count(),
        ];

        $twilioConfigured = config('services.twilio.sid') && config('services.twilio.token') && config('services.twilio.whatsapp_from');

        return view('admin.whatsapp-notifications.index', compact('seekers', 'provinces', 'stats', 'filter', 'province', 'search', 'twilioConfigured', 'latestLogs'));
    }

    public function send(Request $request): RedirectResponse
    {
        $sendMode = $request->input('send_mode', 'template');

        $rules = [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'send_mode' => 'required|in:template,custom',
        ];

        if ($sendMode === 'template') {
            $rules['template'] = 'required|in:profile,cv';
        } else {
            $rules['message'] = 'required|string|min:10|max:2000';
        }

        $request->validate($rules);

        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.whatsapp_from');

        if (!$sid || !$token || !$from) {
            return back()->with('status', '❌ إعدادات Twilio غير مكتملة. تأكد من TWILIO_SID و TWILIO_TOKEN و TWILIO_WHATSAPP_FROM في .env');
        }

        // Resolve content SID for template mode
        $contentSid = null;
        if ($sendMode === 'template') {
            $template = $request->input('template');
            $contentSid = config("services.twilio.content_sid_{$template}");
            if (!$contentSid) {
                return back()->with('status', "❌ قالب \"{$template}\" غير مُعدّ. أضف TWILIO_CONTENT_SID_" . strtoupper($template) . " في .env");
            }
        }

        $client = new Client($sid, $token);
        $users = User::whereIn('id', $request->input('user_ids'))
            ->where('role', 'jobseeker')
            ->whereNotNull('whatsapp_number')
            ->where('whatsapp_number', '!=', '')
            ->with('jobSeeker')
            ->get();

        $profileUrl = url('/jobseeker/profile');
        $sent = 0;
        $failed = 0;
        $errors = [];

        foreach ($users as $user) {
            $to = $this->normalizeMsisdn($user->whatsapp_number);
            if (!$to) {
                $failed++;
                $errors[] = "{$user->email}: رقم غير صالح";
                continue;
            }

            try {
                $payload = ['from' => $from];

                if ($contentSid) {
                    $name = $user->jobSeeker->full_name ?? $user->name;
                    $payload['contentSid'] = $contentSid;
                    $payload['contentVariables'] = json_encode(['1' => $name, '2' => $profileUrl]);
                } else {
                    $payload['body'] = $request->input('message');
                }

                $msg = $client->messages->create("whatsapp:+{$to}", $payload);
                $sent++;

                // Log successful send
                WhatsAppLog::create([
                    'user_id' => $user->id,
                    'phone_number' => $to,
                    'message_type' => $sendMode === 'template' ? ($request->input('template') === 'profile' ? 'profile_update' : 'cv_upload') : 'custom',
                    'template_sid' => $contentSid,
                    'twilio_sid' => $msg->sid,
                    'status' => 'sent',
                ]);

                Log::info('Admin WhatsApp sent', ['admin' => auth()->id(), 'to_user' => $user->id, 'to_number' => $to, 'mode' => $sendMode]);
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = "{$user->email}: {$e->getMessage()}";

                // Log failed send
                WhatsAppLog::create([
                    'user_id' => $user->id,
                    'phone_number' => $to,
                    'message_type' => $sendMode === 'template' ? ($request->input('template') === 'profile' ? 'profile_update' : 'cv_upload') : 'custom',
                    'template_sid' => $contentSid,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);

                Log::error('Admin WhatsApp failed', ['admin' => auth()->id(), 'to_user' => $user->id, 'error' => $e->getMessage()]);
            }
        }

        $msg = "✅ تم الإرسال: {$sent}";
        if ($failed > 0) {
            $msg .= " | ❌ فشل: {$failed}";
            if (count($errors) <= 5) {
                $msg .= "\n" . implode("\n", $errors);
            }
        }

        return back()->with('status', $msg);
    }

    private function normalizeMsisdn(?string $raw): ?string
    {
        if (!$raw) return null;
        $raw = preg_replace('/[^0-9+]/', '', $raw);
        if (str_starts_with($raw, '0')) {
            $raw = ltrim($raw, '0');
            $raw = config('services.whatsapp.country_prefix', '+964') . $raw;
        }
        if (!str_starts_with($raw, '+')) {
            $raw = '+' . $raw;
        }
        return ltrim($raw, '+');
    }
}
