<x-app-layout>
    <x-slot name="header">
	        <div class="rounded-xl bg-gradient-to-br from-[#5B21B6] via-[#6D28D9] to-[#4C1D95] text-white p-6">
            <h2 class="text-xl font-bold">وظائف بانتظار الموافقة</h2>
	            <p class="text-[#38BDF8] text-sm mt-1">مراجعة والموافقة على الوظائف المقدمة من الشركات</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        {{-- Tip Alert --}}
        <div class="flex items-center gap-3 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-800 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-amber-800 dark:text-amber-300">تلميح: يمكنك الموافقة أو الرفض مباشرة من هنا. انقر على "عرض التفاصيل" لرؤية معلومات أكثر.</p>
        </div>

        @if (session('status'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-green-800 dark:text-green-400">{{ session('status') }}</span>
            </div>
        @endif

        <!-- Filters / Sorting -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="flex items-center gap-3 p-6 border-b border-gray-200 dark:border-gray-700">
	                <div class="w-10 h-10 rounded-lg bg-[#5B21B6] flex items-center justify-center">
	                    <svg class="w-5 h-5 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">خيارات الفلترة والترتيب</h3>
            </div>
            <form method="GET" class="p-6 grid grid-cols-1 md:grid-cols-6 gap-4">
                <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="بحث بعنوان/وصف" class="input input-bordered md:col-span-2">
                <input type="text" name="province" value="{{ $province ?? '' }}" placeholder="المحافظة" class="input input-bordered">
                <select name="approved" class="select select-bordered">
                    @php $approved ??= 'pending'; @endphp
                    <option value="pending" @selected(($approved??'pending')==='pending')>بانتظار</option>
                    <option value="approved" @selected(($approved??'pending')==='approved')>معتمدة</option>
                    <option value="all" @selected(($approved??'pending')==='all')>الكل</option>
                </select>
                <select name="status" class="select select-bordered">
                    @php $status ??= ''; @endphp
                    <option value="" @selected(($status??'')==='')>كل الحالات</option>
                    <option value="open" @selected(($status??'')==='open')>مفتوحة</option>
                    <option value="closed" @selected(($status??'')==='closed')>مغلقة</option>
                </select>
                <div class="flex items-center gap-2">
                    <select name="sort" class="select select-bordered flex-1">
                        <option value="id" @selected(($sort??'id')==='id')>#</option>
                        <option value="title" @selected(($sort??'id')==='title')>العنوان</option>
                        <option value="province" @selected(($sort??'id')==='province')>المحافظة</option>
                    </select>
                    <select name="dir" class="select select-bordered flex-1">
                        <option value="desc" @selected(($dir??'desc')==='desc')>تنازلي</option>
                        <option value="asc" @selected(($dir??'desc')==='asc')>تصاعدي</option>
                    </select>
                </div>
                <div class="md:col-span-6 flex gap-2 justify-end">
                    <a href="{{ route('admin.jobs.pending') }}" class="btn btn-ghost">إفراغ</a>
	                    <button class="px-6 py-2 rounded-lg bg-[#5B21B6] hover:bg-[#4C1D95] text-white font-medium transition-colors">تطبيق</button>
                </div>
            </form>
        </div>

        {{-- Jobs Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="flex items-center gap-3 p-6 border-b border-gray-200 dark:border-gray-700">
	                <div class="w-10 h-10 rounded-lg bg-[#38BDF8] flex items-center justify-center">
	                    <svg class="w-5 h-5 text-[#4C1D95]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">قائمة الوظائف</h3>
	                <span class="bg-[#5B21B6] text-white text-xs font-bold px-3 py-1 rounded-full">{{ $jobs->count() }} وظيفة</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">العنوان</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الشركة</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">المحافظة</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الحالة</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">إجراءات</th>
                        </tr>
                    </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($jobs as $j)
                        <tr>
                            <td class="px-4 py-2">{{ $j->id }}</td>
                            <td class="px-4 py-2">{{ $j->title }}</td>
                            <td class="px-4 py-2">{{ optional($j->company)->company_name }}</td>
                            <td class="px-4 py-2">{{ $j->province }}</td>
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-1">
                                    <span class="px-2 py-1 rounded-full text-xs {{ $j->approved_by_admin ? 'bg-emerald-100 text-emerald-800':'bg-yellow-100 text-yellow-800' }}">{{ $j->approved_by_admin ? 'معتمدة' : 'بانتظار' }}</span>
                                    <span class="px-2 py-1 rounded-full text-xs {{ $j->status==='open' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800' }}">{{ $j->status==='open' ? 'مفتوحة' : 'مغلقة' }}</span>
                                </div>
                                @if(!empty($j->admin_reject_reason))
                                    <div class="mt-1 text-[11px] text-red-700">سبب الرفض: {{ $j->admin_reject_reason }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                <details class="mt-2">
                                    <summary class="cursor-pointer text-xs text-slate-600">عرض التفاصيل</summary>
                                    <div class="mt-2 p-3 bg-slate-50 dark:bg-slate-900 rounded">
                                        <div class="text-sm text-slate-700 dark:text-slate-300"><strong>الوصف:</strong> {{ $j->description }}</div>
                                        <div class="text-sm text-slate-700 dark:text-slate-300"><strong>المتطلبات:</strong> {{ $j->requirements ?? '—' }}</div>
                                        <div class="text-sm text-slate-700 dark:text-slate-300"><strong>المناطق:</strong> {{ is_array($j->districts) ? implode('، ', $j->districts) : ($j->districts ?? '—') }}</div>
                                        @if($j->company)
                                            <div class="mt-2 text-xs text-slate-500">الشركة: {{ $j->company->company_name }} — المكتب العلمي: {{ $j->company->scientific_office_name }} — المسمى الوظيفي: {{ $j->company->company_job_title }} — الهاتف: {{ $j->company->mobile_number }} — محافظة الشركة: {{ $j->company->province }}</div>
                                        @endif
                                    </div>
                                </details>
                                <div class="flex flex-col gap-2 mt-2">
                                    <form method="POST" action="{{ route('admin.jobs.approve',$j) }}">
                                        @csrf
                                        <button class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs">موافقة</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.jobs.reject',$j) }}" class="grid grid-cols-1 md:grid-cols-4 gap-2 items-start">
                                        @csrf
                                        <input type="text" name="reason" class="input input-bordered input-xs md:col-span-3" placeholder="اكتب سبب الرفض (اختياري)" />
                                        <button class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs">رفض</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.jobs.destroy',$j) }}" x-data="{open:false}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" @click="open=true" class="px-3 py-1.5 rounded-lg bg-red-700 hover:bg-red-800 text-white text-xs">حذف نهائياً</button>
                                        <x-confirm-modal title="تأكيد الحذف" message="سيتم حذف الوظيفة نهائياً. هل أنت متأكد؟" />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

