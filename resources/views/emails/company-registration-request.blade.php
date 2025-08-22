<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>طلب تسجيل شركة جديد</title>
  <style>body{font-family:Arial,Helvetica,sans-serif;background:#f6f9fc;margin:0;padding:0}.container{max-width:680px;margin:24px auto;background:#fff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.08);overflow:hidden}.header{background:linear-gradient(135deg,#6366f1,#22c55e);padding:20px;color:#fff;text-align:center}.content{padding:20px;color:#334155;line-height:1.9}.grid{display:grid;grid-template-columns:1fr 1fr;gap:8px 16px}.label{color:#64748b;font-size:12px}.val{font-weight:600}</style>
</head>
<body>
  <div class="container">
    <div class="header"><h2>طلب تسجيل شركة جديد</h2></div>
    <div class="content">
      <p>تم استلام طلب تسجيل شركة جديدة بالمعلومات التالية:</p>
      <div class="grid">
        <div class="label">اسم الشركة</div><div class="val">{{ $company->company_name }}</div>
        <div class="label">المكتب العلمي</div><div class="val">{{ $company->scientific_office_name }}</div>
        <div class="label">المسمى الوظيفي</div><div class="val">{{ $company->company_job_title }}</div>
        <div class="label">رقم الهاتف</div><div class="val">{{ $company->mobile_number }}</div>
        <div class="label">المحافظة</div><div class="val">{{ $company->province }}</div>
        <div class="label">الصناعة</div><div class="val">{{ $company->industry }}</div>
      </div>
      <p style="margin-top:16px"><a href="{{ $approveUrl }}" style="display:inline-block;background:#6366f1;color:#fff;text-decoration:none;padding:10px 16px;border-radius:10px">الانتقال لمراجعة الشركات</a></p>
    </div>
  </div>
</body>
</html>

