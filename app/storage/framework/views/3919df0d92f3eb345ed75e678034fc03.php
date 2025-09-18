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
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">الشركات</h2>
     <?php $__env->endSlot(); ?>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <?php if(session('status')): ?>
            <div class="p-3 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-200"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

        <div class="bg-gradient-to-br from-indigo-50 to-white dark:from-gray-900 dark:to-gray-800 p-4 rounded-xl shadow-sm">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 rounded-lg bg-white dark:bg-gray-800 shadow">
                    <div class="text-xs text-gray-500">إجمالي الشركات</div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-gray-100"><?php echo e($companies->count()); ?></div>
                </div>
                <div class="p-4 rounded-lg bg-white dark:bg-gray-800 shadow">
                    <div class="text-xs text-gray-500">خطط فعالة</div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">—</div>
                </div>
                <div class="p-4 rounded-lg bg-white dark:bg-gray-800 shadow">
                    <div class="text-xs text-gray-500">بانتظار الموافقة</div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">—</div>
                </div>
                <div class="p-4 rounded-lg bg-white dark:bg-gray-800 shadow">
                    <div class="text-xs text-gray-500">موقوفة</div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">—</div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-start">#</th>
                        <th class="px-4 py-2 text-start">الشركة</th>
                        <th class="px-4 py-2 text-start">المحافظة</th>
                        <th class="px-4 py-2 text-start">الخطة</th>
                        <th class="px-4 py-2 text-start">انتهاء الاشتراك</th>
                        <th class="px-4 py-2 text-start">حالة</th>
                        <th class="px-4 py-2 text-start">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-t border-gray-100 dark:border-gray-700">
                            <td class="px-4 py-2"><?php echo e($c->id); ?></td>
                            <td class="px-4 py-2"><?php echo e($c->company_name); ?></td>
                            <td class="px-4 py-2"><?php echo e($c->province); ?></td>
                            <td class="px-4 py-2"><?php echo e($c->subscription_plan); ?></td>
                            <td class="px-4 py-2"><?php echo e($c->subscription_expiry); ?></td>
                            <td class="px-4 py-2"><span class="px-2 py-1 rounded-full text-xs <?php echo e($c->status==='active'?'bg-green-100 text-green-800':'bg-yellow-100 text-yellow-800'); ?>"><?php echo e($c->status); ?></span></td>
                            <td class="px-4 py-2 flex flex-wrap gap-2">
                                <form method="POST" action="<?php echo e(route('admin.companies.approve',$c)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs">موافقة</button>
                                </form>
                                <form method="POST" action="<?php echo e(route('admin.companies.subscription',$c)); ?>" class="flex items-center gap-2">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <select name="subscription_plan" class="rounded-lg text-xs border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                                        <?php $__currentLoopData = ['free','basic','pro','enterprise']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($plan); ?>" <?php if($c->subscription_plan==$plan): echo 'selected'; endif; ?>><?php echo e($plan); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <input type="date" name="subscription_expiry" value="<?php echo e($c->subscription_expiry); ?>" class="rounded-lg text-xs border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                                    <button class="px-3 py-1.5 rounded-lg bg-gray-700 hover:bg-gray-800 text-white text-xs">تحديث</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
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

<?php /**PATH /Users/mustafaaljaf/Documents/augment-projects/Connect Job V2/app/resources/views/admin/companies/index.blade.php ENDPATH**/ ?>