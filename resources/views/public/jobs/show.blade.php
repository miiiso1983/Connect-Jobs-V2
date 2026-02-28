<x-app-layout>
    @section('meta_title', $job->title . ' - ' . (optional($job->company)->company_name ?? 'Connect Jobs'))
    @php
        $__desc = \Illuminate\Support\Str::limit(strip_tags((string)($job->description ?? '')), 150);
    @endphp
    @section('meta_description', $__desc)
    @if($job->company && !empty($job->company->profile_image))
        @section('meta_image', Storage::url($job->company->profile_image))
    @endif

    <x-slot name="header">
	        <div class="rounded-xl bg-gradient-to-br from-[#5B21B6] via-[#6D28D9] to-[#4C1D95] text-white p-6">
            <h1 class="text-2xl font-bold">{{ $job->title }}</h1>
	            <div class="mt-1 text-[#38BDF8] text-sm">{{ $job->province }}</div>
            @if($job->company)
                <div class="mt-2 flex items-center gap-2 text-sm">
                    @php
                        $imgPath = $job->company->profile_image ?? null;
                        $srcset = '';
                        if ($imgPath) {
                            $base = \Illuminate\Support\Str::of($imgPath)->beforeLast('.');
                            $sm = (string)$base . '_sm.webp';
                            $md = (string)$base . '_md.webp';
                            $lg = (string)$base . '_lg.webp';
                            $arr = [];
                            if (Storage::disk('public')->exists($sm)) { $arr[] = Storage::url($sm).' 160w'; }
                            if (Storage::disk('public')->exists($md)) { $arr[] = Storage::url($md).' 320w'; }
                            if (Storage::disk('public')->exists($lg)) { $arr[] = Storage::url($lg).' 640w'; }
                            $srcset = implode(', ', $arr);
                        }
                    @endphp
                    @if(!empty($job->company->profile_image))
                        <img src="{{ Storage::url($job->company->profile_image) }}" @if($srcset) srcset="{{ $srcset }}" sizes="32px" @endif alt="{{ $job->company->company_name }}" class="w-8 h-8 rounded object-cover ring-1 ring-white/40" />
                    @endif
                    <a class="underline text-white/90 hover:text-white" href="{{ route('public.company.show', $job->company) }}">{{ $job->company->company_name }}</a>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Job Details Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                    <div class="flex items-center gap-3 p-6 border-b border-gray-200 dark:border-gray-700">
	                        <div class="w-10 h-10 rounded-lg bg-[#5B21B6] flex items-center justify-center">
	                            <svg class="w-5 h-5 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">تفاصيل الوظيفة</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <h4 class="text-sm font-bold text-gray-500 dark:text-gray-400 mb-2">الوصف الوظيفي</h4>
                            <div class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $job->description }}</div>
                        </div>
                        @if($job->requirements)
                            <div>
                                <h4 class="text-sm font-bold text-gray-500 dark:text-gray-400 mb-2">المتطلبات</h4>
                                <div class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $job->requirements }}</div>
                            </div>
                        @endif
                        @if($job->jd_file)
	                            <div class="flex items-center gap-3 p-4 rounded-lg bg-[#5B21B6]/5 dark:bg-[#5B21B6]/20">
	                                <div class="w-10 h-10 rounded-lg bg-[#38BDF8] flex items-center justify-center">
	                                    <svg class="w-5 h-5 text-[#4C1D95]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-bold text-gray-800 dark:text-white">الوصف الوظيفي المفصل</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">ملف PDF</div>
                                </div>
	                                <a href="{{ Storage::url($job->jd_file) }}" target="_blank" class="px-4 py-2 rounded-lg bg-[#5B21B6] text-white text-sm font-medium hover:bg-[#4C1D95] transition">
                                    تحميل
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Quick Info Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                    <div class="flex items-center gap-3 p-4 border-b border-gray-200 dark:border-gray-700">
	                        <div class="w-8 h-8 rounded-lg bg-[#38BDF8] flex items-center justify-center">
	                            <svg class="w-4 h-4 text-[#4C1D95]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h4 class="font-bold text-gray-800 dark:text-white">معلومات سريعة</h4>
                    </div>
                    <div class="p-4 space-y-3">
                        <div class="flex items-center gap-3 text-sm">
	                            <svg class="w-5 h-5 text-[#5B21B6] dark:text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            <span class="text-gray-600 dark:text-gray-400">المحافظة:</span>
                            <span class="font-medium text-gray-800 dark:text-white">{{ $job->province }}</span>
                        </div>
                        @if($job->company)
                            <div class="flex items-center gap-3 text-sm">
	                                <svg class="w-5 h-5 text-[#5B21B6] dark:text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span class="text-gray-600 dark:text-gray-400">الشركة:</span>
                                <span class="font-medium text-gray-800 dark:text-white">{{ $job->company->company_name }}</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-3 text-sm">
	                            <svg class="w-5 h-5 text-[#5B21B6] dark:text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="text-gray-600 dark:text-gray-400">تاريخ النشر:</span>
                            <span class="font-medium text-gray-800 dark:text-white">{{ $job->created_at?->format('Y/m/d') ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Apply Card --}}
	                <div class="bg-gradient-to-br from-[#5B21B6] to-[#4C1D95] rounded-xl shadow-lg p-6 text-center text-white">
	                    <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-[#38BDF8]/20 flex items-center justify-center">
	                        <svg class="w-7 h-7 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h4 class="font-bold text-lg mb-2">هل أنت مهتم؟</h4>
                    <p class="text-white/80 text-sm mb-4">قدم الآن وابدأ رحلتك المهنية</p>
                    @if(auth()->check())
                        @if(auth()->user()->role==='jobseeker')
                            <form method="POST" action="{{ route('jobseeker.apply',$job) }}">
                                @csrf
	                                <button type="submit" class="w-full py-3 rounded-lg bg-[#38BDF8] text-[#4C1D95] font-bold hover:bg-[#7DD3FC] transition">
                                    تقديم الآن
                                </button>
                            </form>
                        @else
                            <p class="text-white/70 text-sm">سجّل دخول كباحث عن عمل للتقديم</p>
                        @endif
                    @else
	                        <a href="{{ route('login') }}" class="block w-full py-3 rounded-lg bg-[#38BDF8] text-[#4C1D95] font-bold hover:bg-[#7DD3FC] transition">
                            سجّل للدخول للتقديم
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @if(auth()->check())
        @if(auth()->user()->role==='jobseeker')
            <div class="fixed bottom-4 inset-x-0 flex justify-center z-40">
                <div class="bg-base-100/90 backdrop-blur border rounded-full shadow-lg px-3 py-2 flex items-center gap-3">
                    <span class="hidden sm:block text-sm">{{ $job->title }}</span>
                    <form method="POST" action="{{ route('jobseeker.apply',$job) }}">
                        @csrf
                        <button class="btn btn-primary btn-sm rounded-full">تقديم الآن</button>
                    </form>
                    @php $saved = $isSaved ?? false; @endphp
                    <form method="POST" action="{{ $saved ? route('jobs.unsave',$job) : route('jobs.save',$job) }}">
                        @csrf
                        @if($saved)
                            @method('DELETE')
                        @endif
                        <button class="btn btn-ghost btn-sm rounded-full text-primary">
                            {{ $saved ? 'إلغاء الحفظ' : 'حفظ لاحقاً' }}
                        </button>
                    </form>
                </div>
            </div>
        @endif
    @endif
    @unless(auth()->check())
        <div class="fixed bottom-4 inset-x-0 flex justify-center z-40">
            <a href="{{ route('login') }}" class="btn btn-primary btn-sm rounded-full">سجّل للدخول للتقديم</a>
        </div>
    @endunless

    {{-- JSON-LD JobPosting Schema --}}
    @php
        $orgName = optional($job->company)->company_name ?? 'Connect Jobs';
        $desc = strip_tags((string) $job->description);
        $desc = mb_substr($desc, 0, 500);
        $posted = $job->created_at ? $job->created_at->toAtomString() : null;
        $updated = $job->updated_at ? $job->updated_at->toAtomString() : null;
        $validThrough = $job->expires_at ? $job->expires_at->toAtomString() : null;
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'JobPosting',
            'title' => $job->title,
            'description' => $desc,
            'datePosted' => $posted,
            'dateModified' => $updated,
            'employmentType' => 'FULL_TIME',
            'hiringOrganization' => [
                '@type' => 'Organization',
                'name' => $orgName,
                'url' => $job->company ? route('public.company.show', $job->company) : null,
            ],
            'jobLocation' => [
                '@type' => 'Place',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressCountry' => 'IQ',
                    'addressRegion' => $job->province,
                ]
            ],
            'applicantLocationRequirements' => [
                '@type' => 'Country',
                'name' => 'Iraq'
            ],
            'validThrough' => $validThrough,
            'identifier' => [
                '@type' => 'PropertyValue',
                'name' => $orgName,
                'value' => (string) $job->id,
            ],
            'url' => url()->current(),
        ];

        // Breadcrumbs schema
        $crumbs = [
            ['@type'=>'ListItem','position'=>1,'name'=>'الرئيسية','item'=>url('/')],
            ['@type'=>'ListItem','position'=>2,'name'=>'الوظائف','item'=>route('jobs.index')],
        ];
        if ($job->company) {
            $crumbs[] = ['@type'=>'ListItem','position'=>3,'name'=>$job->company->company_name,'item'=>route('public.company.show',$job->company)];
            $crumbs[] = ['@type'=>'ListItem','position'=>4,'name'=>$job->title,'item'=>route('jobs.show',$job)];
        } else {
            $crumbs[] = ['@type'=>'ListItem','position'=>3,'name'=>$job->title,'item'=>route('jobs.show',$job)];
        }
        $breadcrumbSchema = [
            '@context'=>'https://schema.org',
            '@type'=>'BreadcrumbList',
            'itemListElement'=>$crumbs,
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode(array_filter($schema), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>

</x-app-layout>

