<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>تفعيل الحساب | Verify Account</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f6f9fc;margin:0;padding:0}
    .container{max-width:640px;margin:20px auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 6px 24px rgba(0,0,0,.08)}
    .header{background:linear-gradient(135deg,#6366f1,#06b6d4);padding:24px;color:#fff;text-align:center}
    .content{padding:24px;color:#334155;line-height:1.8}
    .btn{display:inline-block;background:#6366f1;color:#fff;padding:12px 18px;border-radius:8px;text-decoration:none}
    .muted{color:#64748b;font-size:13px}
  </style>
</head>
<body>
  <div class="container">
    <div class="header"><h2>Connect Jobs</h2></div>
    <div class="content">
      <h3>مرحباً {{ $name ?? '' }}</h3>
      <p>يرجى تأكيد بريدك الإلكتروني لإكمال إنشاء الحساب.</p>
      @isset($verifyUrl)
        <p style="text-align:center;margin:24px 0"><a class="btn" href="{{ $verifyUrl }}">تفعيل الحساب</a></p>
      @endisset
      @isset($code)
        <p>رمز التفعيل الخاص بك:</p>
        <div style="font-size:28px;font-weight:bold;letter-spacing:6px;text-align:center">{{ $code }}</div>
      @endisset
      <p class="muted">إذا لم تقم بإنشاء هذا الحساب، يمكنك تجاهل هذه الرسالة.</p>
    </div>
  </div>
</body>
</html>
