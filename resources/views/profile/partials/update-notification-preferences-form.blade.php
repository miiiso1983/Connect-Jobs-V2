<section>
    <header>
        <h2 class="text-lg font-medium text-base-content">تفضيلات الإشعارات</h2>
        <p class="mt-1 text-sm text-base-content/70">اختر الإشعارات التي ترغب باستلامها عبر البريد الإلكتروني.</p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="form-control">
            <label class="label cursor-pointer justify-start gap-3">
                <input type="checkbox" name="application_notifications_opt_in" value="1" class="checkbox checkbox-primary" {{ old('application_notifications_opt_in', (int)($user->application_notifications_opt_in ?? 1)) ? 'checked' : '' }}>
                <span class="label-text">
                    استلام تنبيه عند تقديم طلب توظيف على إحدى وظائف شركتي
                </span>
            </label>
        </div>

        <div class="form-control">
            <label class="label cursor-pointer justify-start gap-3">
                <input type="checkbox" name="profile_view_notifications_opt_in" value="1" class="checkbox checkbox-primary" {{ old('profile_view_notifications_opt_in', (int)($user->profile_view_notifications_opt_in ?? 1)) ? 'checked' : '' }}>
                <span class="label-text">
                    استلام تنبيه عندما تطّلع شركة على ملفي/طلباتي
                </span>
            </label>
        </div>

        <div class="flex items-center gap-3">
            <x-primary-button>حفظ التغييرات</x-primary-button>
            @if (session('status') === 'profile-updated')
                <p class="text-sm text-green-600">تم الحفظ</p>
            @endif
        </div>
    </form>
</section>

