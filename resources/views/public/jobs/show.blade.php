<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">تفاصيل الوظيفة</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow space-y-3">
            <h1 class="text-2xl font-bold">{{ $job->title }}</h1>
            <div class="text-gray-600">{{ $job->province }}</div>
            <div class="prose dark:prose-invert max-w-none">
                <h3>الوصف</h3>
                <p>{{ $job->description }}</p>
                @if($job->requirements)
                    <h3>المتطلبات</h3>
                    <p>{{ $job->requirements }}</p>
                @endif
                @if($job->jd_file)
                    <p><a class="text-indigo-600" href="{{ Storage::url($job->jd_file) }}" target="_blank">تحميل الوصف الوظيفي</a></p>
                @endif
            </div>

            @auth
                @if(auth()->user()->role==='jobseeker')
                    <form method="POST" action="{{ route('jobseeker.apply',$job) }}">
                        @csrf
                        <x-primary-button>{{ __('messages.apply') }}</x-primary-button>
                    </form>
                @else
                    <div class="text-sm text-gray-600">سجّل دخول كباحث عن عمل للتقديم.</div>
                @endif
            @else
                <a href="{{ route('login') }}" class="px-4 py-2 rounded bg-indigo-600 text-white">سجّل للدخول للتقديم</a>
            @endauth
        </div>
    </div>
</x-app-layout>

