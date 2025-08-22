<x-guest-layout>
    <div class="py-12 max-w-6xl mx-auto px-4">
        <h1 class="text-2xl font-semibold mb-4">الوظائف المتاحة</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($jobs as $job)
                <a href="{{ route('jobs.show',$job) }}" class="p-4 rounded border hover:bg-gray-50 dark:hover:bg-gray-800 block">
                    <div class="font-semibold">{{ $job->title }}</div>
                    <div class="text-sm text-gray-600">{{ $job->province }}</div>
                </a>
            @endforeach
        </div>
        <div class="mt-6">{{ $jobs->links() }}</div>
    </div>
</x-guest-layout>

