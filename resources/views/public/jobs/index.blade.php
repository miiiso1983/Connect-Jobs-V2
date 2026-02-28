<x-guest-layout>
    @php
        $titleParts = [];
        if (!empty($province)) $titleParts[] = "المحافظة: $province";
        if (!empty($industry)) $titleParts[] = "القطاع: $industry";
        if (!empty($jobTitleFilter)) $titleParts[] = "المسمى الوظيفي: $jobTitleFilter";
        $pageTitle = 'الوظائف المتاحة' . (count($titleParts) ? ' - '.implode('، ',$titleParts) : '');
        $pageDesc = 'استكشف أحدث الوظائف المتاحة' . (count($titleParts) ? ' مع فلاتر: '.implode('، ',$titleParts) : '');
    @endphp
    @section('meta_title', $pageTitle.' - Connect Jobs')
    @section('meta_description', $pageDesc)

    {{-- Hero Header --}}
	    <section class="relative overflow-hidden bg-gradient-to-br from-[#5B21B6] via-[#6D28D9] to-[#4C1D95] text-white py-12">
        <div class="absolute -top-20 -left-20 h-72 w-72 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-16 -right-24 h-80 w-80 bg-black/10 rounded-full blur-3xl"></div>
        <div class="relative max-w-7xl mx-auto px-4 text-center">
            <div class="flex items-center justify-center gap-3 mb-4">
	                <div class="w-12 h-12 rounded-xl bg-[#38BDF8]/20 flex items-center justify-center">
	                    <svg class="w-6 h-6 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold mb-2">الوظائف المتاحة</h1>
	            <p class="text-[#38BDF8] text-lg">اكتشف أفضل الفرص الوظيفية المناسبة لمهاراتك</p>
            @if(count($titleParts))
                <div class="flex flex-wrap justify-center gap-2 mt-4">
                    @foreach($titleParts as $part)
                        <span class="px-3 py-1 rounded-full bg-white/10 text-sm">{{ $part }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4">
            {{-- Filter Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden mb-8">
                <div class="flex items-center gap-3 p-6 border-b border-gray-200 dark:border-gray-700">
	                    <div class="w-10 h-10 rounded-lg bg-[#5B21B6] flex items-center justify-center">
	                        <svg class="w-5 h-5 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">تصفية الوظائف</h3>
                </div>
                <form id="jobs-filter-form" method="GET" action="{{ route('jobs.index') }}" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">ابحث</label>
                        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="كلمة مفتاحية، مسمى وظيفي..." class="input input-bordered w-full"/>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">المحافظة</label>
                        <select name="province" class="select select-bordered w-full">
                            <option value="">الكل</option>
                            @foreach(($provinces ?? collect())->sort() as $p)
                                <option value="{{ $p }}" @selected(($province ?? '')===$p)>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">ترتيب</label>
                        <select name="sort" class="select select-bordered w-full">
                            <option value="latest" @selected(($sort ?? 'latest')==='latest')>الأحدث</option>
                            <option value="oldest" @selected(($sort ?? '')==='oldest')>الأقدم</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">القطاع/الصناعة</label>
                        <select name="industry" class="select select-bordered w-full">
                            <option value="">الكل</option>
                            @foreach(($industries ?? collect()) as $ind)
                                <option value="{{ $ind }}" @selected(($industry ?? '')===$ind)>{{ $ind }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">المسمى الوظيفي</label>
                        <select name="job_title" class="select select-bordered w-full">
                            <option value="">الكل</option>
                            @foreach(($jobTitles ?? collect())->sort() as $t)
                                <option value="{{ $t }}" @selected(($jobTitleFilter ?? '')===$t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">الشركة</label>
                        <select name="company_id" class="select select-bordered w-full">
                            <option value="">الكل</option>
                            @foreach(($companies ?? collect()) as $c)
                                <option value="{{ $c->id }}" @selected(($companyId ?? 0)===$c->id)>{{ $c->company_name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
                    <div class="mt-4 flex flex-wrap items-center gap-3">
	                        <button type="submit" class="px-6 py-2.5 rounded-lg bg-gradient-to-r from-[#5B21B6] to-[#6D28D9] hover:from-[#4C1D95] hover:to-[#5B21B6] text-white font-bold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            تطبيق الفلتر
                        </button>
                        <a href="{{ route('jobs.index') }}" class="px-6 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium transition-all duration-300 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            إعادة ضبط
                        </a>
                    </div>
                </form>
            </div>

            <!-- Skeleton (shown on navigation) -->
            <div id="jobs-skeleton" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @for($i=0;$i<6;$i++)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow animate-pulse">
                    <div class="h-4 w-32 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    <div class="mt-2 h-3 w-20 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    <div class="mt-4 space-y-2">
                        <div class="h-3 w-full bg-gray-200 dark:bg-gray-700 rounded"></div>
                        <div class="h-3 w-5/6 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        <div class="h-3 w-2/3 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    </div>
                </div>
                @endfor
                @auth
                    @if(auth()->user()->role==='jobseeker')
                        <div class="mt-3 flex items-center gap-3">
                            <form method="POST" action="{{ route('jobseeker.alerts.store') }}" class="inline">
                                @csrf
                                <input type="hidden" name="q" value="{{ $q ?? '' }}"/>
                                <input type="hidden" name="province" value="{{ $province ?? '' }}"/>
                                <input type="hidden" name="industry" value="{{ $industry ?? '' }}"/>
                                <input type="hidden" name="job_title" value="{{ $jobTitleFilter ?? '' }}"/>
                                <button type="submit" class="btn btn-secondary">
                                    حفظ تنبيه أسبوعي عبر البريد
                                </button>
                            </form>
                            <a href="{{ route('jobseeker.alerts.index') }}" class="link link-primary text-sm">إدارة التنبيهات</a>
                        </div>
                    @endif
                @endauth

            </div>

            {{-- Jobs Results Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="flex items-center gap-3 p-6 border-b border-gray-200 dark:border-gray-700">
	                    <div class="w-10 h-10 rounded-lg bg-[#38BDF8] flex items-center justify-center">
	                        <svg class="w-5 h-5 text-[#4C1D95]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">نتائج البحث</h3>
	                    <span class="bg-[#5B21B6] text-white text-xs font-bold px-3 py-1 rounded-full">{{ $jobs->total() }} وظيفة</span>
                </div>
                <div class="p-6">
                    <div id="jobs-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        @forelse($jobs as $job)
                            <div class="group bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-750 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1 relative overflow-hidden">
                                {{-- New badge --}}
                                @if(!empty($job->created_at) && now()->diffInHours($job->created_at) <= 72)
                                    <div class="absolute top-3 left-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-pink-500 to-rose-500 text-white shadow">جديد</span>
                                    </div>
                                @endif

                                <div class="flex items-start gap-3">
                                    @if($job->company && !empty($job->company->profile_image))
	                                        <img src="{{ Storage::url($job->company->profile_image) }}" alt="{{ $job->company->company_name }}" class="w-12 h-12 rounded-lg object-cover ring-2 ring-[#5B21B6]/20"/>
                                    @else
	                                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-[#5B21B6] to-[#6D28D9] flex items-center justify-center">
	                                            <svg class="w-6 h-6 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
	                                        <a href="{{ route('jobs.show',$job) }}" class="block text-base font-bold text-[#5B21B6] dark:text-[#38BDF8] hover:underline truncate">{{ $job->title }}</a>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            @if($job->company)
	                                                <a href="{{ route('public.company.show',$job->company) }}" class="hover:text-[#5B21B6] dark:hover:text-[#38BDF8]">{{ $job->company->company_name }}</a>
                                            @else
                                                —
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2 mt-3">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        {{ $job->province }}
                                    </span>
                                    @if(optional($job->company)->industry)
	                                        <span class="px-2 py-1 rounded-full text-xs bg-[#5B21B6]/10 dark:bg-[#5B21B6]/30 text-[#5B21B6] dark:text-[#38BDF8]">{{ optional($job->company)->industry }}</span>
                                    @endif
                                </div>

                                <div class="mt-3 text-sm text-gray-600 dark:text-gray-300 line-clamp-2">{{ $job->description }}</div>

                                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        <span>{{ $job->applications_count ?? 0 }} متقدم</span>
                                    </div>
                                    @auth
                                        @if(auth()->user()->role==='jobseeker')
                                            @php $isSaved = in_array($job->id, $savedIds ?? []); @endphp
                                            <form method="POST" action="{{ $isSaved ? route('jobs.unsave',$job) : route('jobs.save',$job) }}">
                                                @csrf
                                                @if($isSaved) @method('DELETE') @endif
	                                                <button class="text-xs text-[#5B21B6] dark:text-[#38BDF8] hover:underline font-medium flex items-center gap-1" type="submit">
                                                    <svg class="w-3.5 h-3.5" fill="{{ $isSaved ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                                    {{ $isSaved ? 'محفوظ' : 'حفظ' }}
                                                </button>
                                            </form>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12">
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400 text-lg">لا توجد وظائف متاحة حالياً</p>
                                <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">جرب تغيير معايير البحث</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div id="jobs-pagination" class="mt-8">{{ $jobs->links() }}</div>
        </div>
    </div>

    <script>
    (function(){
      const form = document.getElementById('jobs-filter-form');
      const grid = document.getElementById('jobs-grid');
      const skel = document.getElementById('jobs-skeleton');
      const pag = document.getElementById('jobs-pagination');
      function showSkeleton(){ if(skel){ skel.classList.remove('hidden'); } if(grid){ grid.classList.add('opacity-30','pointer-events-none'); } }
      if(form){ form.addEventListener('submit', function(){ showSkeleton(); }); }
      if(pag){ pag.addEventListener('click', function(e){ const t = e.target.closest('a'); if(t){ showSkeleton(); } }, true); }
    })();
    </script>
    <script type="application/ld+json">{!! $breadcrumbsJson ?? '' !!}</script>
</x-guest-layout>

