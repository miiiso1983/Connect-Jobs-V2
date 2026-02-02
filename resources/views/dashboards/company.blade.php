<x-app-layout>
    <x-slot name="header">
        <div class="rounded-xl bg-gradient-to-br from-[#0D2660] via-[#102E66] to-[#0A1E46] text-white p-6">
            <h2 class="text-xl font-bold">لوحة تحكم الشركة</h2>
            <p class="text-[#E7C66A] text-sm mt-1">إدارة الوظائف والمتقدمين واستعراض الإحصائيات</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Alerts --}}
            @if(($subscription['status'] ?? 'active') === 'expired')
            <div class="flex items-center gap-3 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-800 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-red-800 dark:text-red-400">انتهى الاشتراك</h4>
                    <p class="text-sm text-red-700 dark:text-red-300">بعض الميزات مثل إنشاء الوظائف ورؤية المتقدمين متوقفة حتى التجديد.</p>
                </div>
            </div>
            @elseif(($subscription['status'] ?? 'active') === 'expiring')
            <div class="flex items-center gap-3 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-800 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-amber-800 dark:text-amber-400">تنبيه الاشتراك</h4>
                    <p class="text-sm text-amber-700 dark:text-amber-300">سينتهي الاشتراك خلال {{ $subscription['days_left'] }} يوم. يُنصح بالتجديد مبكرًا.</p>
                </div>
            </div>
            @endif

            @if (session('status'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-green-800 dark:text-green-400">{{ session('status') }}</span>
            </div>
            @endif

            {{-- Quick Actions --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <a href="{{ route('company.jobs.index') }}" class="group relative overflow-hidden p-6 rounded-xl bg-gradient-to-br from-[#0D2660] to-[#1a3a7a] text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="absolute top-0 left-0 w-full h-full bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <div class="w-12 h-12 rounded-lg bg-white/10 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-[#E7C66A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <div class="text-sm opacity-80">الوظائف</div>
                        <div class="mt-1 text-xl font-bold">إدارة الوظائف</div>
                        <div class="mt-2 text-xs opacity-70">أضف/عدّل/انشر وظائفك</div>
                    </div>
                </a>

                <a href="{{ route('company.seekers.browse') }}" class="group relative overflow-hidden p-6 rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-700 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="absolute top-0 left-0 w-full h-full bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <div class="w-12 h-12 rounded-lg bg-white/10 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div class="text-sm opacity-80">قاعدة البيانات</div>
                        <div class="mt-1 text-xl font-bold">الباحثين عن عمل</div>
                        <div class="mt-2 text-xs opacity-70">استعرض وفلتر جميع الباحثين</div>
                    </div>
                </a>

                <a href="{{ route('company.jobs.create') }}" class="group relative overflow-hidden p-6 rounded-xl bg-gradient-to-br from-[#E7C66A] to-[#D2A85A] text-[#0D2660] shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="absolute top-0 left-0 w-full h-full bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <div class="w-12 h-12 rounded-lg bg-[#0D2660]/10 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-[#0D2660]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </div>
                        <div class="text-sm opacity-80">وظيفة جديدة</div>
                        <div class="mt-1 text-xl font-bold">إنشاء وظيفة</div>
                        <div class="mt-2 text-xs opacity-70">ابدأ بإعلان جديد الآن</div>
                    </div>
                </a>

                <a href="{{ route('company.applicants.index') }}" class="group relative overflow-hidden p-6 rounded-xl bg-white dark:bg-gray-800 border-2 border-[#0D2660]/20 text-[#0D2660] dark:text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-[#E7C66A]">
                    <div class="relative">
                        <div class="w-12 h-12 rounded-lg bg-[#0D2660]/10 dark:bg-[#E7C66A]/10 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-[#0D2660] dark:text-[#E7C66A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        </div>
                        <div class="text-sm opacity-70">المتقدمون</div>
                        <div class="mt-1 text-xl font-bold">فلترة المتقدمين</div>
                        <div class="mt-2 text-xs opacity-60">اكتشف أفضل المطابقات</div>
                    </div>
                </a>
            </div>

            {{-- KPIs --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="w-10 h-10 rounded-lg bg-[#0D2660] flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#E7C66A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">الإحصائيات</h3>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-4 rounded-lg bg-gradient-to-br from-[#0D2660]/5 to-[#0D2660]/10 dark:from-[#0D2660]/20 dark:to-[#0D2660]/30">
                        <div class="text-3xl font-bold text-[#0D2660] dark:text-[#E7C66A]">{{ $kpis['published'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">وظائف منشورة</div>
                    </div>
                    <div class="text-center p-4 rounded-lg bg-gradient-to-br from-amber-500/5 to-amber-500/10 dark:from-amber-500/20 dark:to-amber-500/30">
                        <div class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $kpis['pending'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">بانتظار الموافقة</div>
                    </div>
                    <div class="text-center p-4 rounded-lg bg-gradient-to-br from-emerald-500/5 to-emerald-500/10 dark:from-emerald-500/20 dark:to-emerald-500/30">
                        <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $kpis['apps_week'] ?? 0 }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">متقدمون هذا الأسبوع</div>
                    </div>
                    <div class="text-center p-4 rounded-lg bg-gradient-to-br from-[#E7C66A]/10 to-[#E7C66A]/20 dark:from-[#E7C66A]/20 dark:to-[#E7C66A]/30">
                        <div class="text-3xl font-bold text-[#0D2660] dark:text-[#E7C66A]">{{ $kpis['avg_match'] ?? 0 }}%</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">متوسط المطابقة</div>
                    </div>
                </div>
            </div>

            {{-- Charts --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6"
                 x-data="{ prov: [], spec: [] }"
                 x-init="prov = JSON.parse($el.dataset.prov); spec = JSON.parse($el.dataset.spec)"
                 data-prov='@json($charts["by_province"] ?? [])'
                 data-spec='@json($charts["by_speciality"] ?? [])'>
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="w-10 h-10 rounded-lg bg-[#E7C66A] flex items-center justify-center">
                        <svg class="w-5 h-5 text-[#0D2660]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">توزيع المتقدمين</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">أعلى المحافظات</div>
                        <div class="space-y-3">
                            <template x-for="row in prov" :key="row.province">
                                <div class="flex items-center gap-3">
                                    <div class="w-24 text-sm text-gray-600 dark:text-gray-400 truncate" x-text="row.province || 'غير محدد'"></div>
                                    <div class="flex-1 h-3 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                        <div class="h-3 rounded-full bg-gradient-to-r from-[#0D2660] to-[#1a3a7a] transition-all duration-500" :style="`width: ${Math.min(100, (row.c / (prov[0]?.c||1)) * 100)}%`"></div>
                                    </div>
                                    <div class="w-10 text-sm font-medium text-gray-700 dark:text-gray-300" x-text="row.c"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">أعلى التخصصات</div>
                        <div class="space-y-3">
                            <template x-for="row in spec" :key="row.speciality">
                                <div class="flex items-center gap-3">
                                    <div class="w-24 text-sm text-gray-600 dark:text-gray-400 truncate" x-text="row.speciality || 'غير محدد'"></div>
                                    <div class="flex-1 h-3 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                        <div class="h-3 rounded-full bg-gradient-to-r from-[#E7C66A] to-[#D2A85A] transition-all duration-500" :style="`width: ${Math.min(100, (row.c / (spec[0]?.c||1)) * 100)}%`"></div>
                                    </div>
                                    <div class="w-10 text-sm font-medium text-gray-700 dark:text-gray-300" x-text="row.c"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

