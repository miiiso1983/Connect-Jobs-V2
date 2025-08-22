<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">إدارة المناطق (Districts)</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="p-3 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-200">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.districts.store') }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow flex gap-3 items-end">
            @csrf
            <div>
                <x-input-label for="province" value="المحافظة" />
                <select name="province" id="province" class="mt-1 rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                    @foreach ($provinces as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                <x-input-label for="name" value="المنطقة" />
        <form method="POST" action="{{ route('admin.districts.bulk') }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow flex flex-col gap-2 mt-3">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                <div>
                    <x-input-label for="province_bulk" value="المحافظة" />
                    <select name="province" id="province_bulk" class="mt-1 rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                        @foreach ($provinces as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <x-input-label for="values" value="المناطق (قيمة في كل سطر)" />
                    <textarea id="values" name="values" rows="4" class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700" placeholder="مثال:\nالمنطقة 1\nالمنطقة 2\n..."></textarea>
                </div>
            </div>
            <x-primary-button>رفع جماعي</x-primary-button>
        </form>
                <x-text-input id="name" name="name" class="block mt-1 w-full" />
            </div>
            <x-primary-button>إضافة</x-primary-button>
        </form>

        <form method="GET" action="{{ route('admin.districts.export') }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow flex items-end gap-3 mt-3">
            <div>
                <x-input-label value="تصدير CSV" />
                <select name="province" class="mt-1 rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                    <option value="">جميع المحافظات</option>
                    @foreach ($provinces as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <x-primary-button>تنزيل</x-primary-button>
        </form>

        <form method="POST" action="{{ route('admin.districts.import') }}" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow flex items-end gap-3 mt-3">
            @csrf
            <div class="flex-1">
                <x-input-label for="csv" value="استيراد CSV" />
                <input type="file" id="csv" name="csv" accept=".csv,.txt" class="mt-1 block w-full text-sm" />
            </div>
            <div>
                <x-input-label value="المحافظة (اختياري في حال عدم وجود عمود province)" />
                <select name="province" class="mt-1 rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                    <option value="">—</option>
                    @foreach ($provinces as $p)
                        <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <x-primary-button>استيراد</x-primary-button>
        </form>

        <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow divide-y divide-gray-100 dark:divide-gray-700">
            @foreach ($byProvince as $prov => $items)
                <div class="p-4">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-2">{{ $prov }}</h3>
                    @foreach ($items as $d)
                        <div class="flex items-center gap-2 mb-2">
                            <form method="POST" action="{{ route('admin.districts.destroy',$d) }}">
                                @csrf
                                @method('DELETE')
                                <button class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs">حذف</button>
                            </form>
                            <div class="text-sm text-gray-700 dark:text-gray-300">{{ $d->name }}</div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>

