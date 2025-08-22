<x-guest-layout>
    <div class="min-h-screen hero bg-base-200">
        <div class="hero-content text-center">
            <div class="max-w-5xl">
                <div class="flex items-center justify-center gap-3 mb-8">
                    <img src="/images/logo.svg" alt="Connect Jobs" class="h-12 w-auto">
                    <span class="font-bold tracking-wide text-2xl">Connect Jobs</span>
                </div>

                <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight">منصة وظائف عصرية تربط الشركات بالمواهب</h1>
                <p class="mt-4 text-lg opacity-70">انطلق في مسيرتك المهنية أو اعثر على الكفاءات المناسبة لشركتك</p>

                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('login') }}" class="btn btn-primary">تسجيل الدخول</a>
                    <a href="{{ route('register') }}" class="btn btn-outline">إنشاء حساب</a>
                </div>

                <div class="stats shadow w-full mt-10">
                  <div class="stat">
                    <div class="stat-title">شركات مسجلة</div>
                    <div class="stat-value">+120</div>
                    <div class="stat-desc">آخر 30 يوم</div>
                  </div>
                  <div class="stat">
                    <div class="stat-title">فرص عمل</div>
                    <div class="stat-value">+850</div>
                    <div class="stat-desc">قيد التقديم</div>
                  </div>
                  <div class="stat">
                    <div class="stat-title">باحثين نشطين</div>
                    <div class="stat-value">+5K</div>
                    <div class="stat-desc">متصلون الآن</div>
                  </div>
                </div>

                <div class="mt-14 grid grid-cols-1 sm:grid-cols-3 gap-6 text-sm">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <div class="card-title">لوحات متخصصة</div>
                            <p>أدمن، شركات، وباحثين عن عمل</p>
                        </div>
                    </div>
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <div class="card-title">إدارة الوظائف</div>
                            <p>نشر، مراجعة، وتتبع الطلبات</p>
                        </div>
                    </div>
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <div class="card-title">موافقة الشركات</div>
                            <p>تفعيل الحساب بعد موافقة الأدمن</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>

