<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="card bg-base-100 shadow-xl p-6">
        @csrf

        <!-- Name -->
        <div class="form-control">
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="form-control mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="form-control mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="form-control mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Role Selection -->
        <div class="form-control mt-4">
            <x-input-label for="role" :value="__('Register as')" />
            <select id="role" name="role" class="select select-bordered w-full" required>
                <option value="jobseeker" {{ old('role')==='jobseeker' ? 'selected' : '' }}>{{ __('Job Seeker') }}</option>
                <option value="company" {{ old('role')==='company' ? 'selected' : '' }}>{{ __('Company') }}</option>
            </select>
            <p class="text-xs text-base-content/70 mt-1">{{ __('Company accounts require admin approval before activation.') }}</p>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>


        <div id="company-extra" class="mt-4 hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <x-input-label for="scientific_office_name" value="اسم المكتب العلمي" />
                    <x-text-input id="scientific_office_name" type="text" name="scientific_office_name" :value="old('scientific_office_name')" />
                    <x-input-error :messages="$errors->get('scientific_office_name')" class="mt-2" />
                </div>
                <div class="form-control">
                    <x-input-label for="company_job_title" value="المسمى الوظيفي" />
                    <x-text-input id="company_job_title" type="text" name="company_job_title" :value="old('company_job_title')" />
                    <x-input-error :messages="$errors->get('company_job_title')" class="mt-2" />
                </div>
                <div class="form-control">
                    <x-input-label for="mobile_number" value="رقم الموبايل" />
                    <x-text-input id="mobile_number" type="text" name="mobile_number" :value="old('mobile_number')" placeholder="07xxxxxxxx" />
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

        <div class="mt-6 flex items-center justify-between">
            <a class="link link-hover text-sm" href="{{ route('login') }}">{{ __('Already registered?') }}</a>
            <x-primary-button>
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
