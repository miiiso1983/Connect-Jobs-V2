<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobSeeker;
use App\Models\User;
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

        return view('admin.whatsapp-notifications.index', compact('seekers', 'provinces', 'stats', 'filter', 'province', 'search', 'twilioConfigured'));
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'message' => 'required|string|min:10|max:2000',
        ]);

        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.whatsapp_from');

        if (!$sid || !$token || !$from) {
            return back()->with('status', '❌ إعدادات Twilio غير مكتملة. تأكد من TWILIO_SID و TWILIO_TOKEN و TWILIO_WHATSAPP_FROM في .env');
        }

        $client = new Client($sid, $token);
        $users = User::whereIn('id', $request->input('user_ids'))
            ->where('role', 'jobseeker')
            ->whereNotNull('whatsapp_number')
            ->where('whatsapp_number', '!=', '')
            ->get();

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
                $client->messages->create("whatsapp:+{$to}", [
                    'from' => $from,
                    'body' => $request->input('message'),
                ]);
                $sent++;
                Log::info('Admin WhatsApp sent', ['admin' => auth()->id(), 'to_user' => $user->id, 'to_number' => $to]);
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = "{$user->email}: {$e->getMessage()}";
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
