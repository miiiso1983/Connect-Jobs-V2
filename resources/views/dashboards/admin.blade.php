<x-app-layout>
    <x-slot name="header">
	        <div class="rounded-xl bg-gradient-to-br from-[#5B21B6] via-[#6D28D9] to-[#4C1D95] text-white p-6">
            <h2 class="text-xl font-bold">لوحة تحكم الأدمن</h2>
	            <p class="text-[#38BDF8] text-sm mt-1">إدارة الموافقات ومراجعة الوظائف والشركات</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Quick Actions --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
	                <a href="{{ route('admin.pending.companies', [], false) ?? '#' }}" class="group relative overflow-hidden p-6 rounded-xl bg-gradient-to-br from-[#5B21B6] to-[#6D28D9] text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="absolute top-0 left-0 w-full h-full bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <div class="w-12 h-12 rounded-lg bg-white/10 flex items-center justify-center mb-4">
	                            <svg class="w-6 h-6 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div class="text-sm opacity-80">الشركات</div>
                        <div class="mt-1 text-xl font-bold">بانتظار الموافقة</div>
                        <div class="mt-2 text-xs opacity-70 flex items-center gap-1">
                            مراجعة طلبات التسجيل
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </div>
                    </div>
                </a>

                <a href="{{ route('admin.pending.jobs', [], false) ?? '#' }}" class="group relative overflow-hidden p-6 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="absolute top-0 left-0 w-full h-full bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <div class="w-12 h-12 rounded-lg bg-white/10 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="text-sm opacity-80">الوظائف</div>
                        <div class="mt-1 text-xl font-bold">بانتظار المراجعة</div>
                        <div class="mt-2 text-xs opacity-70 flex items-center gap-1">
                            مراجعة إعلانات الوظائف
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </div>
                    </div>
                </a>

                <a href="{{ route('admin.jobseekers.index') ?? '#' }}" class="group relative overflow-hidden p-6 rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-700 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="absolute top-0 left-0 w-full h-full bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <div class="w-12 h-12 rounded-lg bg-white/10 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div class="text-sm opacity-80">الباحثين عن عمل</div>
                        <div class="mt-1 text-xl font-bold">إدارة المستخدمين</div>
                        <div class="mt-2 text-xs opacity-70 flex items-center gap-1">
                            عرض وإدارة الحسابات
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </div>
                    </div>
                </a>

	                <a href="{{ route('admin.companies.index') ?? '#' }}" class="group relative overflow-hidden p-6 rounded-xl bg-gradient-to-br from-[#38BDF8] to-[#0EA5E9] text-[#4C1D95] shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="absolute top-0 left-0 w-full h-full bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
	                        <div class="w-12 h-12 rounded-lg bg-[#5B21B6]/10 flex items-center justify-center mb-4">
	                            <svg class="w-6 h-6 text-[#5B21B6]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                        </div>
                        <div class="text-sm opacity-80">الشركات</div>
                        <div class="mt-1 text-xl font-bold">جميع الشركات</div>
                        <div class="mt-2 text-xs opacity-70 flex items-center gap-1">
                            عرض وإدارة الشركات
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Secondary Actions --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
	                <a href="{{ route('admin.jobs.index') ?? '#' }}" class="group relative overflow-hidden p-6 rounded-xl bg-white dark:bg-gray-800 border-2 border-[#5B21B6]/20 text-[#5B21B6] dark:text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-[#38BDF8]">
                    <div class="relative">
	                        <div class="w-12 h-12 rounded-lg bg-[#5B21B6]/10 dark:bg-[#38BDF8]/10 flex items-center justify-center mb-4">
	                            <svg class="w-6 h-6 text-[#5B21B6] dark:text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <div class="text-sm opacity-70">الوظائف</div>
                        <div class="mt-1 text-xl font-bold">جميع الوظائف</div>
                        <div class="mt-2 text-xs opacity-60 flex items-center gap-1">
                            عرض وإدارة كل الوظائف
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </div>
                    </div>
                </a>

	                <a href="{{ route('admin.districts.index') ?? '#' }}" class="group relative overflow-hidden p-6 rounded-xl bg-white dark:bg-gray-800 border-2 border-[#5B21B6]/20 text-[#5B21B6] dark:text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-[#38BDF8]">
                    <div class="relative">
	                        <div class="w-12 h-12 rounded-lg bg-[#5B21B6]/10 dark:bg-[#38BDF8]/10 flex items-center justify-center mb-4">
	                            <svg class="w-6 h-6 text-[#5B21B6] dark:text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div class="text-sm opacity-70">المناطق</div>
                        <div class="mt-1 text-xl font-bold">إدارة المناطق</div>
                        <div class="mt-2 text-xs opacity-60 flex items-center gap-1">
                            المحافظات والمناطق
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </div>
                    </div>
                </a>

	                <a href="{{ route('admin.settings.index') ?? '#' }}" class="group relative overflow-hidden p-6 rounded-xl bg-white dark:bg-gray-800 border-2 border-[#5B21B6]/20 text-[#5B21B6] dark:text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-[#38BDF8]">
                    <div class="relative">
	                        <div class="w-12 h-12 rounded-lg bg-[#5B21B6]/10 dark:bg-[#38BDF8]/10 flex items-center justify-center mb-4">
	                            <svg class="w-6 h-6 text-[#5B21B6] dark:text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div class="text-sm opacity-70">الإعدادات</div>
                        <div class="mt-1 text-xl font-bold">إعدادات النظام</div>
                        <div class="mt-2 text-xs opacity-60 flex items-center gap-1">
                            ضبط إعدادات الموقع
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Welcome Message --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center gap-3 mb-4">
	                    <div class="w-10 h-10 rounded-lg bg-[#38BDF8] flex items-center justify-center">
	                        <svg class="w-5 h-5 text-[#4C1D95]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">مرحباً بك في لوحة التحكم</h3>
                </div>
                <p class="text-gray-600 dark:text-gray-400">يمكنك من هنا إدارة الشركات والموافقة على طلبات التسجيل، ومراجعة إعلانات الوظائف قبل نشرها، وإدارة حسابات الباحثين عن عمل.</p>
            </div>
        </div>
    </div>
</x-app-layout>

