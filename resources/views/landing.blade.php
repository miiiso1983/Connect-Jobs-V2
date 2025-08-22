<x-guest-layout>
    <!-- Hero -->
    <section class="gradient-bg text-white py-16 w-full">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <div class="flex items-center justify-center gap-3 mb-6">
                <img src="/images/logo.svg" alt="Connect Jobs" class="h-12 w-auto">
                <span class="font-bold tracking-wide text-2xl">Connect Jobs</span>
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold mb-4">اعثر على وظيفة أحلامك</h1>
            <p class="text-lg md:text-xl opacity-90 mb-8">منصة الوظائف الرائدة التي تربط المواهب بأفضل الفرص</p>

            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-xl p-4">
                <form class="flex flex-col md:flex-row gap-3">
                    <input type="text" placeholder="ابحث عن وظيفة..." class="input input-bordered flex-1 text-gray-800">
                    <select class="select select-bordered flex-1 text-gray-800">
                        <option>اختر المدينة</option>
                        <option>الرياض</option>
                        <option>جدة</option>
                        <option>الدمام</option>
                        <option>مكة</option>
                    </select>
                    <button class="btn btn-primary">بحث</button>
                </form>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-10">
                <div class="text-center">
                    <div class="text-3xl font-bold">1000+</div>
                    <div class="opacity-80">وظيفة متاحة</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold">500+</div>
                    <div class="opacity-80">شركة</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold">5000+</div>
                    <div class="opacity-80">باحث عن عمل</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold">95%</div>
                    <div class="opacity-80">معدل النجاح</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="py-16">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold mb-2">لماذا Connect Jobs؟</h2>
                <p class="opacity-70">نقدم أفضل تجربة للباحثين عن عمل والشركات</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="card bg-base-100 shadow card-hover">
                    <div class="card-body text-center">
                        <div class="text-4xl text-primary mb-2"><i class="fas fa-search"></i></div>
                        <h3 class="card-title justify-center">بحث ذكي</h3>
                        <p class="opacity-70">نظام بحث متطور يساعدك في العثور على الوظائف المناسبة</p>
                    </div>
                </div>
                <div class="card bg-base-100 shadow card-hover">
                    <div class="card-body text-center">
                        <div class="text-4xl text-primary mb-2"><i class="fas fa-handshake"></i></div>
                        <h3 class="card-title justify-center">مطابقة دقيقة</h3>
                        <p class="opacity-70">مطابقة بين المواهب والشركات وفق المهارات والمتطلبات</p>
                    </div>
                </div>
                <div class="card bg-base-100 shadow card-hover">
                    <div class="card-body text-center">
                        <div class="text-4xl text-primary mb-2"><i class="fas fa-rocket"></i></div>
                        <h3 class="card-title justify-center">نمو مهني</h3>
                        <p class="opacity-70">فرص مهنية ونصائح تساعدك على تطوير مسارك</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="gradient-bg text-white py-16">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">ابدأ رحلتك المهنية اليوم</h2>
            <p class="opacity-90 mb-6">انضم إلى آلاف الباحثين عن عمل الذين وجدوا وظائف أحلامهم معنا</p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="/register?type=jobseeker" class="btn">سجل كباحث عن عمل</a>
                <a href="/register?type=company" class="btn btn-outline">سجل كشركة</a>
            </div>
        </div>
    </section>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" defer></script>
        <style>
            .gradient-bg{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%)}
            .card-hover{transition:transform .3s ease,box-shadow .3s ease}
            .card-hover:hover{transform:translateY(-5px);box-shadow:0 20px 40px rgba(0,0,0,.1)}
        </style>
    @endpush
</x-guest-layout>
