<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">لوحة المتقدم</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="card bg-base-100 p-6 shadow">
            <p class="mb-4">أكمل بروفايلك وارفع السيرة الذاتية لزيادة نسبة المطابقة عند التقديم.</p>
            <a href="{{ route('jobseeker.profile.edit') }}" class="btn btn-primary">تعديل البروفايل</a>
        </div>
    </div>
</x-app-layout>

