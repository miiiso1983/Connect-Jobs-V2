@extends('emails.layouts.base')

@section('content')
  <h3 style="margin-top:0;color:#4A00B8">وظيفة جديدة قد تهمك</h3>
  <h4>{{ $job->title }}</h4>
  <p>الشركة: <strong>{{ $job->company->company_name ?? '—' }}</strong></p>
  <p>الموقع: {{ $job->province ?? '—' }}</p>
  <p style="white-space:pre-line">{{ Str::limit($job->description, 220) }}</p>
  <p style="text-align:center;margin:24px 0"><a class="btn" href="{{ url('/jobs/'.$job->id) }}">عرض التفاصيل والتقديم</a></p>
  <p class="muted">يمكنك تعديل تفضيلات البريد أو إلغاء الاشتراك من إعدادات حسابك.</p>
@endsection
