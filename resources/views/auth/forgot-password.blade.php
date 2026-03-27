<x-guest-layout>
    <div class="max-w-md mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-[#4A00B8] to-[#5A00E1] flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white">نسيت كلمة المرور؟</h3>
                <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">أدخل بريدك الإلكتروني وسنرسل لك رابط إعادة تعيين كلمة المرور</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email Address -->
                <div class="form-control">
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="email" class="mt-1" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="mt-6">
                    <button type="submit" class="w-full py-3 px-4 rounded-lg bg-gradient-to-r from-[#4A00B8] to-[#5A00E1] hover:from-[#3C0094] hover:to-[#4A00B8] text-white font-bold transition-all duration-300 shadow-lg hover:shadow-xl">
                        إرسال رابط إعادة التعيين
                    </button>
                </div>

                <div class="mt-4 text-center">
                    <a class="text-sm text-[#4A00B8] dark:text-[#38BDF8] hover:underline" href="{{ route('login') }}">العودة لتسجيل الدخول</a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
