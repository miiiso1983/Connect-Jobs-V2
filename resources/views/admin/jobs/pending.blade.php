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
                                <span class="px-2 py-1 rounded-full text-xs {{ $j->approved_by_admin ? 'bg-emerald-100 text-emerald-800':'bg-yellow-100 text-yellow-800' }}">{{ $j->approved_by_admin ? 'معتمدة' : 'بانتظار' }}</span>
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
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('admin.jobs.approve',$j) }}">
                                        @csrf
                                        <button class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs">موافقة</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.jobs.reject',$j) }}">
                                        @csrf
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

