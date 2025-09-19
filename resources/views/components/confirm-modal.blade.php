@props(['title' => 'تأكيد', 'message' => 'هل أنت متأكد؟'])

<div x-show="open" x-transition x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" @click="open=false"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold mb-2">{{ $title }}</h3>
        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $message }}</p>
        <div class="mt-4 flex items-center justify-end gap-2">
            <button type="button" @click="open=false" class="px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100">إلغاء</button>
            <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white">تأكيد</button>
        </div>
    </div>
</div>

