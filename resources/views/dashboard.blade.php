<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="stat bg-base-100 shadow">
                    <div class="stat-title">وظائفك المنشورة</div>
                    <div class="stat-value">12</div>
                    <div class="stat-desc">+2 اليوم</div>
                </div>
                <div class="stat bg-base-100 shadow">
                    <div class="stat-title">طلبات جديدة</div>
                    <div class="stat-value">34</div>
                    <div class="stat-desc">+5 آخر 24 ساعة</div>
                </div>
                <div class="stat bg-base-100 shadow">
                    <div class="stat-title">رسائل</div>
                    <div class="stat-value">7</div>
                    <div class="stat-desc">3 غير مقروءة</div>
                </div>
            </div>

            <div class="mt-6 card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="card-title">آخر الوظائف</h3>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>العنوان</th>
                                    <th>المحافظة</th>
                                    <th>الحالة</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>صيدلي سريري</td>
                                    <td>بغداد</td>
                                    <td><span class="badge badge-success">مفتوحة</span></td>
                                    <td><a href="#" class="btn btn-sm">عرض</a></td>
                                </tr>
                                <tr>
                                    <td>مندوب مبيعات</td>
                                    <td>نينوى</td>
                                    <td><span class="badge">مغلقة</span></td>
                                    <td><a href="#" class="btn btn-sm">عرض</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
