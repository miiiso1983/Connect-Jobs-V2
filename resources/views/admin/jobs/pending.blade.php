<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">وظائف بانتظار الموافقة</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-3 text-sm">
            تلميح: يمكنك الموافقة أو الرفض مباشرة من هنا. انقر على العنوان لرؤية تفاصيل أكثر.
        </div>
        @if (session('status'))
            <div class="p-3 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-200">{{ session('status') }}</div>
        @endif

        <!-- Filters / Sorting -->
        <form method="GET" class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 grid grid-cols-1 md:grid-cols-6 gap-3">
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="بحث بعنوان/وصف" class="input input-bordered input-sm md:col-span-2">
            <input type="text" name="province" value="{{ $province ?? '' }}" placeholder="المحافظة" class="input input-bordered input-sm">
            <select name="approved" class="select select-bordered select-sm">
                @php $approved ??= 'pending'; @endphp
                <option value="pending" @selected(($approved??'pending')==='pending')>بانتظار</option>
                <option value="approved" @selected(($approved??'pending')==='approved')>معتمدة</option>
                <option value="all" @selected(($approved??'pending')==='all')>الكل</option>
            </select>
            <select name="status" class="select select-bordered select-sm">
                @php $status ??= ''; @endphp
                <option value="" @selected(($status??'')==='')>كل الحالات</option>
                <option value="open" @selected(($status??'')==='open')>مفتوحة</option>
                <option value="closed" @selected(($status??'')==='closed')>مغلقة</option>
            </select>
            <div class="flex items-center gap-2">
                <select name="sort" class="select select-bordered select-sm">
                    <option value="id" @selected(($sort??'id')==='id')>#</option>
                    <option value="title" @selected(($sort??'id')==='title')>العنوان</option>
                    <option value="province" @selected(($sort??'id')==='province')>المحافظة</option>
                </select>
                <select name="dir" class="select select-bordered select-sm">
                    <option value="desc" @selected(($dir??'desc')==='desc')>تنازلي</option>
                    <option value="asc" @selected(($dir??'desc')==='asc')>تصاعدي</option>
                </select>
            </div>
            <div class="md:col-span-6 flex gap-2 justify-end">
                <a href="{{ route('admin.jobs.pending') }}" class="btn btn-ghost btn-sm">إفراغ</a>
                <button class="btn btn-primary btn-sm">تطبيق</button>
            </div>
        </form>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-start">#</th>
                        <th class="px-4 py-3 text-start">العنوان</th>
                        <th class="px-4 py-3 text-start">الشركة</th>
                        <th class="px-4 py-3 text-start">المحافظة</th>
                        <th class="px-4 py-3 text-start">الحالة</th>
                        <th class="px-4 py-3 text-start">إجراءات</th>
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
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

