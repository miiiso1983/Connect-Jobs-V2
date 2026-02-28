<x-app-layout>
    <x-slot name="header">
	        <div class="rounded-xl bg-gradient-to-br from-[#5B21B6] via-[#6D28D9] to-[#4C1D95] text-white p-6">
            <h1 class="text-2xl font-bold">تنبيهات الوظائف</h1>
	            <div class="mt-1 text-[#38BDF8] text-sm">إدارة التنبيهات الأسبوعية عبر البريد</div>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-base-200">
            @if($alerts->isEmpty())
                <div class="text-center text-gray-500">لا توجد تنبيهات محفوظة بعد.
                    <div class="mt-3">
                        <a href="{{ route('jobs.index') }}" class="btn btn-primary">إنشاء تنبيه من صفحة الوظائف</a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>المعايير</th>
                                <th>التكرار</th>
                                <th>القناة</th>
                                <th>الحالة</th>
                                <th>آخر إرسال</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alerts as $a)
                                @php
                                    $parts = array_filter([
                                        $a->q ? ('بحث: '.$a->q) : null,
                                        $a->province ? ('محافظة: '.$a->province) : null,
                                        $a->industry ? ('قطاع: '.$a->industry) : null,
                                        $a->job_title ? ('مسمى: '.$a->job_title) : null,
                                    ]);
                                @endphp
                                <tr>
                                    <td>{{ implode('، ', $parts) ?: 'الكل' }}</td>
                                    <td>{{ $a->frequency === 'weekly' ? 'أسبوعي' : $a->frequency }}</td>
                                    <td>{{ $a->channel === 'email' ? 'البريد' : $a->channel }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('jobseeker.alerts.toggle', $a->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <button class="badge {{ $a->enabled ? 'badge-success' : 'badge-ghost' }}">{{ $a->enabled ? 'مفعل' : 'موقوف' }}</button>
                                        </form>
                                    </td>
                                    <td>{{ optional($a->last_sent_at)->diffForHumans() ?? '—' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('jobseeker.alerts.destroy', $a->id) }}" onsubmit="return confirm('حذف التنبيه؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-ghost text-red-600">حذف</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

