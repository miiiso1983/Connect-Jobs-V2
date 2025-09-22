<table class="table">
    <thead>
        <tr>
            <th>المستخدم</th>
            <th>الاسم الكامل</th>
            <th>المسمى</th>
            <th>المحافظة</th>
            <th>التخصصات</th>
            <th>المناطق</th>
            <th>الجنس</th>
            <th>سيارة</th>
            <th>أُنشئ</th>
            <th></th>
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
                <td>{{ $s->full_name ?? '—' }}</td>
                <td>{{ $s->job_title ?? '—' }}</td>
                <td>{{ $s->province ?? '—' }}</td>
                <td>
                    <div class="flex flex-wrap gap-1">
                        @forelse((array)($s->specialities ?? []) as $sp)
                            <span class="badge badge-outline badge-sm">{{ $sp }}</span>
                        @empty
                            <span class="text-gray-400">-</span>
                        @endforelse
                    </div>
                </td>
                <td>
                    <div class="flex flex-wrap gap-1">
                        @forelse((array)($s->districts ?? []) as $d)
                            <span class="badge badge-ghost badge-sm">{{ $d }}</span>
                        @empty
                            <span class="text-gray-400">-</span>
                        @endforelse
                    </div>
                </td>
                <td>{{ $s->gender ?? '—' }}</td>
                <td>
                    <span class="badge {{ $s->own_car ? 'badge-success' : 'badge-ghost' }} badge-sm">{{ $s->own_car ? 'نعم' : 'لا' }}</span>
                </td>
                <td>{{ $s->user->created_at ?? '—' }}</td>
                <td class="whitespace-nowrap">
                    @if($context==='company')
                        <a href="{{ route('company.applicants.show', $s) }}" class="btn btn-xs">عرض الملف</a>
                    @else
                        <a href="{{ route('admin.jobseekers.show', $s) }}" class="btn btn-xs">عرض</a>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="10" class="text-center text-gray-500">لا نتائج.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="p-3">
    {{ $seekers->links() }}
</div>

