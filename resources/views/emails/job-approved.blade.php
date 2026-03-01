@extends('emails.layouts.base')

@section('content')
		  <h3 style="margin-top:0;color:#4A00B8">تم اعتماد إعلانك</h3>
  <p>نود إبلاغك بأنه تم اعتماد إعلان الوظيفة التالي من قبل فريق الإشراف:</p>
	  <div style="margin:12px 0;padding:12px;border:1px solid #e5e7eb;border-radius:12px">
		    <div style="font-weight:600;color:#4A00B8">{{ $job->title }}</div>
    <div style="font-size:12px;color:#6b7280">{{ optional($job->company)->company_name }} · {{ $job->province }}</div>
  </div>
  <div style="margin-top:12px">
    <a href="{{ route('jobs.show', $job) }}" class="btn">عرض الإعلان على الموقع</a>
  </div>
  <p class="muted" style="margin-top:12px">يمكنك تعديل الإعلان أو إدارته من لوحة شركتك.</p>
@endsection

