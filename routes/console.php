<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


use Illuminate\Support\Facades\Mail;

Artisan::command('mail:test {to}', function (string $to) {
    try {
        Mail::raw('هذا بريد تجريبي من Connect Job للتأكد من إعدادات SMTP', function ($m) use ($to) {
            $m->to($to)->subject('اختبار البريد - Connect Job');
        });
        $this->info('Test email dispatched to: ' . $to);
    } catch (\Throwable $e) {
        $this->error('Failed to send: ' . $e->getMessage());
    }
})->purpose('Send a test email to verify MAIL_ settings');
