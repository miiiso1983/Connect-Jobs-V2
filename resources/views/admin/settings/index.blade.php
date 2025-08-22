<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">القوائم المنسدلة (Master Settings)</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="p-3 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-200">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.settings.store') }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow flex gap-3 items-end">
            @csrf
            <div>
                <x-input-label for="setting_type" value="نوع القائمة" />
                <select name="setting_type" id="setting_type" class="mt-1 rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                    @foreach (['job_title','province','speciality','gender'] as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <form method="POST" action="{{ route('admin.settings.bulk') }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow flex flex-col gap-2 mt-3">
                @csrf
                <div class="text-sm text-gray-600 dark:text-gray-300">إضافة عدة قيم دفعة واحدة (قيمة في كل سطر)</div>
                <textarea name="values" rows="4" class="rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700" placeholder="مثال:\nBaghdad\nErbil\nDuhok"></textarea>
                <input type="hidden" name="setting_type" id="bulk_setting_type" value="job_title" />
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600">النوع:</label>
                    <select class="rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700" onchange="document.getElementById('bulk_setting_type').value=this.value">
                        @foreach (['job_title','province','speciality','gender'] as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                    <x-primary-button>رفع جماعي</x-primary-button>
                </div>
            </form>

            <form method="GET" action="{{ route('admin.settings.export') }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow flex items-end gap-3 mt-3">
                <div>
                    <x-input-label value="تصدير CSV" />
                    <select name="type" class="mt-1 rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                        <option value="">جميع الأنواع</option>
                        @foreach (['job_title','province','speciality','gender'] as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <x-primary-button>تنزيل</x-primary-button>
            </form>

            <form method="POST" action="{{ route('admin.settings.import') }}" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow flex items-end gap-3 mt-3">
                @csrf
                <div class="flex-1">
                    <x-input-label for="csv" value="استيراد CSV" />
                    <input type="file" id="csv" name="csv" accept=".csv,.txt" class="mt-1 block w-full text-sm" />
                </div>
                <div>
                    <x-input-label value="النوع (اختياري في حال عدم وجود عمود setting_type)" />
                    <select name="type" class="mt-1 rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                        <option value="">—</option>
                        @foreach (['job_title','province','speciality','gender'] as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <x-primary-button>استيراد</x-primary-button>
            </form>
            <div class="flex-1">
                <x-input-label for="value" value="القيمة" />
                <x-text-input id="value" name="value" class="block mt-1 w-full" />
            </div>
            <x-primary-button>إضافة</x-primary-button>
        </form>

        <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow divide-y divide-gray-100 dark:divide-gray-700">
            @foreach ($types as $type)
                <div class="p-4">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-2">{{ $type }}</h3>
                    @foreach ($settings->where('setting_type',$type)->sortBy('value', SORT_NATURAL|SORT_FLAG_CASE) as $s)
                        <div class="flex items-center gap-2 mb-2">
                            <form method="POST" action="{{ route('admin.settings.update',$s) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PUT')
                                <input type="text" name="value" value="{{ $s->value }}" class="rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                                <button class="px-3 py-1.5 rounded-lg bg-gray-700 hover:bg-gray-800 text-white text-xs">تحديث</button>
                            </form>
                            <form method="POST" action="{{ route('admin.settings.destroy',$s) }}">
                                @csrf
                                @method('DELETE')
                                <button class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs">حذف</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>

