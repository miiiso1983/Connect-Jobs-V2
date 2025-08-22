<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">الإشعارات</h2>
            <form method="POST" action="{{ route('notifications.read_all') }}">
                @csrf
                <button class="px-3 py-1 rounded bg-gray-700 text-white text-sm">تحديد الكل كمقروء</button>
            </form>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow divide-y divide-gray-100 dark:divide-gray-700">
            @forelse ($notifications as $n)
                <div class="p-4 flex items-start justify-between {{ is_null($n->read_at) ? 'bg-yellow-50 dark:bg-yellow-900/30' : '' }}">
                    <div>
                        <div class="font-semibold">{{ data_get($n->data,'title') }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-300">{{ data_get($n->data,'message') }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</div>
                    </div>
                    <div>
                        @if (is_null($n->read_at))
                        <form method="POST" action="{{ route('notifications.read', $n->id) }}">
                            @csrf
                            <button class="px-2.5 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs">تمييز كمقروء</button>
                        </form>
                        @else
                            <span class="text-xs text-gray-400">مقروء</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-500">لا توجد إشعارات.</div>
            @endforelse
        </div>
        <div class="flex items-center justify-between">
            <div class="text-xs text-gray-500">{{ $notifications->total() }} إشعار</div>
            <div>
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

