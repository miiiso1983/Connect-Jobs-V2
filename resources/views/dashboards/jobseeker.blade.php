<x-app-layout>
    <x-slot name="header">
	        <div class="rounded-xl bg-gradient-to-br from-[#5B21B6] via-[#6D28D9] to-[#4C1D95] text-white p-6">
            <h2 class="text-xl font-bold">مرحباً، {{ auth()->user()->name ?? 'الباحث عن عمل' }}</h2>
	            <p class="text-[#38BDF8] text-sm mt-1">ابدأ رحلتك المهنية واكتشف الفرص المناسبة لك</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Quick Actions --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
	                <a href="{{ route('jobs.index') }}" class="group relative overflow-hidden p-6 rounded-xl bg-gradient-to-br from-[#5B21B6] to-[#6D28D9] text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="absolute top-0 left-0 w-full h-full bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <div class="w-12 h-12 rounded-lg bg-white/10 flex items-center justify-center mb-4">
	                            <svg class="w-6 h-6 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="text-sm opacity-80">الوظائف المتاحة</div>
                        <div class="mt-1 text-2xl font-bold">استكشف الوظائف</div>
                        <div class="mt-2 text-xs opacity-70 flex items-center gap-1">
                            تصفّح وتقدم الآن
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </div>
                    </div>
                </a>

	                <a href="{{ route('jobseeker.profile.edit') }}" class="group relative overflow-hidden p-6 rounded-xl bg-gradient-to-br from-[#38BDF8] to-[#0EA5E9] text-[#4C1D95] shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="absolute top-0 left-0 w-full h-full bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
	                        <div class="w-12 h-12 rounded-lg bg-[#5B21B6]/10 flex items-center justify-center mb-4">
	                            <svg class="w-6 h-6 text-[#5B21B6]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div class="text-sm opacity-80">الملف الشخصي</div>
                        <div class="mt-1 text-2xl font-bold">أكمل ملفك</div>
                        <div class="mt-2 text-xs opacity-70 flex items-center gap-1">
                            زد فرص المطابقة
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </div>
                    </div>
                </a>

	                <a href="{{ route('notifications.index') }}" class="group relative overflow-hidden p-6 rounded-xl bg-white dark:bg-gray-800 border-2 border-[#5B21B6]/20 text-[#5B21B6] dark:text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-[#38BDF8]">
                    <div class="relative">
	                        <div class="w-12 h-12 rounded-lg bg-[#5B21B6]/10 dark:bg-[#38BDF8]/10 flex items-center justify-center mb-4">
	                            <svg class="w-6 h-6 text-[#5B21B6] dark:text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </div>
                        <div class="text-sm opacity-70">الإشعارات</div>
                        <div class="mt-1 text-2xl font-bold">آخر التحديثات</div>
                        <div class="mt-2 text-xs opacity-60 flex items-center gap-1">
                            ابقَ مطلعاً على كل جديد
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Tips Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center gap-3 mb-4">
	                    <div class="w-10 h-10 rounded-lg bg-[#38BDF8] flex items-center justify-center">
	                        <svg class="w-5 h-5 text-[#4C1D95]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">نصائح لزيادة فرصك</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex items-start gap-3 p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
	                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-[#5B21B6] text-white flex items-center justify-center text-sm font-bold">1</span>
                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-white">أكمل ملفك الشخصي</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">الملفات المكتملة تحصل على فرص أكثر بـ 40%</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
	                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-[#38BDF8] text-[#4C1D95] flex items-center justify-center text-sm font-bold">2</span>
                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-white">ارفع سيرتك الذاتية</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">السيرة الذاتية تسهل على الشركات التواصل معك</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
	                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-[#5B21B6] text-white flex items-center justify-center text-sm font-bold">3</span>
                        <div>
                            <h4 class="font-medium text-gray-800 dark:text-white">فعّل تنبيهات الوظائف</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">احصل على إشعار فوري عند نشر وظائف مناسبة</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

