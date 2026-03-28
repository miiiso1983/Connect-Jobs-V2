@extends('emails.layouts.base')

@section('content')
  <h3 style="margin-top:0;color:#4A00B8">تسجيل باحث عمل جديد</h3>
  <p>تم إنشاء حساب باحث عمل جديد بالمعلومات التالية:</p>
  <table style="width:100%;border-collapse:collapse;margin:16px 0">
    <tr><td style="padding:6px;color:#64748b;font-size:13px">الاسم الكامل</td><td style="padding:6px;font-weight:600">{{ $seeker->full_name }}</td></tr>
    <tr><td style="padding:6px;color:#64748b;font-size:13px">البريد الإلكتروني</td><td style="padding:6px;font-weight:600">{{ optional($user)->email }}</td></tr>
    <tr><td style="padding:6px;color:#64748b;font-size:13px">المحافظة</td><td style="padding:6px;font-weight:600">{{ $seeker->province }}</td></tr>
    <tr><td style="padding:6px;color:#64748b;font-size:13px">التخصص</td><td style="padding:6px;font-weight:600">{{ $seeker->speciality ?? '—' }}</td></tr>
  </table>
  <p style="text-align:center;margin:24px 0">
    <a class="btn" href="{{ $profileUrl }}">عرض الملف في لوحة الإدارة</a>
  </p>
@endsection

