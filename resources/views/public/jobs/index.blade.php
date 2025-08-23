<x-guest-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-6">الوظائف المتاحة</h1>

            <!-- Filters Bar -->
            <form method="GET" action="{{ route('jobs.index') }}" class="bg-white dark:bg-gray-800/70 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 md:p-6 shadow mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">ابحث</label>
                        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="كلمة مفتاحية، مسمى وظيفي..." class="w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:border-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">المحافظة</label>
                        <select name="province" class="w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:border-gray-700">
                            <option value="">الكل</option>
                            @foreach(($provinces ?? collect())->sort() as $p)
                                <option value="{{ $p }}" @selected(($province ?? '')===$p)>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">ترتيب</label>
                        <select name="sort" class="w-full rounded-xl border-gray-300 dark:bg-gray-900 dark:border-gray-700">
                            <option value="latest" @selected(($sort ?? 'latest')==='latest')>الأحدث</option>
                            <option value="oldest" @selected(($sort ?? '')==='oldest')>الأقدم</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex gap-3">
                    <button class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white">تطبيق</button>
                    <a href="{{ route('jobs.index') }}" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">إعادة ضبط</a>
                </div>
            </form>

            <!-- Jobs Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse($jobs as $job)
                    <a href="{{ route('jobs.show',$job) }}" class="group block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow hover:shadow-lg transition relative overflow-hidden">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-base text-indigo-600 font-semibold group-hover:underline">{{ $job->title }}</div>
                                <div class="text-xs text-gray-500">{{ optional($job->company)->company_name ?? '—' }}</div>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs bg-emerald-50 text-emerald-700">{{ $job->province }}</span>
                        </div>
                        <div class="mt-3 text-sm text-gray-700 dark:text-gray-300 line-clamp-3">{{ $job->description }}</div>
                        @if(!empty($job->districts))
                            <div class="mt-3 flex flex-wrap gap-1">
                                @foreach((array)$job->districts as $d)
                                    <span class="px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-[11px]">{{ $d }}</span>
                                @endforeach
                            </div>
                        @endif
                        <div class="mt-4 text-[11px] text-gray-500">#{{ $job->id }}</div>
                    </a>
                @empty
                    <div class="col-span-full text-center text-gray-500">لا توجد وظائف متاحة حالياً.</div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-8">{{ $jobs->links() }}</div>
        </div>
    </div>
</x-guest-layout>

