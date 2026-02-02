<x-guest-layout>
  @section('meta_title', $company->company_name.' - Connect Jobs')
  @section('meta_description', 'الملف العام لشركة '.$company->company_name.' ووظائفها المفتوحة')
  @if(!empty($company->profile_image))
    @section('meta_image', Storage::url($company->profile_image))
  @endif

  {{-- Hero Header --}}
  <section class="relative overflow-hidden bg-gradient-to-br from-[#0D2660] via-[#102E66] to-[#0A1E46] text-white py-12">
    <div class="absolute -top-20 -left-20 h-72 w-72 bg-white/5 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-16 -right-24 h-80 w-80 bg-black/10 rounded-full blur-3xl"></div>
    <div class="relative max-w-6xl mx-auto px-4 flex flex-col md:flex-row items-center gap-6">
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
        <img src="{{ Storage::url($company->profile_image) }}" @if($srcset) srcset="{{ $srcset }}" sizes="96px" @endif alt="{{ $company->company_name }}" loading="lazy" width="96" height="96" class="w-24 h-24 rounded-2xl object-cover ring-4 ring-[#E7C66A]/30 shadow-xl"/>
      @else
        <div class="w-24 h-24 rounded-2xl bg-[#E7C66A]/20 flex items-center justify-center">
          <svg class="w-12 h-12 text-[#E7C66A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </div>
      @endif
      <div class="text-center md:text-right">
        <h1 class="text-3xl md:text-4xl font-extrabold">{{ $company->company_name }}</h1>
        <div class="flex flex-wrap justify-center md:justify-start gap-3 mt-3">
          @if($company->industry)
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-sm">
              <svg class="w-4 h-4 text-[#E7C66A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              {{ $company->industry }}
            </span>
          @endif
          @if($company->province)
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-sm">
              <svg class="w-4 h-4 text-[#E7C66A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
              {{ $company->province }}
            </span>
          @endif
        </div>
      </div>
    </div>
  </section>

  <section class="py-10">
    <div class="max-w-6xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-3 gap-8">
      {{-- Jobs List --}}
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
          <div class="flex items-center gap-3 p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="w-10 h-10 rounded-lg bg-[#0D2660] flex items-center justify-center">
              <svg class="w-5 h-5 text-[#E7C66A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">الوظائف المفتوحة</h3>
            <span class="bg-[#0D2660] text-white text-xs font-bold px-3 py-1 rounded-full">{{ $company->jobs->count() }} وظيفة</span>
          </div>
          <div class="p-6 space-y-4">
            @forelse($company->jobs as $job)
              <div class="group bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-750 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                <div class="flex items-start justify-between gap-4">
                  <div class="flex-1">
                    <a href="{{ route('jobs.show',$job) }}" class="text-lg font-bold text-[#0D2660] dark:text-[#E7C66A] hover:underline">{{ $job->title }}</a>
                    <div class="flex items-center gap-2 mt-2 text-sm text-gray-500 dark:text-gray-400">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                      {{ $job->province }}
                    </div>
                    <p class="mt-2 text-gray-600 dark:text-gray-300 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags((string)$job->description), 140) }}</p>
                  </div>
                  <a href="{{ route('jobs.show',$job) }}" class="flex-shrink-0 px-4 py-2 rounded-lg bg-gradient-to-r from-[#0D2660] to-[#102E66] text-white text-sm font-medium hover:from-[#0A1E46] hover:to-[#0D2660] transition-all shadow">
                    عرض التفاصيل
                  </a>
                </div>
              </div>
            @empty
              <div class="text-center py-12">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                  <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400">لا توجد وظائف مفتوحة حالياً</p>
              </div>
            @endforelse
          </div>
        </div>
      </div>

      {{-- Sidebar --}}
      <aside class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
          <div class="flex items-center gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
            <div class="w-8 h-8 rounded-lg bg-[#E7C66A] flex items-center justify-center">
              <svg class="w-4 h-4 text-[#0D2660]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h4 class="font-bold text-gray-800 dark:text-white">حول الشركة</h4>
          </div>
          <div class="p-4 space-y-3">
            <div class="flex items-center gap-3 text-sm">
              <svg class="w-5 h-5 text-[#0D2660] dark:text-[#E7C66A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
              <span class="text-gray-600 dark:text-gray-400">الاسم:</span>
              <span class="font-medium text-gray-800 dark:text-white">{{ $company->company_name }}</span>
            </div>
            <div class="flex items-center gap-3 text-sm">
              <svg class="w-5 h-5 text-[#0D2660] dark:text-[#E7C66A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              <span class="text-gray-600 dark:text-gray-400">الصناعة:</span>
              <span class="font-medium text-gray-800 dark:text-white">{{ $company->industry ?: '—' }}</span>
            </div>
            <div class="flex items-center gap-3 text-sm">
              <svg class="w-5 h-5 text-[#0D2660] dark:text-[#E7C66A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
              <span class="text-gray-600 dark:text-gray-400">المحافظة:</span>
              <span class="font-medium text-gray-800 dark:text-white">{{ $company->province ?: '—' }}</span>
            </div>
            @if($company->mobile_number)
              <div class="flex items-center gap-3 text-sm">
                <svg class="w-5 h-5 text-[#0D2660] dark:text-[#E7C66A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                <span class="text-gray-600 dark:text-gray-400">الهاتف:</span>
                <span class="font-medium text-gray-800 dark:text-white">{{ $company->mobile_number }}</span>
              </div>
            @endif
          </div>
        </div>

        {{-- Browse Jobs CTA --}}
        <div class="bg-gradient-to-br from-[#0D2660] to-[#0A1E46] rounded-xl shadow-lg p-6 text-center text-white">
          <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-[#E7C66A]/20 flex items-center justify-center">
            <svg class="w-7 h-7 text-[#E7C66A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>
          <h4 class="font-bold text-lg mb-2">تصفح المزيد</h4>
          <p class="text-white/80 text-sm mb-4">اكتشف المزيد من الفرص الوظيفية</p>
          <a href="{{ route('jobs.index') }}" class="block w-full py-3 rounded-lg bg-[#E7C66A] text-[#0D2660] font-bold hover:bg-[#D2A85A] transition">
            تصفح جميع الوظائف
          </a>
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

