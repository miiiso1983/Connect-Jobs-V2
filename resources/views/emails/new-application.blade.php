<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>طلب توظيف جديد | New Application</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f8fafc;margin:0;padding:0}
    .container{max-width:640px;margin:20px auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 6px 24px rgba(0,0,0,.08)}
    .header{background:linear-gradient(135deg,#22c55e,#16a34a);padding:24px;color:#fff;text-align:center}
    .content{padding:24px;color:#334155;line-height:1.8}
    .btn{display:inline-block;background:#22c55e;color:#fff;padding:12px 18px;border-radius:8px;text-decoration:none}
    .muted{color:#64748b;font-size:13px}
  </style>
</head>
<body>
  <div class="container">
    <div class="header"><h2>تم استلام طلب توظيف جديد</h2></div>
    <div class="content">
      <h3>الوظيفة: {{ $application->job->title }}</h3>
      <p>المتقدم: <strong>{{ $application->jobSeeker->full_name }}</strong></p>
      @if($application->cv_file)
        <p>السيرة الذاتية: <a href="{{ url('storage/'.$application->cv_file) }}">تنزيل</a></p>
      @endif
      <p class="muted">التفاصيل الكاملة في لوحة التحكم.</p>
      <p style="text-align:center;margin:24px 0"><a class="btn" href="{{ url('/company/applications/'.$application->id) }}">عرض الطلب</a></p>
    </div>
  </div>
</body>
</html>
