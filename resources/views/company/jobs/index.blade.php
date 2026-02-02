<x-app-layout>
    <x-slot name="header">
        <div class="rounded-xl bg-gradient-to-br from-[#0D2660] via-[#102E66] to-[#0A1E46] text-white p-6">
            <h2 class="text-xl font-bold">وظائف الشركة</h2>
            <p class="text-[#E7C66A] text-sm mt-1">إدارة إعلانات الوظائف ومتابعة حالاتها</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-green-800 dark:text-green-400">{{ session('status') }}</span>
            </div>
        @endif

        {{-- Action Buttons --}}
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('company.jobs.create') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[#0D2660] to-[#1a3a7a] text-white font-medium shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                وظيفة جديدة
            </a>
            <a href="{{ route('company.applicants.index') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-white dark:bg-gray-800 text-[#0D2660] dark:text-white border-2 border-[#0D2660]/20 font-medium shadow hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 hover:border-[#E7C66A]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                فلترة المتقدمين
            </a>
        </div>

        {{-- Jobs Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="flex items-center gap-3 p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="w-10 h-10 rounded-lg bg-[#0D2660] flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#E7C66A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">قائمة الوظائف</h3>
                <span class="bg-[#E7C66A] text-[#0D2660] text-xs font-bold px-3 py-1 rounded-full">{{ $jobs->count() }} وظيفة</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">العنوان</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">المحافظة</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الوصف الوظيفي</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">نشر/إيقاف</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الحالة</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">موافقة الأدمن</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($jobs as $j)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $j->id }}</td>
                                <td class="px-4 py-4">
                                    <a href="{{ route('company.jobs.edit',$j) }}" class="font-medium text-[#0D2660] dark:text-[#E7C66A] hover:underline">{{ $j->title }}</a>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $j->province }}</td>
                                <td class="px-4 py-4">
                                    @if($j->jd_file)
                                        <a href="{{ Storage::url($j->jd_file) }}" class="inline-flex items-center gap-1 text-sm text-[#0D2660] dark:text-[#E7C66A] hover:underline" target="_blank">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            عرض
                                        </a>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <form method="POST" action="{{ route('company.jobs.toggle',$j) }}">
                                        @csrf
                                        @method('PUT')
                                        <button class="px-3 py-1 text-xs font-medium rounded-lg transition-colors {{ $j->status==='open' ? 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400' : 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400' }}">
                                            {{ $j->status==='open' ? 'إيقاف' : 'نشر' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $j->status==='open' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400' }}">
                                        {{ $j->status === 'open' ? 'مفتوحة' : 'مغلقة' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $j->approved_by_admin ? 'bg-[#E7C66A]/20 text-[#0D2660] dark:text-[#E7C66A]' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                        {{ $j->approved_by_admin ? 'معتمدة' : 'بانتظار' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('company.jobs.show',$j) }}" class="p-2 rounded-lg text-gray-600 hover:text-[#0D2660] hover:bg-gray-100 dark:text-gray-400 dark:hover:text-[#E7C66A] dark:hover:bg-gray-700 transition-colors" title="تفاصيل">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <a href="{{ route('company.jobs.edit',$j) }}" class="p-2 rounded-lg text-gray-600 hover:text-[#0D2660] hover:bg-gray-100 dark:text-gray-400 dark:hover:text-[#E7C66A] dark:hover:bg-gray-700 transition-colors" title="تعديل">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('company.jobs.destroy',$j) }}" x-data="{open:false}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" @click="open=true" class="p-2 rounded-lg text-gray-600 hover:text-red-600 hover:bg-red-50 dark:text-gray-400 dark:hover:text-red-400 dark:hover:bg-red-900/30 transition-colors" title="حذف">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                            <x-confirm-modal title="تأكيد الحذف" message="هل أنت متأكد من حذف هذه الوظيفة؟ لا يمكن التراجع." />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        </div>
                                        <h4 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-1">لا توجد وظائف</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">ابدأ بإضافة وظيفة جديدة</p>
                                        <a href="{{ route('company.jobs.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#0D2660] text-white text-sm font-medium hover:bg-[#0D2660]/90 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                            إضافة وظيفة
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

