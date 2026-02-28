<x-app-layout>
    <x-slot name="header">
        <div class="rounded-xl bg-gradient-to-br from-[#0D2660] via-[#102E66] to-[#0A1E46] text-white p-6">
            <h2 class="text-xl font-bold">لوحة الباحث عن عمل</h2>
            <p class="text-[#E7C66A] text-sm mt-1">إدارة البروفايل • السيرة الذاتية • توثيق الصيادلة</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="alert alert-success shadow-lg">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-lg font-bold text-gray-800 dark:text-white">الملف الشخصي</div>
                        <div class="text-sm text-gray-600 dark:text-gray-300">أكمل بياناتك لزيادة فرص المطابقة.</div>
                    </div>
                    <a href="{{ route('jobseeker.profile.edit') }}" class="btn bg-[#0D2660] hover:bg-[#0a1d4d] text-white border-none">تعديل</a>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="badge {{ ($js->profile_completed ?? false) ? 'badge-success' : 'badge-warning' }} badge-outline">
                        {{ ($js->profile_completed ?? false) ? 'الملف مكتمل' : 'الملف غير مكتمل' }}
                    </span>
                    <span class="badge badge-ghost">CV: {{ !empty($js->cv_file ?? null) ? 'مرفوعة' : 'غير مرفوعة' }}</span>
                    @if(!empty($js->cv_verified ?? null))
                        <span class="badge badge-success">CV موثقة</span>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">
                <div class="text-lg font-bold text-gray-800 dark:text-white">السيرة الذاتية</div>
                <div class="text-sm text-gray-600 dark:text-gray-300 mt-1">ارفع السيرة الذاتية ثم قم بطلب التوثيق (للصيادلة فقط).</div>
                <div class="mt-4 flex flex-wrap gap-2 items-center">
                    @if(!empty($js->cv_file ?? null))
                        <a href="{{ Storage::url($js->cv_file) }}" target="_blank" class="btn btn-ghost text-primary">عرض CV</a>
                    @else
                        <span class="text-sm text-gray-500">لا توجد سيرة مرفوعة.</span>
                    @endif
                    <a href="{{ route('jobseeker.profile.edit') }}" class="btn">رفع/تعديل</a>
                </div>
            </div>
        </div>

        @if(($isPharmacist ?? false))
	            @php
	                $latest = $latestCvVerificationRequest ?? null;
	                $latestStatus = $latest->status ?? null;
	                $hasCv = !empty($js->cv_file ?? null);
	                $isVerified = (bool)($js->cv_verified ?? false);

	                // Stepper state (1..4)
	                // 1) رفع CV
	                // 2) إرسال الطلب
	                // 3) قيد المراجعة
	                // 4) موثق
	                if (!$hasCv) {
	                    $step = 1;
	                } elseif ($isVerified) {
	                    $step = 4;
	                } elseif ($latestStatus === \App\Models\CvVerificationRequest::STATUS_PENDING) {
	                    $step = 3;
	                } elseif ($latestStatus === \App\Models\CvVerificationRequest::STATUS_REJECTED) {
	                    $step = 2;
	                } else {
	                    $step = 2;
	                }
	            @endphp

	            <div class="card bg-white dark:bg-gray-800 shadow">
	                <div class="card-body p-6">
	                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
	                        <div class="flex items-start gap-3">
	                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#0D2660] to-[#0A1E46] flex items-center justify-center text-white">
	                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
	                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
	                                </svg>
	                            </div>
	                            <div>
	                                <div class="text-lg font-bold text-gray-800 dark:text-white">توثيق السيرة الذاتية للصيادلة</div>
	                                <div class="text-sm text-gray-600 dark:text-gray-300 mt-1">
	                                    توثيق إضافي لرفع المصداقية لدى الشركات. متاح فقط إذا كان المسمى الوظيفي يحتوي على (صيدل) أو (pharmac).
	                                </div>
	                            </div>
	                        </div>

	                        <div class="flex flex-wrap items-center gap-2">
	                            <span class="badge badge-outline">Pharmacist</span>
	                            @if($isVerified)
	                                <span class="badge badge-success">موثّق</span>
	                            @elseif($latestStatus === \App\Models\CvVerificationRequest::STATUS_PENDING)
	                                <span class="badge badge-info">قيد المراجعة</span>
	                            @elseif($latestStatus === \App\Models\CvVerificationRequest::STATUS_REJECTED)
	                                <span class="badge badge-warning">مرفوض</span>
	                            @else
	                                <span class="badge badge-ghost">غير مُرسل</span>
	                            @endif
	                        </div>
	                    </div>

	                    <div class="mt-5">
	                        <ul class="steps steps-vertical md:steps-horizontal w-full">
	                            <li class="step {{ $step >= 1 ? 'step-primary' : '' }}">رفع CV</li>
	                            <li class="step {{ $step >= 2 ? 'step-primary' : '' }}">إرسال الطلب</li>
	                            <li class="step {{ $step >= 3 ? 'step-primary' : '' }}">مراجعة الإدارة</li>
	                            <li class="step {{ $step >= 4 ? 'step-primary' : '' }}">توثيق</li>
	                        </ul>
	                    </div>

	                    <div class="mt-5 space-y-3">
	                        @if($isVerified)
	                            <div class="alert alert-success">
	                                <span>تم توثيق السيرة الذاتية بنجاح. ستظهر للشركات كـ “CV موثّقة”.</span>
	                            </div>
	                        @elseif(!$hasCv)
	                            <div class="alert">
	                                <span>ارفع السيرة الذاتية أولاً (PDF/Word) ثم أرسل طلب التوثيق.</span>
	                            </div>
	                        @elseif($latestStatus === \App\Models\CvVerificationRequest::STATUS_PENDING)
	                            <div class="alert alert-info">
	                                <span>طلبك قيد المراجعة الآن. عادةً تتم المراجعة خلال 24–48 ساعة.</span>
	                            </div>
	                        @elseif($latestStatus === \App\Models\CvVerificationRequest::STATUS_REJECTED)
	                            <div class="alert alert-warning">
	                                <div>
	                                    <div class="font-semibold">تم رفض الطلب.</div>
	                                    @if(!empty($latest->admin_notes ?? null))
	                                        <div class="text-sm mt-1 opacity-90">ملاحظات الإدارة: {{ $latest->admin_notes }}</div>
	                                    @else
	                                        <div class="text-sm mt-1 opacity-90">يمكنك تحديث السيرة الذاتية ثم إعادة الإرسال.</div>
	                                    @endif
	                                </div>
	                            </div>
	                        @else
	                            <div class="alert">
	                                <span>لم يتم إرسال طلب توثيق بعد. يمكنك الإرسال الآن.</span>
	                            </div>
	                        @endif
	                    </div>

	                    <div class="mt-5 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
	                        <div class="flex flex-wrap gap-2">
	                            @if($hasCv)
	                                <a href="{{ Storage::url($js->cv_file) }}" target="_blank" class="btn btn-ghost text-primary">عرض CV</a>
	                            @endif
	                            <a href="{{ route('jobseeker.profile.edit') }}" class="btn">تعديل السيرة/الملف</a>
	                        </div>

	                        <div>
	                            @if(!$isVerified && $hasCv && $latestStatus !== \App\Models\CvVerificationRequest::STATUS_PENDING)
	                                <form method="POST" action="{{ route('jobseeker.cv_verification.request') }}">
	                                    @csrf
	                                    <button type="submit" class="btn bg-[#0D2660] hover:bg-[#0a1d4d] text-white border-none">
	                                        {{ $latestStatus === \App\Models\CvVerificationRequest::STATUS_REJECTED ? 'إعادة إرسال طلب التوثيق' : 'طلب توثيق السيرة الذاتية' }}
	                                    </button>
	                                </form>
	                            @endif
	                        </div>
	                    </div>
	                </div>
	            </div>
        @endif
    </div>
</x-app-layout>

