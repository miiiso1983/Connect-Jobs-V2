<x-app-layout>
    <x-slot name="header">
        <div class="rounded-xl bg-gradient-to-br from-[#0D2660] via-[#102E66] to-[#0A1E46] text-white p-6">
            <h1 class="text-2xl font-bold">{{ $job->title }}</h1>
            <div class="mt-1 text-[#E7C66A] text-sm">{{ $job->province }}</div>
        </div>
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
                    <p><a class="text-primary" href="{{ Storage::url($job->jd_file) }}" target="_blank">تحميل الوصف الوظيفي</a></p>
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
                <a href="{{ route('login') }}" class="btn btn-primary">سجّل للدخول للتقديم</a>
            @endauth
        </div>
    </div>
    @auth
        @if(auth()->user()->role==='jobseeker')
            <div class="fixed bottom-4 inset-x-0 flex justify-center z-40">
                <div class="bg-base-100/90 backdrop-blur border rounded-full shadow-lg px-3 py-2 flex items-center gap-3">
                    <span class="hidden sm:block text-sm">{{ $job->title }}</span>
                    <form method="POST" action="{{ route('jobseeker.apply',$job) }}">
                        @csrf
                        <button class="btn btn-primary btn-sm rounded-full">تقديم الآن</button>
                    </form>
                    @php $saved = $isSaved ?? false; @endphp
                    <form method="POST" action="{{ $saved ? route('jobs.unsave',$job) : route('jobs.save',$job) }}">
                        @csrf
                        @if($saved)
                            @method('DELETE')
                        @endif
                        <button class="btn btn-ghost btn-sm rounded-full text-primary">
                            {{ $saved ? 'إلغاء الحفظ' : 'حفظ لاحقاً' }}
                        </button>
                    </form>
                </div>
            </div>
        @endif
    @else
        <div class="fixed bottom-4 inset-x-0 flex justify-center z-40">
            <a href="{{ route('login') }}" class="btn btn-primary btn-sm rounded-full">سجّل للدخول للتقديم</a>
        </div>
    @endauth

</x-app-layout>

