<x-guest-layout>
    <div class="grid md:grid-cols-2 gap-6 items-stretch">
        <div class="hidden md:flex rounded-xl bg-gradient-to-br from-[#0D2660] via-[#102E66] to-[#0A1E46] p-8 text-white">
            <div class="my-auto space-y-4">
                <x-application-logo class="h-12 w-auto" />
                <h2 class="text-2xl font-bold">مرحباً بعودتك</h2>
                <p class="text-white/80 text-sm">سجّل دخولك وتابع التقديم على الوظائف المناسبة لك.</p>
                <ul class="text-white/80 text-sm space-y-2 list-disc pr-5">
                    <li>تتبع طلباتك بسهولة</li>
                    <li>احفظ الوظائف للرجوع لاحقاً</li>
                    <li>احصل على تنبيهات الفرص الجديدة</li>
                </ul>
            </div>
        </div>
        <div>
        <div class="md:hidden flex justify-center mb-6">
            <x-application-logo class="h-12 w-auto" />
        </div>


    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="card bg-base-100 shadow-xl p-6">
        @csrf

        <div class="form-control">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="form-control mt-4">

        <p class="text-xs text-base-content/60 mt-1">{{ __('Use at least 8 characters with a mix of letters and numbers.') }}</p>

            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="form-control mt-4">
            <label for="remember_me" class="label cursor-pointer justify-start gap-2">
                <input id="remember_me" type="checkbox" class="checkbox checkbox-primary" name="remember">
                <span class="label-text">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="mt-6 flex items-center justify-between">
            @if (Route::has('password.request'))
                <a class="link link-hover text-sm" href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
            @endif
            <x-primary-button>
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
        </div>
    </div>

</x-guest-layout>
