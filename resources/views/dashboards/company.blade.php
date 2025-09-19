<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            لوحة تحكم الشركة
        </h2>
    </x-slot>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(($subscription['status'] ?? 'active') === 'expired')
            <div class="alert alert-error shadow">
                <div>
                    <span>انتهى اشتراك شركتك. بعض الميزات مثل إنشاء الوظائف ورؤية المتقدمين متوقفة حتى التجديد.</span>
                </div>
            </div>
            @elseif(($subscription['status'] ?? 'active') === 'expiring')
            <div class="alert alert-warning shadow">
                <div>
                    <span>سينتهي الاشتراك خلال {{ $subscription['days_left'] }} يوم. يُنصح بالتجديد مبكرًا.</span>
                </div>
            </div>
            @endif

            @if (session('status'))
            <div class="alert alert-warning shadow">
                <div>
                    <span>{{ session('status') }}</span>
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <a href="{{ route('company.jobs.index') }}" class="group p-6 rounded-xl bg-gradient-to-br from-[#0D2660] to-[#102E66] text-white shadow hover:shadow-lg">
                    <div class="text-sm opacity-90">الوظائف</div>
                    <div class="mt-1 text-2xl font-bold">إدارة الوظائف</div>
                    <div class="mt-2 text-xs opacity-90">أضف/عدّل/انشر وظائفك</div>
                </a>
                <a href="{{ route('company.jobs.create') }}" class="group p-6 rounded-xl bg-gradient-to-br from-[#D2A85A] to-[#B7792A] text-[#0D2660] shadow hover:shadow-lg">
                    <div class="text-sm opacity-90">وظيفة جديدة</div>
                    <div class="mt-1 text-2xl font-bold">إنشاء وظيفة</div>
                    <div class="mt-2 text-xs opacity-90">ابدأ بإعلان جديد الآن</div>
                </a>
                <a href="{{ route('company.applicants.index') }}" class="group p-6 rounded-xl bg-gradient-to-br from-[#E7C66A] to-[#D2A85A] text-[#0D2660] shadow hover:shadow-lg">
                    <div class="text-sm opacity-90">المتقدمون</div>
                    <div class="mt-1 text-2xl font-bold">فلترة المتقدمين</div>
                    <div class="mt-2 text-xs opacity-90">اكتشف أفضل المطابقات</div>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
                    <div class="text-sm text-gray-500">وظائف منشورة</div>
                    <div class="mt-2 text-3xl font-bold text-gray-800 dark:text-gray-100">{{ $kpis['published'] ?? 0 }}</div>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
                    <div class="text-sm text-gray-500">وظائف بانتظار موافقة</div>
                    <div class="mt-2 text-3xl font-bold text-gray-800 dark:text-gray-100">{{ $kpis['pending'] ?? 0 }}</div>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
                    <div class="text-sm text-gray-500">متقدمون هذا الأسبوع</div>
                    <div class="mt-2 text-3xl font-bold text-gray-800 dark:text-gray-100">{{ $kpis['apps_week'] ?? 0 }}</div>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
                    <div class="text-sm text-gray-500">متوسط المطابقة</div>
                    <div class="mt-2 text-3xl font-bold text-gray-800 dark:text-gray-100">{{ $kpis['avg_match'] ?? 0 }}%</div>
                </div>
            </div>
        </div>
    </div>
            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow"
                 x-data="{ prov: [], spec: [] }"
                 x-init="prov = JSON.parse($el.dataset.prov); spec = JSON.parse($el.dataset.spec)"
                 data-prov='@json($charts["by_province"])'
                 data-spec='@json($charts["by_speciality"])'>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="text-sm text-gray-500 mb-2">أعلى المحافظات</div>
                        <div class="space-y-2">
                            <template x-for="row in prov" :key="row.province">
                                <div class="flex items-center gap-3">
                                    <div class="w-28 text-xs text-gray-600" x-text="row.province || 'غير محدد'"></div>
                                    <div class="flex-1 h-2 rounded-full bg-gray-200 dark:bg-gray-700">
                                        <div class="h-2 rounded-full bg-gradient-to-r from-[#0D2660] to-[#102E66]" :style="`width: ${Math.min(100, (row.c / (prov[0]?.c||1)) * 100)}%`"></div>
                                    </div>
                                    <div class="w-10 text-xs text-gray-500" x-text="row.c"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 mb-2">أعلى التخصصات</div>
                        <div class="space-y-2">
                            <template x-for="row in spec" :key="row.speciality">
                                <div class="flex items-center gap-3">
                                    <div class="w-28 text-xs text-gray-600" x-text="row.speciality || 'غير محدد'"></div>
                                    <div class="flex-1 h-2 rounded-full bg-gray-200 dark:bg-gray-700">
                                        <div class="h-2 rounded-full bg-gradient-to-r from-[#D2A85A] to-[#B7792A]" :style="`width: ${Math.min(100, (row.c / (spec[0]?.c||1)) * 100)}%`"></div>
                                    </div>
                                    <div class="w-10 text-xs text-gray-500" x-text="row.c"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
</x-app-layout>

