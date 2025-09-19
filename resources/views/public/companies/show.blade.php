<x-guest-layout>
  @section('meta_title', $company->company_name.' - Connect Jobs')
  @section('meta_description', 'الملف العام لشركة '.$company->company_name.' ووظائفها المفتوحة')

  <section class="bg-gradient-to-r from-[#0D2660] via-[#102E66] to-[#0A1E46] text-white py-10">
    <div class="max-w-6xl mx-auto px-4">
      <h1 class="text-3xl font-extrabold">{{ $company->company_name }}</h1>
      <p class="opacity-90 mt-1">{{ $company->industry }} · {{ $company->province }}</p>
    </div>
  </section>

  <section class="py-10">
    <div class="max-w-6xl mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-8">
      <div class="md:col-span-2 space-y-4">
        <h2 class="text-xl font-bold">الوظائف المفتوحة</h2>
        @forelse($company->jobs as $job)
          <div class="p-4 bg-white rounded-xl shadow flex items-start justify-between gap-4">
            <div>
              <a href="{{ route('jobs.show',$job) }}" class="font-semibold text-primary">{{ $job->title }}</a>
              <div class="text-sm text-slate-600">{{ $company->company_name }} · {{ $job->province }}</div>
              <p class="mt-1 text-slate-800">{{ \Illuminate\Support\Str::limit(strip_tags((string)$job->description), 140) }}</p>
            </div>
            <a class="btn btn-secondary btn-sm" href="{{ route('jobs.show',$job) }}">عرض</a>
          </div>
        @empty
          <div class="alert">لا توجد وظائف مفتوحة حالياً.</div>
        @endforelse
      </div>
      <aside>
        <div class="bg-white rounded-xl shadow p-4">
          <h3 class="font-semibold mb-2">حول الشركة</h3>
          <div class="text-sm text-slate-700 space-y-1">
            <div><strong>الاسم:</strong> {{ $company->company_name }}</div>
            <div><strong>الصناعة:</strong> {{ $company->industry ?: '—' }}</div>
            <div><strong>المحافظة:</strong> {{ $company->province ?: '—' }}</div>
            <div><strong>الهاتف:</strong> {{ $company->mobile_number ?: '—' }}</div>
          </div>
        </div>
      </aside>
    </div>
  </section>

  @php
    $schema = [
      '@context'=>'https://schema.org',
      '@type'=>'Organization',
      'name'=>$company->company_name,
      'url'=>route('public.company.show',$company),
      'address'=>[
        '@type'=>'PostalAddress',
        'addressCountry'=>'IQ',
        'addressRegion'=>$company->province,
      ],
    ];
  @endphp
  <script type="application/ld+json">{!! json_encode(array_filter($schema), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
</x-guest-layout>

