<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>إعلان وظيفة بانتظار المراجعة</title>
  <style>body{font-family:Arial,Helvetica,sans-serif;background:#f6f9fc;margin:0;padding:0}.container{max-width:680px;margin:24px auto;background:#fff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.08);overflow:hidden}.header{background:linear-gradient(135deg,#f59e0b,#ef4444);padding:20px;color:#fff;text-align:center}.content{padding:20px;color:#334155;line-height:1.9}.grid{display:grid;grid-template-columns:1fr 1fr;gap:8px 16px}.label{color:#64748b;font-size:12px}.val{font-weight:600}</style>
</head>
<body>
  <div class="container">
    <div class="header"><h2>إعلان وظيفة بانتظار المراجعة</h2></div>
    <div class="content">
      <p>هناك إعلان وظيفة جديد يحتاج إلى موافقة الإدارة.</p>
      <div class="grid">
        <div class="label">العنوان</div><div class="val">{{ $job->title }}</div>
        <div class="label">المحافظة</div><div class="val">{{ $job->province }}</div>
        <div class="label">الحالة</div><div class="val">{{ $job->status }}</div>
      </div>
      <p style="margin-top:16px"><a href="{{ $reviewUrl }}" style="display:inline-block;background:#ef4444;color:#fff;text-decoration:none;padding:10px 16px;border-radius:10px">مراجعة الوظائف المعلقة</a></p>
    </div>
  </div>
</body>
</html>

