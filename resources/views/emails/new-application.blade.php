@extends('emails.layouts.base')

@section('content')
  <h2 style="margin-top:0">تم استلام طلب توظيف جديد</h2>
  <p>العنوان الوظيفي: <strong>{{ $application->job->title }}</strong></p>
  <p>اسم المتقدم: <strong>{{ $application->jobSeeker->full_name }}</strong></p>
  @if($application->applied_at)
    <p>تاريخ التقديم: <strong>{{ \Carbon\Carbon::parse($application->applied_at)->format('Y-m-d H:i') }}</strong></p>
  @endif
  @if($application->cv_file)
    <p>السيرة الذاتية: <a href="{{ url('storage/'.$application->cv_file) }}">تنزيل</a></p>
  @endif
  <p class="muted">يمكنك عرض الطلب كاملاً واتخاذ إجراء من لوحة الشركة.</p>
  <p style="text-align:center;margin:24px 0">
    <a class="btn" href="{{ route('company.applicants.show', $application->job_seeker_id) }}">عرض الطلب في لوحة الشركة</a>
  </p>
@endsection
