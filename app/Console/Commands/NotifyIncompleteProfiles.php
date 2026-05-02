<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\JobSeeker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class NotifyIncompleteProfiles extends Command
{
    protected $signature = 'notify:incomplete-profiles
                            {--dry-run : عرض المستهدفين بدون إرسال فعلي}
                            {--group=all : المجموعة المستهدفة: all, incomplete, no-cv}';

    protected $description = 'إرسال إشعارات WhatsApp عبر Twilio لتذكير الباحثين عن عمل بإكمال ملفاتهم أو رفع السيرة الذاتية';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $group  = $this->option('group');

        $sid   = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from  = config('services.twilio.whatsapp_from');

        if (!$dryRun && (!$sid || !$token || !$from)) {
            $this->error('❌ إعدادات Twilio غير مكتملة. تأكد من TWILIO_SID و TWILIO_TOKEN و TWILIO_WHATSAPP_FROM في .env');
            return self::FAILURE;
        }

        // ── المجموعة 1: لم يكملوا البيانات الشخصية ──
        $incompleteUsers = collect();
        if (in_array($group, ['all', 'incomplete'])) {
            $incompleteUsers = User::where('role', 'jobseeker')
                ->where('status', 'active')
                ->whereNotNull('whatsapp_number')
                ->where('whatsapp_number', '!=', '')
                ->whereHas('jobSeeker', fn($q) => $q->where('profile_completed', false))
                ->with('jobSeeker')
                ->get();
        }

        // ── المجموعة 2: أكملوا البيانات لكن بدون CV ──
        $noCvUsers = collect();
        if (in_array($group, ['all', 'no-cv'])) {
            $noCvUsers = User::where('role', 'jobseeker')
                ->where('status', 'active')
                ->whereNotNull('whatsapp_number')
                ->where('whatsapp_number', '!=', '')
                ->whereHas('jobSeeker', fn($q) => $q->where('profile_completed', true)->whereNull('cv_file'))
                ->with('jobSeeker')
                ->get();
        }

        $this->info("📊 المجموعة 1 (بيانات غير مكتملة): {$incompleteUsers->count()} مستخدم");
        $this->info("📊 المجموعة 2 (بدون CV): {$noCvUsers->count()} مستخدم");

        if ($dryRun) {
            $this->warn('⚠️ وضع المعاينة (dry-run) — لن يتم إرسال رسائل فعلية.');
            $this->table(
                ['المجموعة', 'الاسم', 'البريد', 'رقم الواتساب'],
                $incompleteUsers->map(fn($u) => ['بيانات غير مكتملة', $u->name, $u->email, $u->whatsapp_number])
                    ->merge($noCvUsers->map(fn($u) => ['بدون CV', $u->name, $u->email, $u->whatsapp_number]))
                    ->toArray()
            );
            return self::SUCCESS;
        }

        $client = new Client($sid, $token);
        $sent = 0;
        $failed = 0;

        // ── إرسال رسائل المجموعة 1 ──
        foreach ($incompleteUsers as $user) {
            $result = $this->sendWhatsApp($client, $from, $user, $this->incompleteProfileMessage($user));
            $result ? $sent++ : $failed++;
        }

        // ── إرسال رسائل المجموعة 2 ──
        foreach ($noCvUsers as $user) {
            $result = $this->sendWhatsApp($client, $from, $user, $this->noCvMessage($user));
            $result ? $sent++ : $failed++;
        }

        $this->info("✅ تم الإرسال: {$sent} | ❌ فشل: {$failed}");
        Log::channel('stack')->info("WhatsApp notify:incomplete-profiles — sent: {$sent}, failed: {$failed}");

        return self::SUCCESS;
    }

    private function sendWhatsApp(Client $client, string $from, User $user, string $body): bool
    {
        $to = $this->normalizeMsisdn($user->whatsapp_number);
        if (!$to) {
            $this->warn("⚠️ رقم غير صالح للمستخدم: {$user->email}");
            Log::warning("WhatsApp notify skipped — invalid number", ['user_id' => $user->id, 'raw' => $user->whatsapp_number]);
            return false;
        }

        try {
            $client->messages->create("whatsapp:+{$to}", [
                'from' => $from,
                'body' => $body,
            ]);
            $this->line("  ✔ {$user->email} → +{$to}");
            Log::info("WhatsApp notify sent", ['user_id' => $user->id, 'to' => $to]);
            return true;
        } catch (\Twilio\Exceptions\RestException $e) {
            $this->error("  ✖ {$user->email}: {$e->getMessage()}");
            Log::error("WhatsApp notify failed (Twilio)", ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return false;
        } catch (\Throwable $e) {
            $this->error("  ✖ {$user->email}: {$e->getMessage()}");
            Log::error("WhatsApp notify failed", ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return false;
        }
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

    private function incompleteProfileMessage(User $user): string
    {
        $name = $user->jobSeeker->full_name ?? $user->name;
        return "مرحباً {$name} 👋\n\n"
            . "لاحظنا أن ملفك الشخصي على *Connect Jobs* غير مكتمل بعد.\n\n"
            . "✅ أكمل بياناتك الآن (المسمى الوظيفي، التخصص، المحافظة، الجنس) لتظهر في نتائج البحث وتزيد فرصك في الحصول على وظيفة مناسبة.\n\n"
            . "🔗 حدّث ملفك من هنا:\n"
            . url('/jobseeker/profile') . "\n\n"
            . "فريق Connect Jobs";
    }

    private function noCvMessage(User $user): string
    {
        $name = $user->jobSeeker->full_name ?? $user->name;
        return "مرحباً {$name} 👋\n\n"
            . "بياناتك الشخصية مكتملة على *Connect Jobs* 🎉\n\n"
            . "لكن لم يتم رفع *السيرة الذاتية (CV)* بعد.\n\n"
            . "📄 رفع الـ CV يزيد فرصك بنسبة كبيرة! الشركات تبحث عن مرشحين لديهم سيرة ذاتية جاهزة.\n\n"
            . "🔗 ارفع سيرتك الذاتية الآن:\n"
            . url('/jobseeker/profile') . "\n\n"
            . "فريق Connect Jobs";
    }
}
