<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">تفعيل الحساب</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="alert alert-success shadow">{{ session('status') }}</div>
        @endif

        <div class="card bg-base-100 p-6 shadow-xl space-y-6">
            <form method="POST" action="{{ route('verify.code.send') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div class="form-control">
                    <x-input-label value="إرسال كود التفعيل عبر" />
                    <select name="channel" class="select select-bordered">
                        <option value="email">البريد الإلكتروني</option>
                        <option value="whatsapp">الواتساب</option>
                    </select>
                </div>
                <div class="form-control">
                    <x-input-label for="whatsapp_number" value="رقم الواتساب (اختياري)" />
                    <x-text-input id="whatsapp_number" name="whatsapp_number" placeholder="مثال: 07xxxxxxxx" />
                </div>
                <div class="md:col-span-2">
                    <x-primary-button class="w-full">إرسال الرمز</x-primary-button>
                </div>
            </form>

            <form method="POST" action="{{ route('verify.code.verify') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                @csrf
                <div class="form-control md:col-span-2">
                    <x-input-label for="code" value="أدخل كود التفعيل" />
                    <x-text-input id="code" name="code" />
                </div>
                <x-primary-button class="w-full">تفعيل</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>

