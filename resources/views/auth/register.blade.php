<x-guest-layout>
    <div class="grid md:grid-cols-2 gap-6 items-stretch">
        {{-- Left Side - Branding --}}
	        <div class="hidden md:flex rounded-xl bg-gradient-to-br from-[#5B21B6] via-[#6D28D9] to-[#4C1D95] p-8 text-white shadow-2xl">
            <div class="my-auto space-y-6">
                <x-application-logo class="h-14 w-auto" />
                <h2 class="text-3xl font-bold">ابدأ رحلتك المهنية</h2>
	                <p class="text-[#38BDF8] text-lg">أنشئ حسابك واكتشف فرص العمل المناسبة لك</p>
                <ul class="text-white/90 text-sm space-y-3">
                    <li class="flex items-center gap-3">
	                        <div class="w-8 h-8 rounded-full bg-[#38BDF8]/20 flex items-center justify-center">
	                            <svg class="w-4 h-4 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <span>آلاف الوظائف في انتظارك</span>
                    </li>
                    <li class="flex items-center gap-3">
	                        <div class="w-8 h-8 rounded-full bg-[#38BDF8]/20 flex items-center justify-center">
	                            <svg class="w-4 h-4 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <span>شركات موثوقة ومعتمدة</span>
                    </li>
                    <li class="flex items-center gap-3">
	                        <div class="w-8 h-8 rounded-full bg-[#38BDF8]/20 flex items-center justify-center">
	                            <svg class="w-4 h-4 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <span>تقديم سريع وسهل</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Right Side - Registration Form --}}
        <div>
            <div class="md:hidden flex justify-center mb-6">
                <x-application-logo class="h-12 w-auto" />
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white">إنشاء حساب جديد</h3>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">أدخل بياناتك للتسجيل</p>
                </div>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Name -->
                    <div class="form-control">
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" class="mt-1" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Email Address -->
                    <div class="form-control mt-4">
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" class="mt-1" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div class="form-control mt-4">
                        <x-input-label for="password" :value="__('Password')" />
                        <x-text-input id="password" type="password" name="password" required autocomplete="new-password" class="mt-1" />
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">استخدم 8 أحرف على الأقل مع مزيج من الحروف والأرقام</p>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-control mt-4">
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                        <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="mt-1" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <!-- Role Selection -->
                    <div class="form-control mt-4">
                        <x-input-label for="role" :value="__('Register as')" />
	                        <select id="role" name="role" class="select select-bordered w-full mt-1 focus:border-[#5B21B6] focus:ring-[#5B21B6]" required>
                            <option value="jobseeker" {{ old('role')==='jobseeker' ? 'selected' : '' }}>{{ __('Job Seeker') }}</option>
                            <option value="company" {{ old('role')==='company' ? 'selected' : '' }}>{{ __('Company') }}</option>
                        </select>
                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">{{ __('Company accounts require admin approval before activation.') }}</p>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>

                    <!-- Company Extra Fields -->
                    <div id="company-extra" class="mt-4 hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600">
                        <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">معلومات الشركة</h4>
                        <div class="grid grid-cols-1 gap-4">
                            <div class="form-control">
                                <x-input-label for="scientific_office_name" value="اسم المكتب العلمي" />
                                <x-text-input id="scientific_office_name" type="text" name="scientific_office_name" :value="old('scientific_office_name')" class="mt-1" />
                                <x-input-error :messages="$errors->get('scientific_office_name')" class="mt-2" />
                            </div>
                            <div class="form-control">
                                <x-input-label for="company_job_title" value="المسمى الوظيفي" />
                                <x-text-input id="company_job_title" type="text" name="company_job_title" :value="old('company_job_title')" class="mt-1" />
                                <x-input-error :messages="$errors->get('company_job_title')" class="mt-2" />
                            </div>
                            <div class="form-control">
                                <x-input-label for="mobile_number" value="رقم الموبايل" />
                                <x-text-input id="mobile_number" type="text" name="mobile_number" :value="old('mobile_number')" placeholder="07xxxxxxxx" class="mt-1" />
                                <x-input-error :messages="$errors->get('mobile_number')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function(){
                            const roleSel = document.getElementById('role');
                            const extra = document.getElementById('company-extra');
                            function toggleExtra(){ extra.classList.toggle('hidden', roleSel.value !== 'company'); }
                            roleSel.addEventListener('change', toggleExtra);
                            toggleExtra();
                        });
                    </script>

                    <div class="mt-6">
	                        <button type="submit" class="w-full py-3 px-4 rounded-lg bg-gradient-to-r from-[#5B21B6] to-[#6D28D9] hover:from-[#4C1D95] hover:to-[#5B21B6] text-white font-bold transition-all duration-300 shadow-lg hover:shadow-xl">
                            {{ __('Register') }}
                        </button>
                    </div>

                    <div class="mt-4 text-center text-sm">
                        <span class="text-gray-600 dark:text-gray-400">لديك حساب بالفعل؟</span>
	                        <a class="text-[#5B21B6] dark:text-[#38BDF8] hover:underline font-medium mr-1" href="{{ route('login') }}">تسجيل الدخول</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>

