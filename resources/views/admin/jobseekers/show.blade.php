<x-app-layout>
    <x-slot name="header">
        <div class="rounded-xl bg-gradient-to-br from-[#0D2660] via-[#102E66] to-[#0A1E46] text-white p-6">
            <h2 class="text-xl font-bold">ملف الباحث عن عمل</h2>
            <p class="text-[#E7C66A] text-sm mt-1">عرض تفصيلي — للإدمن</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-sm text-gray-500">الاسم</div>
                    <div class="font-semibold">{{ $jobSeeker->full_name ?? $jobSeeker->user->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">البريد</div>
                    <div class="font-mono">{{ $jobSeeker->user->email ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">المسمى</div>
                    <div>{{ $jobSeeker->job_title ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">المحافظة</div>
                    <div>{{ $jobSeeker->province ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">التخصصات</div>
                    <div class="flex flex-wrap gap-1">
                        @foreach((array)($jobSeeker->specialities ?? []) as $sp)
                            <span class="badge badge-outline badge-sm">{{ $sp }}</span>
                        @endforeach
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">المناطق</div>
                    <div class="flex flex-wrap gap-1">
                        @foreach((array)($jobSeeker->districts ?? []) as $d)
                            <span class="badge badge-ghost badge-sm">{{ $d }}</span>
                        @endforeach
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">الجنس</div>
                    <div>{{ $jobSeeker->gender ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">سيارة</div>
                    <div><span class="badge {{ $jobSeeker->own_car ? 'badge-success' : 'badge-ghost' }} badge-sm">{{ $jobSeeker->own_car ? 'نعم' : 'لا' }}</span></div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">المؤهل العلمي</div>
                    <div>{{ $jobSeeker->education_level ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">سنوات الخبرة/المستوى</div>
                    <div>{{ $jobSeeker->experience_level ?? '—' }}</div>
                </div>
                <div class="md:col-span-2">
                    <div class="text-sm text-gray-500">المهارات</div>
                    <pre class="text-sm whitespace-pre-wrap">{{ $jobSeeker->skills ?? '—' }}</pre>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <div class="font-semibold mb-2">الطلبات</div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الوظيفة</th>
                            <th>الحالة</th>
                            <th>تاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($jobSeeker->applications ?? []) as $a)
                            <tr>
                                <td>{{ $a->id }}</td>
                                <td>{{ $a->job->title ?? '—' }}</td>
                                <td>{{ $a->status ?? '—' }}</td>
                                <td>{{ $a->created_at ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-gray-500">لا يوجد</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

