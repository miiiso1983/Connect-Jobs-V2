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

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-6">الوظائف المتاحة</h1>

            <!-- Filters Bar -->
            <form id="jobs-filter-form" method="GET" action="{{ route('jobs.index') }}" class="bg-base-100 border border-base-200 rounded-2xl p-4 md:p-6 shadow mb-6">
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
                    <button class="btn btn-primary">تطبيق</button>
                    <a href="{{ route('jobs.index') }}" class="btn btn-ghost">إعادة ضبط</a>

                </div>
            </form>

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

            <!-- Jobs Grid -->
            <div id="jobs-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse($jobs as $job)
                    <div class="group bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow hover:shadow-lg transition relative overflow-hidden">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <a href="{{ route('jobs.show',$job) }}" class="text-base text-primary font-semibold hover:underline">{{ $job->title }}</a>
                                <div class="text-xs text-gray-500">
                                    @if($job->company)
                                        <a href="{{ route('public.company.show',$job->company) }}" class="underline hover:text-primary">{{ $job->company->company_name }}</a>
                                    @else
                                        —
                                    @endif
                                    @if(optional($job->company)->industry)
                                        <span>&middot; {{ optional($job->company)->industry }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 rounded-full text-xs bg-emerald-50 text-emerald-700">{{ $job->province }}</span>
                                @if(!empty($job->created_at) && now()->diffInHours($job->created_at) <= 72)
                                    <span class="px-2 py-1 rounded-full text-xs bg-pink-50 text-pink-700">جديد</span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-3 text-sm text-gray-700 dark:text-gray-300 line-clamp-3">{{ $job->description }}</div>
                        @if(!empty($job->districts))
                            <div class="mt-3 flex flex-wrap gap-1">
                                @foreach((array)$job->districts as $d)
                                    <span class="px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-[11px]">{{ $d }}</span>
                                @endforeach
                            </div>
                        @endif
                        <div class="mt-4 flex items-center justify-between text-[12px] text-gray-500">
                            <div>المتقدمون: <strong>{{ $job->applications_count ?? 0 }}</strong></div>
                            @auth
                                @if(auth()->user()->role==='jobseeker')
                                    @php $isSaved = in_array($job->id, $savedIds ?? []); @endphp
                                    <form method="POST" action="{{ $isSaved ? route('jobs.unsave',$job) : route('jobs.save',$job) }}">
                                        @csrf
                                        @if($isSaved)
                                            @method('DELETE')
                                        @endif
                                        <button class="text-primary hover:underline" type="submit">{{ $isSaved ? 'إلغاء الحفظ' : 'حفظ لاحقاً' }}</button>
                                    </form>
                                @endif
                            @endauth
                        </div>
                        <div class="mt-1 text-[11px] text-gray-500">#{{ $job->id }}</div>
                    </div>
                @empty
                    <div class="col-span-full text-center text-gray-500">لا توجد وظائف متاحة حالياً.</div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div id="jobs-pagination" class="mt-8">{{ $jobs->links() }}</div>
        </div>
    </div>

    @push('scripts')
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
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "BreadcrumbList",
      "itemListElement": [
        {"@type": "ListItem", "position": 1, "name": "الصفحة الرئيسية", "item": "{{ url('/') }}"},
        {"@type": "ListItem", "position": 2, "name": "الوظائف المتاحة", "item": "{{ url('/jobs') }}"}
      ]
    }
    </script>
    @endpush
</x-guest-layout>

