<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">وظائف بانتظار الموافقة</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
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
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded-full text-xs {{ $j->approved_by_admin ? 'bg-emerald-100 text-emerald-800':'bg-yellow-100 text-yellow-800' }}">{{ $j->approved_by_admin ? 'معتمدة' : 'بانتظار' }}</span>
                            </td>
                            <td class="px-4 py-2">
                                <form method="POST" action="{{ route('admin.jobs.approve',$j) }}">
                                    @csrf
                                    <button class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs">موافقة</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

