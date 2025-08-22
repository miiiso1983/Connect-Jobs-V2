<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>وظيفة جديدة | New Job</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f8fafc;margin:0;padding:0}
    .container{max-width:640px;margin:20px auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 6px 24px rgba(0,0,0,.08)}
    .header{background:linear-gradient(135deg,#0ea5e9,#6366f1);padding:24px;color:#fff;text-align:center}
    .content{padding:24px;color:#334155;line-height:1.8}
    .btn{display:inline-block;background:#0ea5e9;color:#fff;padding:12px 18px;border-radius:8px;text-decoration:none}
    .muted{color:#64748b;font-size:13px}
  </style>
</head>
<body>
  <div class="container">
    <div class="header"><h2>وظيفة جديدة قد تهمك</h2></div>
    <div class="content">
      <h3>{{ $job->title }}</h3>
      <p>الشركة: <strong>{{ $job->company->company_name ?? '—' }}</strong></p>
      <p>الموقع: {{ $job->province ?? '—' }}</p>
      <p style="white-space:pre-line">{{ Str::limit($job->description, 220) }}</p>
      <p style="text-align:center;margin:24px 0"><a class="btn" href="{{ url('/jobs/'.$job->id) }}">عرض التفاصيل والتقديم</a></p>
      <p class="muted">يمكنك تعديل تفضيلات البريد أو إلغاء الاشتراك من إعدادات حسابك.</p>
    </div>
  </div>
</body>
</html>
