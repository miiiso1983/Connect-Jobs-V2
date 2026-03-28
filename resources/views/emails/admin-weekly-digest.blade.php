@extends('emails.layouts.base')

@section('content')
  <h3 style="margin-top:0;color:#4A00B8">الملخص الأسبوعي - Connect Job</h3>
  <p>منذ: {{ $stats['since']->timezone(config('app.timezone'))?->format('Y-m-d') }}</p>

  <ul style="list-style:none;padding:0;margin:16px 0;color:#111827;">
    <li>عدد تنبيهات الوظائف المُرسلة: <strong>{{ $stats['alerts_sent'] }}</strong></li>
    <li>وظائف جديدة: <strong>{{ $stats['jobs_new'] }}</strong></li>
    <li>شركات مسجلة جديدة: <strong>{{ $stats['companies_new'] }}</strong></li>
    <li>باحثون عن عمل جدد: <strong>{{ $stats['seekers_new'] }}</strong></li>
  </ul>

  @if($topJobs->count())
    <h3 style="color:#4A00B8;margin-top:24px;">أحدث الوظائف</h3>
    <ol style="padding-right:18px;color:#374151;">
      @foreach($topJobs as $job)
        <li>
          {{ $job->title }}
          @if(optional($job->company)->company_name)
            — <span style="color:#6B7280">{{ $job->company->company_name }}</span>
          @endif
        </li>
      @endforeach
    </ol>
  @endif
@endsection

