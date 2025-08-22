<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>رمز التفعيل | Verify Code</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f6f9fc;margin:0;padding:0}
    .container{max-width:640px;margin:20px auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 6px 24px rgba(0,0,0,.08)}
    .header{background:linear-gradient(135deg,#22c55e,#14b8a6);padding:24px;color:#fff;text-align:center}
    .content{padding:24px;color:#334155;line-height:1.8}
    .muted{color:#64748b;font-size:13px}
    .code{font-size:28px;font-weight:bold;letter-spacing:6px;text-align:center;background:#f1f5f9;border-radius:8px;padding:12px 0;margin:12px 0}
  </style>
</head>
<body>
  <div class="container">
    <div class="header"><h2>تفعيل حسابك</h2></div>
    <div class="content">
      <h3>مرحباً {{ $name ?? '' }}</h3>
      <p>هذا رمز التفعيل الخاص بك، صالح لمدة 15 دقيقة:</p>
      <div class="code">{{ $code }}</div>
      <p class="muted">إذا لم تقم بطلب هذا الرمز، يمكنك تجاهل هذه الرسالة.</p>
    </div>
  </div>
</body>
</html>

