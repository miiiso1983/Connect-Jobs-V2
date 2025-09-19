@extends('emails.layouts.base')

@section('content')
  <h3 style="margin-top:0;color:#0D2660">تنبيهات وظائف جديدة</h3>
  <p>هذه أبرز الوظائف وفق تفضيلاتك.</p>
  @php $qs = array_filter([
    'q' => $filters['q'] ?? null,
    'province' => $filters['province'] ?? null,
    'industry' => $filters['industry'] ?? null,
    'job_title' => $filters['job_title'] ?? null,
  ]); @endphp
  @if(!empty($qs))
    <p>المعايير:
      @foreach($qs as $k=>$v)
        <strong>{{ $k }}</strong>: {{ $v }}@if(!$loop->last) ، @endif
      @endforeach
    </p>
  @endif

  @forelse($jobs as $job)
    <div style="margin:12px 0;padding:12px;border:1px solid #e5e7eb;border-radius:12px">
      <div style="font-weight:600;color:#0D2660">{{ $job->title }}</div>
      <div style="font-size:12px;color:#6b7280">{{ optional($job->company)->company_name ?? '—' }} · {{ $job->province }}</div>
      <p style="margin:8px 0 0 0;color:#111827">{{ \Illuminate\Support\Str::limit(strip_tags((string)$job->description), 160) }}</p>
      <div style="margin-top:8px">
        <a href="{{ route('jobs.show',$job) }}" class="btn">عرض التفاصيل</a>
      </div>
    </div>
  @empty
    <p>لا توجد وظائف مطابقة حاليًا. سنوافيك بتحديثات أسبوعية.</p>
  @endforelse

  <p class="muted">يمكنك إدارة التنبيهات أو إيقافها من لوحة الباحث عن عمل &rarr; تنبيهات الوظائف.</p>
  @if(!empty($unsubscribeToken))
    <p class="muted" style="margin-top:8px">
      لإيقاف هذا التنبيه مباشرة: <a href="{{ route('alerts.unsubscribe', $unsubscribeToken) }}">إلغاء الاشتراك من هذا التنبيه</a>
    </p>
  @endif
@endsection

