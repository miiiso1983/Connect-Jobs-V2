<x-guest-layout>
    {{-- Header --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-[#0D2660] via-[#102E66] to-[#0A1E46] text-white py-12">
        <div class="absolute -top-20 -left-20 h-72 w-72 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-16 -right-24 h-80 w-80 bg-black/10 rounded-full blur-3xl"></div>
        <div class="relative max-w-6xl mx-auto px-4 text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-[#E7C66A]/20 flex items-center justify-center">
                <svg class="w-8 h-8 text-[#E7C66A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold">إدارة التنبيهات</h1>
        </div>
    </section>

    <div class="py-16">
        <div class="max-w-xl mx-auto px-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 text-center">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                    <svg class="w-10 h-10 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-3">تم إلغاء تنبيه الوظائف</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-8">لن تتلقى رسائل هذا التنبيه بعد الآن. يمكنك إدارة جميع تنبيهاتك من لوحة الباحث عن عمل.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="/" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[#0D2660] to-[#102E66] text-white font-bold hover:from-[#0A1E46] hover:to-[#0D2660] transition-all duration-300 shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        العودة للرئيسية
                    </a>
                    <a href="{{ route('jobseeker.alerts.index') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-[#E7C66A] text-[#0D2660] font-bold hover:bg-[#D2A85A] transition-all duration-300 shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        إدارة التنبيهات
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>

