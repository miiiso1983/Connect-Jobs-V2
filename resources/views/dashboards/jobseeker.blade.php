<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            لوحة تحكم الباحث عن عمل
        </h2>
    </x-slot>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <a href="{{ route('jobs.index') }}" class="group p-6 rounded-xl bg-gradient-to-br from-[#0D2660] to-[#102E66] text-white shadow hover:shadow-lg">
                    <div class="text-sm opacity-90">الوظائف</div>
                    <div class="mt-1 text-2xl font-bold">استكشف الوظائف</div>
                    <div class="mt-2 text-xs opacity-90">تصفّح وتقدم</div>
                </a>
                <a href="{{ route('jobseeker.profile.edit') }}" class="group p-6 rounded-xl bg-gradient-to-br from-[#D2A85A] to-[#B7792A] text-[#0D2660] shadow hover:shadow-lg">
                    <div class="text-sm opacity-90">البروفايل</div>
                    <div class="mt-1 text-2xl font-bold">أكمل ملفك</div>
                    <div class="mt-2 text-xs opacity-90">زد فرص المطابقة</div>
                </a>
                <a href="{{ route('notifications.index') }}" class="group p-6 rounded-xl bg-gradient-to-br from-[#E7C66A] to-[#D2A85A] text-[#0D2660] shadow hover:shadow-lg">
                    <div class="text-sm opacity-90">إشعارات</div>
                    <div class="mt-1 text-2xl font-bold">آخر التحديثات</div>
                    <div class="mt-2 text-xs opacity-90">ابقَ مطلعًا</div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

