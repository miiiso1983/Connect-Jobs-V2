<x-guest-layout>
    <div class="max-w-xl mx-auto py-16 text-center">
        <h1 class="text-3xl font-extrabold text-primary mb-3">تم إلغاء تنبيه الوظائف</h1>
        <p class="opacity-80">لن تتلقى رسائل هذا التنبيه بعد الآن. يمكنك إدارة جميع تنبيهاتك من لوحة الباحث عن عمل.</p>
        <div class="mt-6 flex gap-3 justify-center">
            <a href="/" class="btn btn-primary">العودة للرئيسية</a>
            <a href="{{ route('jobseeker.alerts.index') }}" class="btn btn-secondary">إدارة التنبيهات</a>
        </div>
    </div>
</x-guest-layout>

