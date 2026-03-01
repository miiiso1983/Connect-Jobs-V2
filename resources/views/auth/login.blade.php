<x-guest-layout>
    <div class="grid md:grid-cols-2 gap-6 items-stretch">
        {{-- Left Side - Branding --}}
		        <div class="hidden md:flex rounded-xl bg-gradient-to-br from-[#4A00B8] via-[#5A00E1] to-[#3C0094] p-8 text-white shadow-2xl">
            <div class="my-auto space-y-6">
                <x-application-logo class="h-14 w-auto" />
                <h2 class="text-3xl font-bold">مرحباً بعودتك</h2>
	                <p class="text-[#38BDF8] text-lg">سجّل دخولك وتابع التقديم على الوظائف المناسبة لك</p>
                <ul class="text-white/90 text-sm space-y-3">
                    <li class="flex items-center gap-3">
	                        <div class="w-8 h-8 rounded-full bg-[#38BDF8]/20 flex items-center justify-center">
	                            <svg class="w-4 h-4 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <span>تتبع طلباتك بسهولة</span>
                    </li>
                    <li class="flex items-center gap-3">
	                        <div class="w-8 h-8 rounded-full bg-[#38BDF8]/20 flex items-center justify-center">
	                            <svg class="w-4 h-4 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                        </div>
                        <span>احفظ الوظائف للرجوع لاحقاً</span>
                    </li>
                    <li class="flex items-center gap-3">
	                        <div class="w-8 h-8 rounded-full bg-[#38BDF8]/20 flex items-center justify-center">
	                            <svg class="w-4 h-4 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </div>
                        <span>احصل على تنبيهات الفرص الجديدة</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Right Side - Login Form --}}
        <div>
            <div class="md:hidden flex justify-center mb-6">
                <x-application-logo class="h-12 w-auto" />
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white">تسجيل الدخول</h3>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">أدخل بيانات حسابك للمتابعة</p>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-control">
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" class="mt-1" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="form-control mt-4">
                        <x-input-label for="password" :value="__('Password')" />
                        <x-text-input id="password" type="password" name="password" required autocomplete="current-password" class="mt-1" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="form-control mt-4">
                        <label for="remember_me" class="label cursor-pointer justify-start gap-2">
		                            <input id="remember_me" type="checkbox" class="checkbox checkbox-sm border-[#4A00B8] checked:bg-[#4A00B8]" name="remember">
                            <span class="label-text text-gray-700 dark:text-gray-300">{{ __('Remember me') }}</span>
                        </label>
                    </div>

                    <div class="mt-6">
		                        <button type="submit" class="w-full py-3 px-4 rounded-lg bg-gradient-to-r from-[#4A00B8] to-[#5A00E1] hover:from-[#3C0094] hover:to-[#4A00B8] text-white font-bold transition-all duration-300 shadow-lg hover:shadow-xl">
                            {{ __('Log in') }}
                        </button>
                    </div>

                    <div class="mt-4 flex items-center justify-between text-sm">
                        @if (Route::has('password.request'))
		                            <a class="text-[#4A00B8] dark:text-[#38BDF8] hover:underline" href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
                        @endif
							<a class="text-gray-600 dark:text-gray-400 hover:text-[#4A00B8] dark:hover:text-[#38BDF8]" href="{{ route('register') }}">حساب جديد</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
