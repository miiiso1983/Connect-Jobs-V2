<x-guest-layout>


    <!-- Hero -->
		    <section class="relative overflow-hidden bg-gradient-to-br from-[#4A00B8] via-[#5A00E1] to-[#3C0094] text-white py-20 w-full">
        <div class="absolute -top-20 -left-20 h-72 w-72 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-16 -right-24 h-80 w-80 bg-black/10 dark:bg-white/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="relative max-w-6xl mx-auto px-4 text-center">
            <div class="flex items-center justify-center gap-3 mb-6">
                <x-application-logo class="h-10 sm:h-12" />
                <span class="font-bold tracking-wide text-2xl">Connect Jobs</span>
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold mb-4 leading-tight">
                منصة وظائف تربطك بأفضل الفرص
            </h1>
            <p class="text-lg md:text-xl/relaxed opacity-95 mb-8">
                انطلق في مسيرتك المهنية أو اعثر على الكفاءات المناسبة لشركتك
            </p>

            <!-- Quick Search -->
            <form method="GET" action="/jobs" class="max-w-2xl mx-auto flex items-stretch gap-2 bg-white/10 backdrop-blur rounded-2xl p-2">
                <label for="q" class="sr-only">ابحث عن وظيفة</label>
                <input id="q" name="q" type="text" placeholder="ابحث عن مسمى وظيفي..." class="flex-1 rounded-xl bg-white/90 text-gray-800 placeholder-gray-500 px-4 py-2 focus:outline-none">
		                <button class="px-5 py-2 rounded-xl bg-gradient-to-r from-[#38BDF8] to-[#0EA5E9] text-[#3C0094] font-semibold hover:from-[#7DD3FC] hover:to-[#38BDF8] transition">
                    ابحث
                </button>
            </form>

		            <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
		                <a href="/jobs" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[#38BDF8] to-[#0EA5E9] text-[#3C0094] font-semibold hover:from-[#7DD3FC] hover:to-[#38BDF8] transition">تصفح الوظائف</a>
		                <a href="/register" class="px-6 py-3 rounded-xl border border-[#38BDF8] text-[#38BDF8] hover:bg-[#38BDF8] hover:text-[#3C0094] transition">إنشاء حساب</a>
	            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mt-14">
                <div class="text-center">
                    <div class="text-4xl font-extrabold">{{ ($jobsCount ?? 0) > 0 ? number_format($jobsCount) : '1000+' }}</div>
                    <div class="opacity-90">وظيفة متاحة</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-extrabold">{{ ($companiesCount ?? 0) > 0 ? number_format($companiesCount) : '500+' }}</div>
                    <div class="opacity-90">شركة</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-extrabold">{{ ($seekersCount ?? 0) > 0 ? number_format($seekersCount) : '5000+' }}</div>
                    <div class="opacity-90">باحث عن عمل</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-extrabold">95%</div>
                    <div class="opacity-90">معدل النجاح</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="py-20 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-6xl mx-auto px-4">
		            <div class="text-center mb-14">
		                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#4A00B8]/10 dark:bg-[#4A00B8]/30 text-[#4A00B8] dark:text-[#38BDF8] text-sm font-medium mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    مميزاتنا
                </div>
                <h2 class="text-3xl md:text-4xl font-extrabold mb-3 text-gray-900 dark:text-white">لماذا Connect Jobs؟</h2>
                <p class="text-gray-600 dark:text-gray-400">تجربة سلسة للباحثين عن عمل والشركات</p>
            </div>
		            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
		                <div class="group bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8 text-center transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl border border-gray-100 dark:border-gray-700">
		                    <div class="w-16 h-16 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-[#4A00B8] to-[#5A00E1] flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
	                        <svg class="w-8 h-8 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">بحث ذكي</h3>
                    <p class="text-gray-600 dark:text-gray-400">محرك بحث يساعدك على الوصول للوظيفة المناسبة بسرعة</p>
                </div>
	                <div class="group bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8 text-center transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl border border-gray-100 dark:border-gray-700">
		                    <div class="w-16 h-16 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-[#38BDF8] to-[#0EA5E9] flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
		                        <svg class="w-8 h-8 text-[#3C0094]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">مطابقة دقيقة</h3>
                    <p class="text-gray-600 dark:text-gray-400">مطابقة بين متطلبات الشركات ومهارات المرشحين</p>
                </div>
                <div class="group bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8 text-center transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl border border-gray-100 dark:border-gray-700">
                    <div class="w-16 h-16 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">نمو مهني</h3>
                    <p class="text-gray-600 dark:text-gray-400">فرص متنوعة تساعدك على تطوير مسارك المهني</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
		    <section class="relative overflow-hidden bg-gradient-to-br from-[#4A00B8] via-[#5A00E1] to-[#3C0094] text-white py-20">
	        <div class="absolute -top-20 -right-20 h-72 w-72 bg-[#38BDF8]/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-16 -left-24 h-80 w-80 bg-white/5 rounded-full blur-3xl"></div>
        <div class="relative max-w-6xl mx-auto px-4 text-center">
	            <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-[#38BDF8]/20 flex items-center justify-center">
	                <svg class="w-10 h-10 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4">ابدأ رحلتك اليوم</h2>
            <p class="text-lg text-white/90 mb-8 max-w-2xl mx-auto">انضم إلى آلاف المستخدمين الذين وجدوا فرصهم عبر Connect Jobs واكتشف الفرص المناسبة لك</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
		                <a href="/register?type=jobseeker" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-[#38BDF8] text-[#3C0094] font-bold text-lg hover:bg-[#0EA5E9] transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    سجل كباحث عن عمل
                </a>
		                <a href="/register?type=company" class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl border-2 border-[#38BDF8] text-[#38BDF8] font-bold text-lg hover:bg-[#38BDF8] hover:text-[#3C0094] transition-all duration-300 hover:-translate-y-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    سجل كشركة
                </a>
            </div>
        </div>
    </section>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" defer></script>
    @php($orgJson = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'Connect Jobs',
        'url' => url('/'),
        'logo' => asset('favicon.ico'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
    <script type="application/ld+json">{!! $orgJson !!}</script>
    <style>
        .card-hover{transition:transform .3s ease,box-shadow .3s ease}
        .card-hover:hover{transform:translateY(-6px);box-shadow:0 24px 48px rgba(0,0,0,.12)}
    </style>
</x-guest-layout>
