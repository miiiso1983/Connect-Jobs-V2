<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GuestLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
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
                    <a href="<?php echo e(route('login')); ?>" class="btn btn-primary">تسجيل الدخول</a>
                    <a href="<?php echo e(route('register')); ?>" class="btn btn-outline">إنشاء حساب</a>
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
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>

<?php /**PATH /Users/mustafaaljaf/Documents/augment-projects/Connect Job V2/app/resources/views/landing.blade.php ENDPATH**/ ?>