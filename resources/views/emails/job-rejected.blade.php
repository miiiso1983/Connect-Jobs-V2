@extends('emails.layouts.base')

@section('content')
	  <h3 style="margin-top:0;color:#5B21B6">إشعار رفض وظيفة</h3>
  <p>تمت مراجعة إعلان الوظيفة التالية وتم رفضه من قبل فريق الإشراف:</p>
	  <div style="margin:12px 0;padding:12px;border:1px solid #e5e7eb;border-radius:12px">
	    <div style="font-weight:600;color:#5B21B6">{{ $job->title }}</div>
    <div style="font-size:12px;color:#6b7280">{{ optional($job->company)->company_name }} · {{ $job->province }}</div>
    @if(!empty($reason))
      <p style="margin:8px 0 0 0;color:#b91c1c"><strong>سبب الرفض:</strong> {{ $reason }}</p>
    @endif
  </div>
  <p class="muted">يمكنك تعديل تفاصيل الإعلان وإعادة إرساله للمراجعة من لوحة شركتك.</p>
  <div style="margin-top:12px">
    <a href="{{ url('/company/jobs') }}" class="btn">فتح لوحة الشركة</a>
  </div>
@endsection

