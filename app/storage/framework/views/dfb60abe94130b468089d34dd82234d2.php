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
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">وظائف الشركة</h2>
     <?php $__env->endSlot(); ?>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <?php if(session('status')): ?>
            <div class="alert alert-success shadow"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

        <div class="flex flex-wrap gap-3">
            <a href="<?php echo e(route('company.jobs.create')); ?>" class="btn btn-primary">وظيفة جديدة</a>
            <a href="<?php echo e(route('company.applicants.index')); ?>" class="btn">فلترة المتقدمين</a>
        </div>

        <div class="overflow-x-auto card bg-base-100 shadow">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>العنوان</th>
                        <th>المحافظة</th>
                        <th>الوصف الوظيفي</th>
                        <th>نشر/إيقاف</th>
                        <th>الحالة</th>
                        <th>موافقة الأدمن</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="px-4 py-2"><?php echo e($j->id); ?></td>
                            <td class="px-4 py-2">
                                <a href="<?php echo e(route('company.jobs.edit',$j)); ?>" class="text-indigo-600 hover:underline"><?php echo e($j->title); ?></a>
                            </td>
                            <td class="px-4 py-2"><?php echo e($j->province); ?></td>
                            <td class="px-4 py-2">
                                <?php if($j->jd_file): ?>
                                    <a href="<?php echo e(Storage::url($j->jd_file)); ?>" class="text-indigo-600" target="_blank">عرض</a>
                                <?php else: ?> - <?php endif; ?>
                            </td>
                            <td class="px-4 py-2">
                                <form method="POST" action="<?php echo e(route('company.jobs.toggle',$j)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <button class="btn btn-xs <?php echo e($j->status==='open' ? 'btn-error' : 'btn-success'); ?>">
                                        <?php echo e($j->status==='open' ? 'إيقاف' : 'نشر'); ?>

                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-2"><span class="badge <?php echo e($j->status==='open' ? 'badge-success':'badge-ghost'); ?>"><?php echo e($j->status); ?></span></td>
                            <td class="px-4 py-2">
                                <div class="flex flex-wrap gap-2">
                                    <a href="<?php echo e(route('company.jobs.show',$j)); ?>" class="btn btn-xs">تفاصيل</a>
                                    <a href="<?php echo e(route('company.jobs.edit',$j)); ?>" class="btn btn-xs btn-primary">تعديل</a>
                                    <form method="POST" action="<?php echo e(route('company.jobs.destroy',$j)); ?>" x-data="{open:false}">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="button" @click="open=true" class="btn btn-xs btn-error">حذف</button>
                                        <?php if (isset($component)) { $__componentOriginal2cfaf2d8c559a20e3495c081df2d0b10 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2cfaf2d8c559a20e3495c081df2d0b10 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.confirm-modal','data' => ['title' => 'تأكيد الحذف','message' => 'هل أنت متأكد من حذف هذه الوظيفة؟ لا يمكن التراجع.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('confirm-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'تأكيد الحذف','message' => 'هل أنت متأكد من حذف هذه الوظيفة؟ لا يمكن التراجع.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2cfaf2d8c559a20e3495c081df2d0b10)): ?>
<?php $attributes = $__attributesOriginal2cfaf2d8c559a20e3495c081df2d0b10; ?>
<?php unset($__attributesOriginal2cfaf2d8c559a20e3495c081df2d0b10); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2cfaf2d8c559a20e3495c081df2d0b10)): ?>
<?php $component = $__componentOriginal2cfaf2d8c559a20e3495c081df2d0b10; ?>
<?php unset($__componentOriginal2cfaf2d8c559a20e3495c081df2d0b10); ?>
<?php endif; ?>
                                    </form>
                                </div>
                            </td>
                            <td class="px-4 py-2"><?php echo e($j->approved_by_admin ? 'نعم' : 'بانتظار'); ?></td>
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

<?php /**PATH /Users/mustafaaljaf/Documents/augment-projects/Connect Job V2/app/resources/views/company/jobs/index.blade.php ENDPATH**/ ?>