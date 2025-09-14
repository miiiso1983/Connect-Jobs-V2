<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">ملف المتقدم</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="card bg-base-100 shadow">
            <div class="card-body space-y-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><div class="text-sm text-base-content/70">الاسم الكامل</div><div class="font-semibold">{{ $jobSeeker->full_name }}</div></div>
                    <div><div class="text-sm text-base-content/70">المسمى الوظيفي</div><div>{{ $jobSeeker->job_title }}</div></div>
                    <div><div class="text-sm text-base-content/70">المحافظة</div><div>{{ $jobSeeker->province }}</div></div>
                    <div><div class="text-sm text-base-content/70">نسبة المطابقة</div><div>{{ $application->matching_percentage }}%</div></div>
                </div>
                @if($application?->cv_file)
                    <div class="mt-4">
                        <a class="btn btn-primary btn-sm" href="{{ url('storage/'.$application->cv_file) }}" target="_blank">تنزيل السيرة الذاتية</a>
                    </div>
                @endif
                @if($application?->job)
                    <div class="mt-4 text-sm text-base-content/70">الوظيفة: <span class="font-semibold text-base-content">{{ $application->job->title }}</span></div>
                @endif
            </div>
        </div>
        <div>
            <a class="btn btn-ghost" href="{{ route('company.applicants.index') }}">رجوع</a>
        </div>
    </div>
</x-app-layout>

