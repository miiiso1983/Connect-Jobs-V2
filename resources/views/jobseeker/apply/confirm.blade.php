<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">تأكيد التقديم</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            @if(!empty($alreadyApplied))
                <div class="mb-4 p-3 rounded-lg bg-amber-100 text-amber-900 border border-amber-200 text-sm">
                    لقد تقدمت لهذه الوظيفة سابقاً. يمكنك إعادة التقديم لتحديث ملفك.
                </div>
            @endif
            <h3 class="text-lg font-semibold mb-2">{{ $job->title }}</h3>
            <div class="text-sm text-slate-600 dark:text-slate-300 space-y-1">
                <div><strong>الوصف:</strong> {{ $job->description }}</div>
                <div><strong>المتطلبات:</strong> {{ $job->requirements ?? '—' }}</div>
                <div><strong>المحافظة:</strong> {{ $job->province }}</div>
                <div><strong>المناطق:</strong> {{ is_array($job->districts) ? implode('، ', $job->districts) : ($job->districts ?? '—') }}</div>
            </div>

            <form method="POST" action="{{ route('jobseeker.apply', $job) }}" class="mt-6 flex gap-3">
                @csrf
                <button class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white">تأكيد التقديم</button>
                <a href="{{ route('jobs.show', $job) }}" class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">رجوع</a>
            </form>
        </div>
    </div>
</x-app-layout>

