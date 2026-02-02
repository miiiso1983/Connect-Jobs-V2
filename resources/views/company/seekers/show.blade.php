<x-app-layout>
    <x-slot name="header">
        <div class="rounded-xl bg-gradient-to-br from-[#0D2660] via-[#102E66] to-[#0A1E46] text-white p-6">
            <h2 class="text-xl font-bold">ملف الباحث عن عمل</h2>
            <p class="text-[#E7C66A] text-sm mt-1">عرض تفصيلي كامل — {{ ($context ?? 'company') === 'admin' ? 'للإدمن' : 'للشركة' }}</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
        {{-- Profile Image & Basic Info --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <div class="flex flex-col md:flex-row gap-6">
                {{-- Profile Image --}}
                <div class="flex-shrink-0">
                    @if($jobSeeker->profile_image)
                        <img src="{{ asset('storage/' . $jobSeeker->profile_image) }}" alt="صورة الملف" class="w-32 h-32 rounded-full object-cover border-4 border-primary/20">
                    @else
                        <div class="w-32 h-32 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        </div>
                    @endif
                </div>
                {{-- Basic Info --}}
                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $jobSeeker->full_name ?? $jobSeeker->user->name ?? '—' }}</h3>
                    <p class="text-lg text-primary mt-1">{{ $jobSeeker->job_title ?? '—' }}</p>
                    <div class="flex flex-wrap gap-4 mt-3 text-sm text-gray-600 dark:text-gray-400">
                        <span><strong>البريد:</strong> {{ $jobSeeker->user->email ?? '—' }}</span>
                        <span><strong>المحافظة:</strong> {{ $jobSeeker->province ?? '—' }}</span>
                        <span><strong>الجنس:</strong> {{ $jobSeeker->gender === 'male' ? 'ذكر' : ($jobSeeker->gender === 'female' ? 'أنثى' : ($jobSeeker->gender ?? '—')) }}</span>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <span class="badge {{ $jobSeeker->profile_completed ? 'badge-success' : 'badge-warning' }}">{{ $jobSeeker->profile_completed ? 'الملف مكتمل' : 'الملف غير مكتمل' }}</span>
                        <span class="badge {{ $jobSeeker->own_car ? 'badge-info' : 'badge-ghost' }}">{{ $jobSeeker->own_car ? 'يملك سيارة' : 'لا يملك سيارة' }}</span>
                        @if($jobSeeker->cv_file)
                            <a href="{{ asset('storage/' . $jobSeeker->cv_file) }}" target="_blank" class="badge badge-primary gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                تحميل السيرة الذاتية
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Detailed Information --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h4 class="text-lg font-semibold mb-4 border-b pb-2">المعلومات التفصيلية</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-sm text-gray-500">المؤهل العلمي</div>
                    <div class="font-medium">{{ $jobSeeker->education_level ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">سنوات الخبرة / المستوى</div>
                    <div class="font-medium">{{ $jobSeeker->experience_level ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">التخصصات</div>
                    <div class="flex flex-wrap gap-1 mt-1">
                        @forelse((array)($jobSeeker->specialities ?? []) as $sp)
                            <span class="badge badge-outline badge-sm">{{ $sp }}</span>
                        @empty
                            <span class="text-gray-400">—</span>
                        @endforelse
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">المناطق المفضلة للعمل</div>
                    <div class="flex flex-wrap gap-1 mt-1">
                        @forelse((array)($jobSeeker->districts ?? []) as $d)
                            <span class="badge badge-ghost badge-sm">{{ $d }}</span>
                        @empty
                            <span class="text-gray-400">—</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary --}}
        @if($jobSeeker->summary)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h4 class="text-lg font-semibold mb-4 border-b pb-2">نبذة شخصية</h4>
            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $jobSeeker->summary }}</p>
        </div>
        @endif

        {{-- Skills --}}
        @if($jobSeeker->skills)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h4 class="text-lg font-semibold mb-4 border-b pb-2">المهارات</h4>
            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $jobSeeker->skills }}</p>
        </div>
        @endif

        {{-- Qualifications --}}
        @if($jobSeeker->qualifications)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h4 class="text-lg font-semibold mb-4 border-b pb-2">المؤهلات العلمية</h4>
            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $jobSeeker->qualifications }}</p>
        </div>
        @endif

        {{-- Experiences --}}
        @if($jobSeeker->experiences)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h4 class="text-lg font-semibold mb-4 border-b pb-2">الخبرات العملية</h4>
            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $jobSeeker->experiences }}</p>
        </div>
        @endif

        {{-- Languages --}}
        @if($jobSeeker->languages)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h4 class="text-lg font-semibold mb-4 border-b pb-2">اللغات</h4>
            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $jobSeeker->languages }}</p>
        </div>
        @endif

        {{-- Applications (Admin only or if loaded) --}}
        @if(($context ?? 'company') === 'admin' && $jobSeeker->applications && count($jobSeeker->applications) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h4 class="text-lg font-semibold mb-4 border-b pb-2">الطلبات المقدمة ({{ count($jobSeeker->applications) }})</h4>
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الوظيفة</th>
                            <th>الحالة</th>
                            <th>تاريخ التقديم</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jobSeeker->applications as $a)
                            <tr>
                                <td>{{ $a->id }}</td>
                                <td>{{ $a->job->title ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $a->status === 'accepted' ? 'badge-success' : ($a->status === 'rejected' ? 'badge-error' : 'badge-warning') }} badge-sm">
                                        {{ $a->status === 'accepted' ? 'مقبول' : ($a->status === 'rejected' ? 'مرفوض' : ($a->status === 'pending' ? 'قيد المراجعة' : ($a->status ?? '—'))) }}
                                    </span>
                                </td>
                                <td>{{ $a->applied_at ?? $a->created_at ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Back Button --}}
        <div class="flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-ghost">
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                رجوع
            </a>
        </div>
    </div>
</x-app-layout>

