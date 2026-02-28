<x-app-layout>
    <x-slot name="header">
	        <div class="rounded-xl bg-gradient-to-br from-[#5B21B6] via-[#6D28D9] to-[#4C1D95] text-white p-6">
            <h2 class="text-xl font-bold">التوثيق (للصيادلة)</h2>
	            <p class="text-[#38BDF8] text-sm mt-1">حالة الطلب • إرسال طلب التوثيق • متابعة المراجعة</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="alert alert-success shadow-lg">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if(!($isPharmacist ?? false))
            <div class="card bg-white dark:bg-gray-800 shadow">
                <div class="card-body p-6">
                    <div class="alert alert-warning">
	                        <span>هذه الصفحة مخصصة لتوثيق السيرة الذاتية للصيادلة فقط. إذا كنت صيدلانياً، حدّث بيانات ملفك (مثل: المسمى الوظيفي/التخصص/الكلية/القسم) لتتضمن (صيدل...) أو (pharmac...) ثم أعد المحاولة.</span>
                    </div>
                    <div class="mt-4">
	                        <a href="{{ route('jobseeker.profile.edit') }}" class="btn bg-[#5B21B6] hover:bg-[#4C1D95] text-white border-none">تعديل الملف الشخصي</a>
                    </div>
                </div>
            </div>
        @elseif(!($cvVerificationAvailable ?? true))
            <div class="alert alert-warning">
                <span>ميزة التوثيق غير متاحة حالياً (تحتاج تحديث قاعدة البيانات). يرجى المحاولة لاحقاً.</span>
            </div>
        @else
            @php
                $latest = $latestCvVerificationRequest ?? null;
                $latestStatus = $latest->status ?? null;
                $hasCv = !empty($js->cv_file ?? null);
				$hasEducation = !empty($js->university_name ?? null) && !empty($js->college_name ?? null) && !empty($js->graduation_year ?? null);
                $isVerified = (bool)($js->cv_verified ?? false);
                if (!$hasCv) {
                    $step = 1;
                } elseif ($isVerified) {
                    $step = 4;
                } elseif ($latestStatus === \App\Models\CvVerificationRequest::STATUS_PENDING) {
                    $step = 3;
                } else {
                    $step = 2;
                }
            @endphp

            <div class="card bg-white dark:bg-gray-800 shadow">
                <div class="card-body p-6">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div class="flex items-start gap-3">
	                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#5B21B6] to-[#4C1D95] flex items-center justify-center text-white">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-gray-800 dark:text-white">توثيق السيرة الذاتية للصيادلة</div>
                                <div class="text-sm text-gray-600 dark:text-gray-300 mt-1">بعد التوثيق ستظهر سيرتك الذاتية للشركات كـ “CV موثّقة”.</div>
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
                            <div class="alert alert-success"><span>تم توثيق السيرة الذاتية بنجاح.</span></div>
                        @elseif(!$hasCv)
                            <div class="alert"><span>يرجى رفع السيرة الذاتية أولاً (PDF/Word) ثم إرسال طلب التوثيق.</span></div>
                        @elseif($latestStatus === \App\Models\CvVerificationRequest::STATUS_PENDING)
                            <div class="alert alert-info"><span>طلبك قيد المراجعة الآن. عادةً تتم المراجعة خلال 24–48 ساعة.</span></div>
                        @elseif($latestStatus === \App\Models\CvVerificationRequest::STATUS_REJECTED)
                            <div class="alert alert-warning">
                                <div>
                                    <div class="font-semibold">تم رفض الطلب.</div>
                                    @if(!empty($latest->admin_notes ?? null))
                                        <div class="text-sm mt-1 opacity-90">ملاحظات الإدارة: {{ $latest->admin_notes }}</div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="alert"><span>لم يتم إرسال طلب توثيق بعد. يمكنك الإرسال الآن.</span></div>
                        @endif

							@if(!$isVerified && $hasCv && !$hasEducation)
								<div class="alert alert-warning">
									<span>قبل إرسال الطلب، يرجى إكمال معلومات الدراسة: اسم الجامعة، اسم الكلية، سنة التخرج.</span>
								</div>
							@endif
                    </div>

                    <div class="mt-5 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                        <div class="flex flex-wrap gap-2">
                            @if($hasCv)
                                <a href="{{ Storage::url($js->cv_file) }}" target="_blank" class="btn btn-ghost text-primary">عرض CV</a>
                            @endif
                            <a href="{{ route('jobseeker.profile.edit') }}" class="btn">رفع/تعديل السيرة الذاتية</a>
                        </div>

                        <div>
							@if(!$isVerified && $hasCv && $hasEducation && $latestStatus !== \App\Models\CvVerificationRequest::STATUS_PENDING)
                                <form method="POST" action="{{ route('jobseeker.cv_verification.request') }}">
                                    @csrf
	                                    <button type="submit" class="btn bg-[#5B21B6] hover:bg-[#4C1D95] text-white border-none">
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
