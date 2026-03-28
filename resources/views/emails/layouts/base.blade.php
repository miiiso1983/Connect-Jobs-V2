<!doctype html>
<html lang="ar" dir="rtl" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <title>{{ $subject ?? 'Connect Jobs' }}</title>
  <style>
    body{font-family:'Segoe UI',Arial,Helvetica,sans-serif;background:#f6f9fc;margin:0;padding:24px;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}
    .wrapper{max-width:640px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 6px 24px rgba(0,0,0,.08)}
    .header{background:linear-gradient(135deg,#4A00B8,#5A00E1);padding:24px;color:#fff;text-align:center}
    .content{padding:24px;color:#334155;line-height:1.8;font-size:15px}
    .btn{display:inline-block;background:#4A00B8;color:#fff !important;padding:12px 18px;border-radius:8px;text-decoration:none;font-weight:600}
    .muted{color:#64748b;font-size:13px}
    .footer{background:#f8fafc;padding:20px 24px;text-align:center;color:#94a3b8;font-size:12px;line-height:1.8;border-top:1px solid #e2e8f0}
    .footer a{color:#64748b;text-decoration:underline}
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header"><h2 style="margin:0;font-size:22px">Connect Jobs</h2></div>
    <div class="content">
      @yield('content')
    </div>
    <div class="footer">
      <p style="margin:0 0 4px">Connect Jobs &mdash; منصة التوظيف الرائدة في العراق</p>
      <p style="margin:0 0 4px">بغداد، العراق | <a href="https://www.connect-job.com">www.connect-job.com</a></p>
      <p style="margin:0 0 4px"><a href="mailto:info@connect-job.com">info@connect-job.com</a></p>
      <p style="margin:8px 0 0;color:#cbd5e1;font-size:11px">هذه رسالة آلية من Connect Jobs. إذا لم تكن المعني بهذه الرسالة يرجى تجاهلها.</p>
      <p style="margin:4px 0 0;color:#cbd5e1;font-size:11px">&copy; {{ date('Y') }} Connect Jobs. جميع الحقوق محفوظة.</p>
    </div>
  </div>
</body>
</html>
