<x-guest-layout>
    <div class="max-w-md mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8">
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-[#4A00B8] to-[#5A00E1] flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white">إعادة تعيين كلمة المرور</h3>
                <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">أدخل كلمة المرور الجديدة</p>
            </div>

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div class="form-control">
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" class="mt-1" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="form-control mt-4">
                    <x-input-label for="password" :value="__('كلمة المرور الجديدة')" />
                    <x-text-input id="password" type="password" name="password" required autocomplete="new-password" class="mt-1" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div class="form-control mt-4">
                    <x-input-label for="password_confirmation" :value="__('تأكيد كلمة المرور')" />
                    <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="mt-1" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="mt-6">
                    <button type="submit" class="w-full py-3 px-4 rounded-lg bg-gradient-to-r from-[#4A00B8] to-[#5A00E1] hover:from-[#3C0094] hover:to-[#4A00B8] text-white font-bold transition-all duration-300 shadow-lg hover:shadow-xl">
                        إعادة تعيين كلمة المرور
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
