<x-app-layout>
    <x-slot name="header">
		        <div class="rounded-xl bg-gradient-to-br from-[#4A00B8] via-[#5A00E1] to-[#3C0094] text-white p-6">
            <h2 class="text-xl font-bold">تفاصيل الشركة</h2>
	            <p class="text-[#38BDF8] text-sm mt-1">{{ $company->company_name }}</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-3">
                <h3 class="font-semibold mb-2">معلومات عامة</h3>
                <div class="text-sm space-y-1">
                    <div><strong>الاسم:</strong> {{ $company->company_name }}</div>
                    <div><strong>المحافظة:</strong> {{ $company->province }}</div>
                    <div><strong>القطاع:</strong> {{ $company->industry }}</div>
                    <div><strong>الحالة:</strong> <span class="badge {{ $company->status==='active' ? 'badge-success':'badge-ghost' }}">{{ $company->status }}</span></div>
                    <div><strong>البريد:</strong> {{ $company->user->email ?? '—' }}</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-3">
                <h3 class="font-semibold mb-2">وظائف الشركة</h3>
                <div class="text-sm text-gray-600">مفتوحة: <strong>{{ $jobsOpen }}</strong> · بانتظار الموافقة: <strong>{{ $jobsPending }}</strong></div>
                <div class="mt-3 overflow-x-auto">
                    <table class="table text-sm">
                        <thead><tr><th>العنوان</th><th>الحالة</th><th>الموافقة</th><th></th></tr></thead>
                        <tbody>
                        @foreach($company->jobs->take(10) as $j)
                            <tr>
                                <td>{{ $j->title }}</td>
                                <td><span class="badge {{ $j->status==='open' ? 'badge-success':'badge-ghost' }}">{{ $j->status }}</span></td>
                                <td>{!! $j->approved_by_admin ? '<span class="badge badge-success">موافق</span>' : '<span class="badge badge-ghost">بانتظار</span>' !!}</td>
                                <td><a class="btn btn-xs" href="{{ route('company.jobs.edit',$j) }}">تعديل</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <aside class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <h3 class="font-semibold mb-3">الموافقة</h3>
                <form method="POST" action="{{ route('admin.companies.approve',$company) }}">
                    @csrf
                    <button class="btn btn-primary w-full">موافقة على الشركة</button>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <h3 class="font-semibold mb-3">الاشتراك</h3>
                <form method="POST" action="{{ route('admin.companies.subscription',$company) }}" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-input-label value="الخطة" />
                        <select name="subscription_plan" class="select select-bordered w-full">
                            @foreach (['free','basic','pro','enterprise'] as $plan)
                                <option value="{{ $plan }}" @selected($company->subscription_plan==$plan)>{{ $plan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label value="تاريخ الانتهاء (يوم)" />
                        <input type="date" name="subscription_expiry" value="{{ $company->subscription_expiry }}" class="input input-bordered w-full" />
                    </div>
                    <div>
                        <x-input-label value="تاريخ/وقت الانتهاء" />
                        <input type="datetime-local" name="subscription_expires_at" value="{{ $company->subscription_expires_at ? $company->subscription_expires_at->format('Y-m-d\TH:i') : '' }}" class="input input-bordered w-full" />
                    </div>
                    <x-primary-button class="w-full">تحديث الاشتراك</x-primary-button>
                </form>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <h3 class="font-semibold mb-3">إجراءات الحساب</h3>
                <form method="POST" action="{{ route('admin.companies.user.toggle', $company) }}" class="mb-3">
                    @csrf
                    @method('PUT')
                    <button class="btn w-full">{{ ($company->user->status ?? 'active')==='active' ? 'إيقاف المستخدم' : 'تنشيط المستخدم' }}</button>
                </form>
                <form method="POST" action="{{ route('admin.companies.user.email', $company) }}" class="space-y-2">
                    @csrf
                    <x-input-label value="قالب جاهز (اختياري)" />
                    @if(($emailTemplates ?? collect())->count() > 0)
                        <select name="template" class="select select-bordered w-full">
                            <option value="">—</option>
                            @foreach($emailTemplates as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} — {{ \Illuminate\Support\Str::limit($t->subject, 50) }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500">يدعم القالب المتغيّرات @{{name}} و @{{company}}. عند ترك الحقول أدناه فارغة سيتم استخدام القالب.</p>
                    @else
                        <p class="text-xs text-gray-500">لا توجد قوالب مُنشأة بعد. يمكنك إنشاء القوالب من صفحة الإعدادات.</p>
                    @endif

                    <x-input-label value="الموضوع" />
                    <input type="text" name="subject" class="input input-bordered w-full" placeholder="موضوع الرسالة" />
                    <x-input-label value="نص الرسالة" />
                    <textarea name="message" rows="4" class="textarea textarea-bordered w-full" placeholder="اكتب رسالتك هنا..."></textarea>
                    <x-primary-button class="w-full">إرسال بريد</x-primary-button>
                </form>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <h3 class="font-semibold mb-3">حذف الشركة</h3>
                <form method="POST" action="{{ route('admin.companies.destroy', $company) }}" x-data="{open:false}">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="open=true" class="btn btn-error w-full">حذف نهائياً</button>
                    <x-confirm-modal title="تأكيد الحذف" message="سيتم حذف الشركة وجميع وظائفها والحساب المرتبط بها. هل أنت متأكد؟" />
                </form>
            </div>

        </aside>
    </div>
</x-app-layout>

