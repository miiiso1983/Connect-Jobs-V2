@extends('emails.layouts.base')

@section('content')
  <h3 style="margin-top:0;color:#4A00B8">إعلان وظيفة بانتظار المراجعة</h3>
  <p>هناك إعلان وظيفة جديد يحتاج إلى موافقة الإدارة.</p>
  <table style="width:100%;border-collapse:collapse;margin:16px 0">
    <tr><td style="padding:6px;color:#64748b;font-size:13px">العنوان</td><td style="padding:6px;font-weight:600">{{ $job->title }}</td></tr>
    <tr><td style="padding:6px;color:#64748b;font-size:13px">المحافظة</td><td style="padding:6px;font-weight:600">{{ $job->province }}</td></tr>
    <tr><td style="padding:6px;color:#64748b;font-size:13px">الحالة</td><td style="padding:6px;font-weight:600">{{ $job->status }}</td></tr>
  </table>
  <p style="text-align:center;margin:24px 0">
    <a class="btn" href="{{ $reviewUrl }}">مراجعة الوظائف المعلقة</a>
  </p>
@endsection

