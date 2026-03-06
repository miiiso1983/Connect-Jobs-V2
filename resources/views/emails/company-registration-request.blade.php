<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>إشعار إداري: طلب تسجيل شركة جديدة</title>
  <style>body{font-family:Arial,Helvetica,sans-serif;background:#f6f9fc;margin:0;padding:0}.container{max-width:680px;margin:24px auto;background:#fff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.08);overflow:hidden}.header{background:linear-gradient(135deg,#6366f1,#22c55e);padding:20px;color:#fff;text-align:center}.content{padding:20px;color:#334155;line-height:1.9}.grid{display:grid;grid-template-columns:1fr 1fr;gap:8px 16px}.label{color:#64748b;font-size:12px}.val{font-weight:600}</style>
</head>
<body>
  <div class="container">
    <div class="header"><h2>إشعار إداري: طلب تسجيل شركة جديدة</h2></div>
    <div class="content">
      <p>السادة فريق الإدارة،</p>
      <p>نود إشعاركم بأنه تم استلام طلب جديد لتسجيل شركة على منصة Connect Job. يرجى مراجعة بيانات الطلب أدناه واتخاذ الإجراء المناسب بحسب سياسة الاعتماد المعتمدة.</p>
      <div class="grid">
        <div class="label">اسم الشركة</div><div class="val">{{ $company->company_name }}</div>
        <div class="label">البريد الإلكتروني لصاحب الحساب</div><div class="val">{{ optional($user)->email ?: 'غير متوفر' }}</div>
        <div class="label">المكتب العلمي</div><div class="val">{{ $company->scientific_office_name }}</div>
        <div class="label">المسمى الوظيفي</div><div class="val">{{ $company->company_job_title }}</div>
        <div class="label">رقم الهاتف</div><div class="val">{{ $company->mobile_number }}</div>
        <div class="label">المحافظة</div><div class="val">{{ $company->province }}</div>
        <div class="label">الصناعة</div><div class="val">{{ $company->industry }}</div>
        <div class="label">وقت استلام الطلب</div><div class="val">{{ $submittedAt->format('Y-m-d H:i') }}</div>
      </div>
      <p style="margin-top:16px">للاطلاع على الطلبات الجديدة ومراجعتها، يرجى استخدام الزر التالي:</p>
      <p style="margin-top:16px"><a href="{{ $approveUrl }}" style="display:inline-block;background:#6366f1;color:#fff;text-decoration:none;padding:10px 16px;border-radius:10px">مراجعة طلبات تسجيل الشركات</a></p>
      <p style="margin-top:20px;color:#64748b;font-size:13px">هذه رسالة آلية صادرة من النظام، يرجى عدم الرد عليها مباشرة.</p>
    </div>
  </div>
</body>
</html>

