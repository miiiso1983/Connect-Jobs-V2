<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">لوحة المتقدم</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow">
            <p class="mb-4">أكمل بروفايلك وارفع السيرة الذاتية لزيادة نسبة المطابقة عند التقديم.</p>
            <a href="{{ route('jobseeker.profile.edit') }}" class="px-4 py-2 rounded bg-indigo-600 text-white">تعديل البروفايل</a>
        </div>
    </div>
</x-app-layout>

