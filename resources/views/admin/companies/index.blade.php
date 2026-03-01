<x-app-layout>
    <x-slot name="header">
		        <div class="rounded-xl bg-gradient-to-br from-[#4A00B8] via-[#5A00E1] to-[#3C0094] text-white p-6">
            <h2 class="text-xl font-bold">إدارة الشركات</h2>
	            <p class="text-[#38BDF8] text-sm mt-1">عرض وإدارة جميع الشركات المسجلة في النظام</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-green-800 dark:text-green-400">{{ session('status') }}</span>
            </div>
        @endif

        {{-- KPIs --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
		                <div class="w-10 h-10 rounded-lg bg-[#4A00B8] flex items-center justify-center">
	                    <svg class="w-5 h-5 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">إحصائيات الشركات</h3>
            </div>
	            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
		                <div class="text-center p-4 rounded-lg bg-gradient-to-br from-[#4A00B8]/5 to-[#4A00B8]/10 dark:from-[#4A00B8]/20 dark:to-[#4A00B8]/30">
		                    <div class="text-3xl font-bold text-[#4A00B8] dark:text-[#38BDF8]">{{ $companies->count() }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">إجمالي الشركات</div>
                </div>
                <div class="text-center p-4 rounded-lg bg-gradient-to-br from-emerald-500/5 to-emerald-500/10 dark:from-emerald-500/20 dark:to-emerald-500/30">
                    <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">—</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">خطط فعالة</div>
                </div>
                <div class="text-center p-4 rounded-lg bg-gradient-to-br from-amber-500/5 to-amber-500/10 dark:from-amber-500/20 dark:to-amber-500/30">
                    <div class="text-3xl font-bold text-amber-600 dark:text-amber-400">—</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">بانتظار الموافقة</div>
                </div>
                <div class="text-center p-4 rounded-lg bg-gradient-to-br from-red-500/5 to-red-500/10 dark:from-red-500/20 dark:to-red-500/30">
                    <div class="text-3xl font-bold text-red-600 dark:text-red-400">—</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">موقوفة</div>
                </div>
            </div>
        </div>

        {{-- Companies Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            <div class="flex items-center gap-3 p-6 border-b border-gray-200 dark:border-gray-700">
	                <div class="w-10 h-10 rounded-lg bg-[#38BDF8] flex items-center justify-center">
		                    <svg class="w-5 h-5 text-[#3C0094]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">قائمة الشركات</h3>
		                <span class="bg-[#4A00B8] text-white text-xs font-bold px-3 py-1 rounded-full">{{ $companies->count() }} شركة</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الشركة</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">البريد الإلكتروني</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">المحافظة</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">الخطة</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">انتهاء الاشتراك</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">حالة</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">إجراءات</th>
                        </tr>
                    </thead>
                <tbody>
                    @foreach ($companies as $c)
                        <tr class="border-t border-gray-100 dark:border-gray-700">
                            <td class="px-4 py-2">{{ $c->id }}</td>
                            <td class="px-4 py-2">{{ $c->company_name }}</td>
                            <td class="px-4 py-2">
                                @if($c->user && $c->user->email)
                                    <a href="mailto:{{ $c->user->email }}" class="text-primary hover:underline">{{ $c->user->email }}</a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">{{ $c->province }}</td>
                            <td class="px-4 py-2">{{ $c->subscription_plan }}</td>
                            <td class="px-4 py-2">
                                @php($expAt = $c->subscription_expires_at ?? ($c->subscription_expiry ? \Carbon\Carbon::parse($c->subscription_expiry)->endOfDay() : null))
                                @if($expAt)
                                    <div class="flex items-center gap-2">
                                        <span>{{ $expAt->toDateString() }}</span>
                                        <span class="text-xs text-gray-500">({{ $expAt->isPast() ? 'منتهي' : 'متبقّي '.now()->diffInDays($expAt).' يوم' }})</span>
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                @php($statusColor = 'bg-green-100 text-green-800')
                                @if(isset($expAt) && $expAt)
                                    @if($expAt->isPast())
                                        @php($statusColor = 'bg-red-100 text-red-800')
                                    @elseif(now()->diffInDays($expAt) <= 10)
                                        @php($statusColor = 'bg-amber-100 text-amber-800')
                                    @endif
                                @endif
                                <span class="px-2 py-1 rounded-full text-xs {{ $statusColor }}">{{ isset($expAt) && $expAt && $expAt->isPast() ? 'منتهي' : 'فعال' }}</span>
                            </td>
                            <td class="px-4 py-2 flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('admin.companies.approve',$c) }}">
                                    @csrf
                                    <button class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs">موافقة</button>
                                </form>
                                <form method="POST" action="{{ route('admin.companies.subscription',$c) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <select name="subscription_plan" class="rounded-lg text-xs border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                                        @foreach (['free','basic','pro','enterprise'] as $plan)
                                            <option value="{{ $plan }}" @selected($c->subscription_plan==$plan)>{{ $plan }}</option>
                                        @endforeach
                                    </select>
                                    <input type="date" name="subscription_expiry" value="{{ $c->subscription_expiry }}" class="rounded-lg text-xs border-gray-300 dark:bg-gray-800 dark:border-gray-700" title="تاريخ الانتهاء (سيتم اعتباره نهاية اليوم)">
                                    <input type="datetime-local" name="subscription_expires_at" value="{{ $c->subscription_expires_at ? $c->subscription_expires_at->format('Y-m-d\\TH:i') : '' }}" class="rounded-lg text-xs border-gray-300 dark:bg-gray-800 dark:border-gray-700" title="تاريخ ووقت الانتهاء">
                                    <button class="px-3 py-1.5 rounded-lg bg-gray-700 hover:bg-gray-800 text-white text-xs">تحديث</button>
                                </form>
                                <form method="POST" action="{{ route('admin.companies.destroy',$c) }}" x-data="{open:false}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" @click="open=true" class="px-3 py-1.5 rounded-lg bg-red-700 hover:bg-red-800 text-white text-xs">حذف الشركة</button>
                                    <x-confirm-modal title="تأكيد الحذف" message="سيتم حذف الشركة وجميع وظائفها. هل أنت متأكد؟" />
                                </form>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

