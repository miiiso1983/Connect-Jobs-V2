@extends('emails.layouts.base')

@section('content')
  <h3 style="margin-top:0;color:#4A00B8">إشعار إداري: طلب تسجيل شركة جديدة</h3>
  <p>السادة فريق الإدارة،</p>
  <p>نود إشعاركم بأنه تم استلام طلب جديد لتسجيل شركة على منصة Connect Job. يرجى مراجعة بيانات الطلب أدناه واتخاذ الإجراء المناسب.</p>
  <table style="width:100%;border-collapse:collapse;margin:16px 0">
    <tr><td style="padding:6px;color:#64748b;font-size:13px">اسم الشركة</td><td style="padding:6px;font-weight:600">{{ $company->company_name }}</td></tr>
    <tr><td style="padding:6px;color:#64748b;font-size:13px">البريد الإلكتروني</td><td style="padding:6px;font-weight:600">{{ optional($user)->email ?: 'غير متوفر' }}</td></tr>
    <tr><td style="padding:6px;color:#64748b;font-size:13px">المكتب العلمي</td><td style="padding:6px;font-weight:600">{{ $company->scientific_office_name }}</td></tr>
    <tr><td style="padding:6px;color:#64748b;font-size:13px">المسمى الوظيفي</td><td style="padding:6px;font-weight:600">{{ $company->company_job_title }}</td></tr>
    <tr><td style="padding:6px;color:#64748b;font-size:13px">رقم الهاتف</td><td style="padding:6px;font-weight:600">{{ $company->mobile_number }}</td></tr>
    <tr><td style="padding:6px;color:#64748b;font-size:13px">المحافظة</td><td style="padding:6px;font-weight:600">{{ $company->province }}</td></tr>
    <tr><td style="padding:6px;color:#64748b;font-size:13px">الصناعة</td><td style="padding:6px;font-weight:600">{{ $company->industry }}</td></tr>
    <tr><td style="padding:6px;color:#64748b;font-size:13px">وقت استلام الطلب</td><td style="padding:6px;font-weight:600">{{ $submittedAt->format('Y-m-d H:i') }}</td></tr>
  </table>
  <p style="text-align:center;margin:24px 0">
    <a class="btn" href="{{ $approveUrl }}">مراجعة طلبات تسجيل الشركات</a>
  </p>
@endsection

