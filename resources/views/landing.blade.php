<x-guest-layout>
    <!-- Hero -->
    <section class="bg-gradient-to-br from-sky-500 via-indigo-500 to-fuchsia-500 text-white py-20 w-full">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <div class="flex items-center justify-center gap-3 mb-6">
                <img src="/images/logo.svg" alt="Connect Jobs" class="h-12 w-auto">
                <span class="font-bold tracking-wide text-2xl">Connect Jobs</span>
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold mb-4 leading-tight">منصة وظائف تربطك بأفضل الفرص</h1>
            <p class="text-lg md:text-xl/relaxed opacity-95 mb-10">انطلق في مسيرتك المهنية أو اعثر على الكفاءات المناسبة لشركتك</p>

            <div class="mt-1 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="/jobs" class="btn btn-primary btn-lg">تصفح الوظائف</a>
                <a href="/register" class="btn btn-outline btn-lg text-white border-white hover:bg-white hover:text-indigo-600">إنشاء حساب</a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mt-14">
                <div class="text-center">
                    <div class="text-4xl font-extrabold">1000+</div>
                    <div class="opacity-90">وظيفة متاحة</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-extrabold">500+</div>
                    <div class="opacity-90">شركة</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-extrabold">5000+</div>
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
    <section class="bg-gradient-to-r from-indigo-600 via-sky-500 to-emerald-500 text-white py-16">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4">ابدأ رحلتك اليوم</h2>
            <p class="opacity-95 mb-6">انضم إلى آلاف المستخدمين الذين وجدوا فرصهم عبر Connect Jobs</p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="/register?type=jobseeker" class="btn btn-white">سجل كباحث عن عمل</a>
                <a href="/register?type=company" class="btn btn-outline text-white border-white hover:bg-white hover:text-indigo-600">سجل كشركة</a>
            </div>
        </div>
    </section>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" defer></script>
        <style>
            .card-hover{transition:transform .3s ease,box-shadow .3s ease}
            .card-hover:hover{transform:translateY(-6px);box-shadow:0 24px 48px rgba(0,0,0,.12)}
        </style>
    @endpush
</x-guest-layout>
