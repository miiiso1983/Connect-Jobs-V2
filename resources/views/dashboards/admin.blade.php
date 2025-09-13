<x-app-layout>
    <x-slot name="header">
        <div class="rounded-xl bg-gradient-to-br from-[#0D2660] via-[#102E66] to-[#0A1E46] text-white p-6">
            <h2 class="text-xl font-bold">لوحة تحكم الأدمن</h2>
            <p class="text-[#E7C66A] text-sm mt-1">إدارة الموافقات ومراجعة الوظائف</p>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-base-100 shadow sm:rounded-xl p-6 space-y-4">
                <p>مرحبًا! يمكنك إدارة الشركات والموافقة على الحسابات.</p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.pending.companies', [], false) ?? '#' }}" class="btn btn-sm btn-primary">الشركات بانتظار الموافقة</a>
                    <a href="{{ route('admin.pending.jobs', [], false) ?? '#' }}" class="btn btn-sm btn-ghost text-primary">الوظائف بانتظار المراجعة</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

