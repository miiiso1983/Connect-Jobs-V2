<x-guest-layout>
  @section('meta_title', $company->company_name.' - Connect Jobs')
  @section('meta_description', 'الملف العام لشركة '.$company->company_name.' ووظائفها المفتوحة')
  @if(!empty($company->profile_image))
    @section('meta_image', Storage::url($company->profile_image))
  @endif


  <section class="bg-gradient-to-r from-[#0D2660] via-[#102E66] to-[#0A1E46] text-white py-10">
    <div class="max-w-6xl mx-auto px-4 flex items-center gap-4">
      @if(!empty($company->profile_image))
        @php
          $imgPath = $company->profile_image;
          $base = \Illuminate\Support\Str::of($imgPath)->beforeLast('.');
          $sm = (string)$base . '_sm.webp';
          $md = (string)$base . '_md.webp';
          $lg = (string)$base . '_lg.webp';
          $srcsetArr = [];
          if (Storage::disk('public')->exists($sm)) { $srcsetArr[] = Storage::url($sm).' 160w'; }
          if (Storage::disk('public')->exists($md)) { $srcsetArr[] = Storage::url($md).' 320w'; }
          if (Storage::disk('public')->exists($lg)) { $srcsetArr[] = Storage::url($lg).' 640w'; }
          $srcset = implode(', ', $srcsetArr);
        @endphp
        <img src="{{ Storage::url($company->profile_image) }}" @if($srcset) srcset="{{ $srcset }}" sizes="64px" @endif alt="{{ $company->company_name }}" loading="lazy" width="64" height="64" class="w-16 h-16 rounded-xl object-cover ring-2 ring-white/50"/>
      @endif
      <div>
        <h1 class="text-3xl font-extrabold">{{ $company->company_name }}</h1>
        <p class="opacity-90 mt-1">{{ $company->industry }} · {{ $company->province }}</p>
      </div>
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

