<x-guest-layout>


    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-[#0D2660] via-[#102E66] to-[#0A1E46] text-white py-20 w-full">
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
                <button class="px-5 py-2 rounded-xl bg-gradient-to-r from-[#D2A85A] to-[#B7792A] text-[#0D2660] font-semibold hover:from-[#E7C66A] hover:to-[#C98936] transition">
                    ابحث
                </button>
            </form>

            <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="/jobs" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[#D2A85A] to-[#B7792A] text-[#0D2660] font-semibold hover:from-[#E7C66A] hover:to-[#C98936] transition">تصفح الوظائف</a>
                <a href="/register" class="px-6 py-3 rounded-xl border border-[#D2A85A] text-[#E7C66A] hover:bg-[#E7C66A] hover:text-[#0D2660] transition">إنشاء حساب</a>
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
    <section class="py-20">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-14">
                <h2 class="text-3xl md:text-4xl font-extrabold mb-3">لماذا Connect Jobs؟</h2>
                <p class="opacity-70">تجربة سلسة للباحثين عن عمل والشركات</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="card bg-base-100 shadow-xl card-hover">
                    <div class="card-body text-center">
                        <div class="text-5xl text-primary mb-3"><i class="fas fa-search"></i></div>
                        <h3 class="card-title justify-center">بحث ذكي</h3>
                        <p class="opacity-70">محرك بحث يساعدك على الوصول للوظيفة المناسبة بسرعة</p>
                    </div>
                </div>
                <div class="card bg-base-100 shadow-xl card-hover">
                    <div class="card-body text-center">
                        <div class="text-5xl text-secondary mb-3"><i class="fas fa-handshake"></i></div>
                        <h3 class="card-title justify-center">مطابقة دقيقة</h3>
                        <p class="opacity-70">مطابقة بين متطلبات الشركات ومهارات المرشحين</p>
                    </div>
                </div>
                <div class="card bg-base-100 shadow-xl card-hover">
                    <div class="card-body text-center">
                        <div class="text-5xl text-accent mb-3"><i class="fas fa-rocket"></i></div>
                        <h3 class="card-title justify-center">نمو مهني</h3>
                        <p class="opacity-70">فرص متنوعة تساعدك على تطوير مسارك المهني</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="bg-gradient-to-r from-[#0D2660] via-[#102E66] to-[#0A1E46] text-white py-16">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4">ابدأ رحلتك اليوم</h2>
            <p class="opacity-95 mb-6">انضم إلى آلاف المستخدمين الذين وجدوا فرصهم عبر Connect Jobs</p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="/register?type=jobseeker" class="btn btn-secondary">سجل كباحث عن عمل</a>
                <a href="/register?type=company" class="btn btn-outline text-white border-white hover:bg-white hover:text-[#0D2660]">سجل كشركة</a>


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
