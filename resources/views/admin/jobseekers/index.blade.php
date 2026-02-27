<x-app-layout>
    <x-slot name="header">
        <div class="rounded-xl bg-gradient-to-br from-[#0D2660] via-[#102E66] to-[#0A1E46] text-white p-6">
            <h2 class="text-xl font-bold">إدارة الباحثين عن عمل</h2>
            <p class="text-[#E7C66A] text-sm mt-1">فلترة، عرض، تغيير الحالة، حذف</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-green-800 dark:text-green-400">{{ session('status') }}</span>
            </div>
        @endif

        {{-- KPIs --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                <div class="w-10 h-10 rounded-lg bg-[#0D2660] flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#E7C66A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">إحصائيات الباحثين عن عمل</h3>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="text-center p-4 rounded-lg bg-gradient-to-br from-[#0D2660]/5 to-[#0D2660]/10 dark:from-[#0D2660]/20 dark:to-[#0D2660]/30">
                    <div class="text-3xl font-bold text-[#0D2660] dark:text-[#E7C66A]">{{ $totalSeekers }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">إجمالي الباحثين</div>
                </div>
                <div class="text-center p-4 rounded-lg bg-gradient-to-br from-emerald-500/5 to-emerald-500/10 dark:from-emerald-500/20 dark:to-emerald-500/30">
                    <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $activeUsers }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">نشط</div>
                </div>
                <div class="text-center p-4 rounded-lg bg-gradient-to-br from-amber-500/5 to-amber-500/10 dark:from-amber-500/20 dark:to-amber-500/30">
                    <div class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $suspendedUsers }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">موقوف</div>
                </div>
                @if(!is_null($completedCount))
                <div class="text-center p-4 rounded-lg bg-gradient-to-br from-indigo-500/5 to-indigo-500/10 dark:from-indigo-500/20 dark:to-indigo-500/30">
                    <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $completedCount }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">أكمل الملف</div>
                </div>
                @endif
                @if(!is_null($cvCount))
                <div class="text-center p-4 rounded-lg bg-gradient-to-br from-purple-500/5 to-purple-500/10 dark:from-purple-500/20 dark:to-purple-500/30">
                    <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $cvCount }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">سيرة ذاتية مرفوعة</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="flex items-center gap-3 p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="w-10 h-10 rounded-lg bg-[#0D2660] flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#E7C66A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">خيارات الفلترة</h3>
            </div>
            <form method="GET" class="p-6 grid grid-cols-1 md:grid-cols-6 gap-3">
            <div class="md:col-span-2">
                <x-input-label for="q" value="بحث (اسم، بريد، مسمى، تخصص)" />
                <input type="text" id="q" name="q" value="{{ $q }}" class="input input-bordered w-full" />
            </div>
            <div>
                <x-input-label for="province" value="المحافظة" />
                <select id="province" name="province" class="select select-bordered w-full">
                    <option value="">—</option>
                    @foreach($provinces as $p)
                        <option value="{{ $p }}" @selected($province===$p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="status" value="الحالة" />
                <select id="status" name="status" class="select select-bordered w-full">
                    <option value="">—</option>
                    <option value="active" @selected($status==='active')>نشط</option>
                    <option value="suspended" @selected($status==='suspended')>موقوف</option>
                </select>
            </div>
            <div>
                <x-input-label for="per_page" value="عدد النتائج/صفحة" />
                <select id="per_page" name="per_page" class="select select-bordered w-full">
                    @foreach([10,20,50,100,200] as $pp)
                        <option value="{{ $pp }}" @selected($perPage==$pp)>{{ $pp }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="created_from" value="إنشاء من" />
                <input type="date" id="created_from" name="created_from" value="{{ $createdFrom }}" class="input input-bordered w-full" />
            </div>
            <div>
                <x-input-label for="created_to" value="إنشاء إلى" />
                <input type="date" id="created_to" name="created_to" value="{{ $createdTo }}" class="input input-bordered w-full" />
            </div>
            <div>
                <x-input-label for="last_seen_from" value="آخر دخول من" />
                <input type="date" id="last_seen_from" name="last_seen_from" value="{{ $lastSeenFrom }}" class="input input-bordered w-full" />
            </div>
            <div>
                <x-input-label for="last_seen_to" value="آخر دخول إلى" />
                <input type="date" id="last_seen_to" name="last_seen_to" value="{{ $lastSeenTo }}" class="input input-bordered w-full" />
            </div>
            <div>
                <x-input-label for="profile_completed" value="أكمل الملف؟" />
                <select id="profile_completed" name="profile_completed" class="select select-bordered w-full">
                    <option value="">—</option>
                    <option value="1" @selected(($profileCompleted ?? '')==='1')>نعم</option>
                    <option value="0" @selected(($profileCompleted ?? '')==='0')>لا</option>
                </select>
            </div>
            <div>
                <x-input-label for="has_cv" value="لديه سيرة ذاتية؟" />
                <select id="has_cv" name="has_cv" class="select select-bordered w-full">
                    <option value="">—</option>
                    <option value="1" @selected(($hasCv ?? '')==='1')>نعم</option>
                    <option value="0" @selected(($hasCv ?? '')==='0')>لا</option>
                </select>
            </div>
	            <div class="md:col-span-2">
	                <x-input-label for="university_name" value="اسم الجامعة" />
	                <input type="text" id="university_name" name="university_name" value="{{ $universityName ?? '' }}" class="input input-bordered w-full" />
	            </div>
	            <div class="md:col-span-2">
	                <x-input-label for="college_name" value="اسم الكلية" />
	                <input type="text" id="college_name" name="college_name" value="{{ $collegeName ?? '' }}" class="input input-bordered w-full" />
	            </div>
	            <div class="md:col-span-2">
	                <x-input-label for="department_name" value="اسم القسم" />
	                <input type="text" id="department_name" name="department_name" value="{{ $departmentName ?? '' }}" class="input input-bordered w-full" />
	            </div>
	            <div>
	                <x-input-label for="graduation_year" value="سنة التخرج" />
	                <input type="number" id="graduation_year" name="graduation_year" value="{{ $graduationYear ?? '' }}" class="input input-bordered w-full" placeholder="مثال: 2024" />
	            </div>
	            <div>
	                <x-input-label for="is_fresh_graduate" value="خريج جديد؟" />
	                <select id="is_fresh_graduate" name="is_fresh_graduate" class="select select-bordered w-full">
	                    <option value="">—</option>
	                    <option value="1" @selected(($isFreshGraduate ?? '')==='1')>نعم</option>
	                    <option value="0" @selected(($isFreshGraduate ?? '')==='0')>لا</option>
	                </select>
	            </div>
	            <div>
	                <x-input-label for="cv_verified" value="توثيق السيرة الذاتية" />
	                <select id="cv_verified" name="cv_verified" class="select select-bordered w-full">
	                    <option value="">—</option>
	                    <option value="1" @selected(($cvVerified ?? '')==='1')>موثق</option>
	                    <option value="0" @selected(($cvVerified ?? '')==='0')>غير موثق</option>
	                </select>
	            </div>
            <div class="md:col-span-6 flex gap-2">
                <button class="px-6 py-2 rounded-lg bg-[#0D2660] hover:bg-[#0A1E46] text-white font-medium transition-colors">تطبيق</button>
                <a href="{{ route('admin.jobseekers.index') }}" class="btn btn-ghost">تفريغ</a>
            </div>
            </form>
        </div>

        {{-- Jobseekers Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="flex items-center gap-3 p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="w-10 h-10 rounded-lg bg-[#E7C66A] flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#0D2660]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">قائمة الباحثين عن عمل</h3>
                <span class="bg-[#0D2660] text-white text-xs font-bold px-3 py-1 rounded-full">{{ $seekers->total() }} باحث</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">المستخدم</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الاسم الكامل</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">المحافظة</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">المسمى</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الحالة</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الملف</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">السيرة الذاتية</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">أُنشئ</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">آخر ظهور</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">إجراءات</th>
                        </tr>
                    </thead>
                <tbody>
                    @forelse($seekers as $s)
                        <tr>
                            <td class="whitespace-nowrap">
                                <div class="text-sm">
                                    <div class="font-semibold">{{ $s->user->name ?? '—' }}</div>
                                    <div class="text-gray-500">{{ $s->user->email ?? '—' }}</div>
                                </div>
                            </td>
	                            <td>
	                                <div class="font-medium">{{ $s->full_name ?? '—' }}</div>
	                                @php($edu = collect([$s->university_name ?? null, $s->college_name ?? null, $s->department_name ?? null])->filter()->implode(' — '))
	                                @if($edu)
	                                    <div class="text-xs text-gray-500 mt-1">{{ $edu }}</div>
	                                @endif
	                                @if(($s->graduation_year ?? null))
	                                    <div class="text-xs text-gray-500">سنة التخرج: {{ $s->graduation_year }}</div>
	                                @endif
	                                <div class="mt-1 flex flex-wrap gap-1">
	                                    @if(($s->is_fresh_graduate ?? false))
	                                        <span class="badge badge-info badge-sm">خريج جديد</span>
	                                    @endif
	                                </div>
	                            </td>
                            <td>{{ $s->province ?? '—' }}</td>
                            <td>{{ $s->job_title ?? '—' }}</td>
                            <td>
                                <span class="badge {{ ($s->user->status ?? 'active')==='active' ? 'badge-success' : 'badge-ghost' }}">
                                    {{ $s->user->status ?? 'active' }}
                                </span>
                            </td>
                            <td>
                                @php($pc = $s->profile_completed ?? null)
                                @if($pc===null)
                                    —
                                @else
                                    <span class="badge {{ $pc ? 'badge-success' : 'badge-ghost' }}">{{ $pc ? 'مكتمل' : 'غير مكتمل' }}</span>
                                @endif
                            </td>
                            <td>
                                @php($cv = $s->cv_file ?? null)
                                @if($cv===null)
                                    —
                                @else
	                                    <div class="flex flex-wrap gap-1">
	                                        <span class="badge {{ ($cv !== '') ? 'badge-success' : 'badge-ghost' }}">{{ ($cv !== '') ? 'مرفوعة' : 'لا' }}</span>
	                                        @if(($s->cv_verified ?? false))
	                                            <span class="badge badge-success">موثق</span>
	                                        @endif
	                                    </div>
                                @endif
                            </td>
                            <td>{{ $s->user->created_at ?? '—' }}</td>
                            @php($ts = $lastSeenTs[$s->user_id] ?? null)
                            <td>{{ $ts ? \Carbon\Carbon::createFromTimestamp($ts)->toDateTimeString() : '—' }}</td>
                            <td class="whitespace-nowrap">
                                <a href="{{ route('admin.seekers.show', $s) }}" class="btn btn-xs btn-primary">عرض الملف</a>
                                <form method="POST" action="{{ route('admin.jobseekers.toggle', $s->user) }}" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <button class="btn btn-xs">{{ ($s->user->status ?? 'active')==='active' ? 'إيقاف' : 'تنشيط' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.jobseekers.destroy', $s->user) }}" class="inline" onsubmit="return confirm('تأكيد الحذف؟ لا يمكن التراجع.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-xs btn-error">حذف</button>
                                </form>
                            </td>
                        </tr>


                    @empty
                        <tr><td colspan="10" class="text-center py-8 text-gray-500 dark:text-gray-400">لا نتائج.</td></tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="flex justify-center">
            {{ $seekers->links() }}
        </div>
    </div>
</x-app-layout>

