<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            لوحة تحكم الشركة
        </h2>
     <?php $__env->endSlot(); ?>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <a href="<?php echo e(route('company.jobs.index')); ?>" class="group p-6 rounded-xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white shadow hover:shadow-lg">
                    <div class="text-sm opacity-90">الوظائف</div>
                    <div class="mt-1 text-2xl font-bold">إدارة الوظائف</div>
                    <div class="mt-2 text-xs opacity-90">أضف/عدّل/انشر وظائفك</div>
                </a>
                <a href="<?php echo e(route('company.jobs.create')); ?>" class="group p-6 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow hover:shadow-lg">
                    <div class="text-sm opacity-90">وظيفة جديدة</div>
                    <div class="mt-1 text-2xl font-bold">إنشاء وظيفة</div>
                    <div class="mt-2 text-xs opacity-90">ابدأ بإعلان جديد الآن</div>
                </a>
                <a href="<?php echo e(route('company.applicants.index')); ?>" class="group p-6 rounded-xl bg-gradient-to-br from-pink-500 to-rose-600 text-white shadow hover:shadow-lg">
                    <div class="text-sm opacity-90">المتقدمون</div>
                    <div class="mt-1 text-2xl font-bold">فلترة المتقدمين</div>
                    <div class="mt-2 text-xs opacity-90">اكتشف أفضل المطابقات</div>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
                    <div class="text-sm text-gray-500">وظائف منشورة</div>
                    <div class="mt-2 text-3xl font-bold text-gray-800 dark:text-gray-100"><?php echo e($kpis['published'] ?? 0); ?></div>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
                    <div class="text-sm text-gray-500">وظائف بانتظار موافقة</div>
                    <div class="mt-2 text-3xl font-bold text-gray-800 dark:text-gray-100"><?php echo e($kpis['pending'] ?? 0); ?></div>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
                    <div class="text-sm text-gray-500">متقدمون هذا الأسبوع</div>
                    <div class="mt-2 text-3xl font-bold text-gray-800 dark:text-gray-100"><?php echo e($kpis['apps_week'] ?? 0); ?></div>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
                    <div class="text-sm text-gray-500">متوسط المطابقة</div>
                    <div class="mt-2 text-3xl font-bold text-gray-800 dark:text-gray-100"><?php echo e($kpis['avg_match'] ?? 0); ?>%</div>
                </div>
            </div>
        </div>
    </div>
            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow" x-data='{ prov: <?php echo \Illuminate\Support\Js::from($charts["by_province"])->toHtml() ?>, spec: <?php echo \Illuminate\Support\Js::from($charts["by_speciality"])->toHtml() ?> }'>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="text-sm text-gray-500 mb-2">أعلى المحافظات</div>
                        <div class="space-y-2">
                            <template x-for="row in prov" :key="row.province">
                                <div class="flex items-center gap-3">
                                    <div class="w-28 text-xs text-gray-600" x-text="row.province || 'غير محدد'"></div>
                                    <div class="flex-1 h-2 rounded-full bg-gray-200 dark:bg-gray-700">
                                        <div class="h-2 rounded-full bg-gradient-to-r from-sky-500 to-indigo-600" :style="`width: ${Math.min(100, (row.c / (prov[0]?.c||1)) * 100)}%`"></div>
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
                                        <div class="h-2 rounded-full bg-gradient-to-r from-emerald-500 to-green-600" :style="`width: ${Math.min(100, (row.c / (spec[0]?.c||1)) * 100)}%`"></div>
                                    </div>
                                    <div class="w-10 text-xs text-gray-500" x-text="row.c"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>

<?php /**PATH /Users/mustafaaljaf/Documents/augment-projects/Connect Job V2/app/resources/views/dashboards/company.blade.php ENDPATH**/ ?>