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
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-lg font-bold text-gray-800 dark:text-white">توثيق السيرة الذاتية (للصيادلة)</div>
                        <div class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                            متاح فقط إذا كان المسمى الوظيفي يحتوي على (صيدل) أو (pharmac).
                        </div>
                    </div>
                    <span class="badge badge-outline">Pharmacist</span>
                </div>

                <div class="mt-4">
                    @php
                        $latest = $latestCvVerificationRequest ?? null;
                        $latestStatus = $latest->status ?? null;
                    @endphp

	                    @if((bool)($js->cv_verified ?? false))
	                        <div class="alert alert-success"><span>السيرة الذاتية موثقة.</span></div>
                    @elseif(empty($js->cv_file ?? null))
                        <div class="alert"><span>يرجى رفع السيرة الذاتية أولاً ثم يمكنك إرسال طلب توثيق.</span></div>
                    @elseif($latestStatus === \App\Models\CvVerificationRequest::STATUS_PENDING)
	                        <div class="alert"><span>طلب التوثيق قيد المراجعة.</span></div>
                    @elseif($latestStatus === \App\Models\CvVerificationRequest::STATUS_REJECTED)
                        <div class="alert alert-warning">
                            <div>
                                <div>تم رفض الطلب.</div>
                                @if(!empty($latest->admin_notes ?? null))
                                    <div class="text-sm mt-1">ملاحظات الإدارة: {{ $latest->admin_notes }}</div>
                                @endif
                            </div>
                        </div>
                        <form method="POST" action="{{ route('jobseeker.cv_verification.request') }}" class="mt-4">
                            @csrf
                            <button type="submit" class="btn bg-[#0D2660] hover:bg-[#0a1d4d] text-white border-none">إعادة إرسال طلب التوثيق</button>
                        </form>
                    @else
                        <div class="text-sm text-gray-600 dark:text-gray-300">لم يتم إرسال طلب توثيق بعد.</div>
                        <form method="POST" action="{{ route('jobseeker.cv_verification.request') }}" class="mt-4">
                            @csrf
                            <button type="submit" class="btn bg-[#0D2660] hover:bg-[#0a1d4d] text-white border-none">طلب توثيق السيرة الذاتية</button>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

