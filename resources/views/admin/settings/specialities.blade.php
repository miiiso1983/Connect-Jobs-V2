<x-app-layout>
    <x-slot name="header">
        <div class="rounded-xl bg-gradient-to-br from-[#4A00B8] via-[#5A00E1] to-[#3C0094] text-white p-6">
            <h2 class="text-xl font-bold">إدارة التخصصات</h2>
            <p class="text-[#38BDF8] text-sm mt-1">إضافة، تعديل، وحذف التخصصات يدوياً</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="p-3 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-200">{{ session('status') }}</div>
        @endif

        {{-- Add Single Speciality --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#4A00B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                إضافة تخصص جديد
            </h3>
            <form method="POST" action="{{ route('admin.settings.store') }}" class="flex gap-3 items-end">
                @csrf
                <input type="hidden" name="setting_type" value="speciality" />
                <div class="flex-1">
                    <x-input-label for="value" value="اسم التخصص" />
                    <x-text-input id="value" name="value" class="block mt-1 w-full" placeholder="مثال: صيدلة، طب أسنان..." required />
                </div>
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-gradient-to-r from-[#4A00B8] to-[#5A00E1] hover:from-[#3C0094] hover:to-[#4A00B8] text-white font-medium transition-all shadow-lg">
                    إضافة
                </button>
            </form>
        </div>

        {{-- Bulk Add --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                إضافة عدة تخصصات دفعة واحدة
            </h3>
            <form method="POST" action="{{ route('admin.settings.bulk') }}">
                @csrf
                <input type="hidden" name="setting_type" value="speciality" />
                <div class="mb-3">
                    <x-input-label value="أدخل كل تخصص في سطر منفصل" />
                    <textarea name="values" rows="5" class="mt-1 w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700" placeholder="صيدلة&#10;طب أسنان&#10;تمريض&#10;تحليلات مرضية"></textarea>
                </div>
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-[#38BDF8] hover:bg-[#0EA5E9] text-white font-medium transition-all shadow">
                    رفع جماعي
                </button>
            </form>
        </div>

        {{-- Existing Specialities List --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="flex items-center gap-3 p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="w-10 h-10 rounded-lg bg-[#4A00B8] flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">التخصصات الحالية</h3>
                <span class="bg-[#4A00B8] text-white text-xs font-bold px-3 py-1 rounded-full">{{ $specialities->count() }} تخصص</span>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($specialities as $s)
                    <div class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <form method="POST" action="{{ route('admin.settings.update', $s) }}" class="flex items-center gap-3 flex-1">
                            @csrf
                            @method('PUT')
                            <input type="text" name="value" value="{{ $s->value }}" class="rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-700 flex-1 max-w-md" />
                            <button type="submit" class="px-4 py-2 rounded-lg bg-gray-700 hover:bg-gray-800 text-white text-sm font-medium transition-colors">تحديث</button>
                        </form>
                        <form method="POST" action="{{ route('admin.settings.destroy', $s) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا التخصص؟');" class="mr-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium transition-colors">حذف</button>
                        </form>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        لا توجد تخصصات بعد. أضف تخصصاً جديداً أعلاه.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Back Link --}}
        <div class="text-center">
            <a href="{{ route('admin.settings.index') }}" class="text-[#4A00B8] dark:text-[#38BDF8] hover:underline text-sm">← العودة لجميع الإعدادات</a>
        </div>
    </div>
</x-app-layout>
