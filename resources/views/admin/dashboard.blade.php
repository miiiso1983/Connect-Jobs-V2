<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            لوحة تحكم الماستر أدمن
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8 grid grid-cols-1 md:grid-cols-4 gap-6">
                <a href="{{ route('admin.companies.index') }}" class="group p-6 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white shadow hover:shadow-lg transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm opacity-80">الشركات</div>
                            <div class="mt-1 text-2xl font-bold">إدارة الشركات</div>
                        </div>
                        <div class="shrink-0 w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path d="M3 7a2 2 0 012-2h3v14H5a2 2 0 01-2-2V7zM9 5h6v14H9V5zm8 0h3a2 2 0 012 2v10a2 2 0 01-2 2h-3V5z"/></svg>
                        </div>
                    </div>
                </a>

                <a href="{{ route('admin.jobs.pending') }}" class="group p-6 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow hover:shadow-lg transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm opacity-80">الوظائف</div>
                            <div class="mt-1 text-2xl font-bold">بانتظار الموافقة</div>
                        </div>
                        <div class="shrink-0 w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </a>

                <a href="{{ route('admin.jobseekers.index') }}" class="group p-6 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 text-white shadow hover:shadow-lg transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm opacity-80">الباحثون</div>
                            <div class="mt-1 text-2xl font-bold">إدارة الباحثين</div>
                        </div>
                        <div class="shrink-0 w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                        </div>
                    </div>
                </a>

                <a href="{{ route('admin.settings.index') }}" class="group p-6 rounded-xl bg-gradient-to-br from-pink-500 to-pink-600 text-white shadow hover:shadow-lg transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm opacity-80">الإعدادات</div>
                            <div class="mt-1 text-2xl font-bold">القوائم المنسدلة</div>
                        </div>
                        <div class="shrink-0 w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 14.93V19h-2v-2.07A8.001 8.001 0 014.07 13H2v-2h2.07A8.001 8.001 0 0111 4.07V2h2v2.07A8.001 8.001 0 0119.93 11H22v2h-2.07A8.001 8.001 0 0113 16.93z"/></svg>
                        </div>
                    </div>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
                    <div class="text-gray-500 text-sm">الشركات النشطة</div>
                    <div class="mt-2 text-3xl font-bold text-gray-800 dark:text-gray-100">—</div>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
                    <div class="text-gray-500 text-sm">وظائف بانتظار الموافقة</div>
                    <div class="mt-2 text-3xl font-bold text-gray-800 dark:text-gray-100">—</div>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
                    <div class="text-gray-500 text-sm">باحثون عن عمل</div>
                    <div class="mt-2 text-3xl font-bold text-gray-800 dark:text-gray-100">—</div>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
                    <div class="text-gray-500 text-sm">إشعارات جديدة</div>
                    <div class="mt-2 text-3xl font-bold text-gray-800 dark:text-gray-100">—</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

