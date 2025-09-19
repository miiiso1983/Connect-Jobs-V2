<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>الملخص الأسبوعي</title>
</head>
<body style="font-family:Tahoma,Arial,sans-serif;background:#f7f7f9;padding:24px;">
  <div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:10px;padding:24px;border:1px solid #e5e7eb;">
    <h2 style="margin-top:0;color:#0D2660">الملخص الأسبوعي - Connect Job</h2>
    <p style="color:#374151;margin:8px 0;">منذ: {{ $stats['since']->timezone(config('app.timezone'))?->format('Y-m-d') }}</p>

    <ul style="list-style:none;padding:0;margin:16px 0;color:#111827;">
      <li>عدد تنبيهات الوظائف المُرسلة: <strong>{{ $stats['alerts_sent'] }}</strong></li>
      <li>وظائف جديدة: <strong>{{ $stats['jobs_new'] }}</strong></li>
      <li>شركات مسجلة جديدة: <strong>{{ $stats['companies_new'] }}</strong></li>
      <li>باحثون عن عمل جدد: <strong>{{ $stats['seekers_new'] }}</strong></li>
    </ul>

    @if($topJobs->count())
      <h3 style="color:#0D2660;margin-top:24px;">أحدث الوظائف</h3>
      <ol style="padding-right:18px;color:#374151;">
        @foreach($topJobs as $job)
          <li>
            {{ $job->title }}
            @if(optional($job->company)->company_name)
              — <span style="color:#6B7280">{{ $job->company->company_name }}</span>
            @endif
          </li>
        @endforeach
      </ol>
    @endif

    <p style="color:#6B7280;margin-top:24px;font-size:12px;">هذه الرسالة أُرسلت تلقائيًا بعد تنفيذ تنبيهات السبت.</p>
  </div>
</body>
</html>

