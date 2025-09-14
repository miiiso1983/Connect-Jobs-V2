@extends('emails.layouts.base')

@section('content')
  <h2 style="margin-top:0">تم الاطلاع على ملفك</h2>
  <p>الشركة: <strong>{{ $companyName }}</strong></p>
  @if($job)
    <p>الوظيفة: <strong>{{ $job->title }}</strong></p>
  @endif
  <p>تاريخ العرض: <strong>{{ \Carbon\Carbon::parse($viewedAt)->format('Y-m-d H:i') }}</strong></p>
  <p class="muted">تذكير ودي: حافظ على تحديث ملفك ومهاراتك لتحسين فرصك في الحصول على الفرص المناسبة.</p>
  <p style="text-align:center;margin:24px 0">
    <a class="btn" href="{{ route('jobseeker.profile.edit') }}">حدّث ملفك الآن</a>
  </p>
@endsection

