<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">الشركات</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="p-3 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-200">{{ session('status') }}</div>
        @endif

        <div class="bg-gradient-to-br from-indigo-50 to-white dark:from-gray-900 dark:to-gray-800 p-4 rounded-xl shadow-sm">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 rounded-lg bg-white dark:bg-gray-800 shadow">
                    <div class="text-xs text-gray-500">إجمالي الشركات</div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $companies->count() }}</div>
                </div>
                <div class="p-4 rounded-lg bg-white dark:bg-gray-800 shadow">
                    <div class="text-xs text-gray-500">خطط فعالة</div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">—</div>
                </div>
                <div class="p-4 rounded-lg bg-white dark:bg-gray-800 shadow">
                    <div class="text-xs text-gray-500">بانتظار الموافقة</div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">—</div>
                </div>
                <div class="p-4 rounded-lg bg-white dark:bg-gray-800 shadow">
                    <div class="text-xs text-gray-500">موقوفة</div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">—</div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl shadow">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-start">#</th>
                        <th class="px-4 py-2 text-start">الشركة</th>
                        <th class="px-4 py-2 text-start">البريد الإلكتروني</th>
                        <th class="px-4 py-2 text-start">المحافظة</th>
                        <th class="px-4 py-2 text-start">الخطة</th>
                        <th class="px-4 py-2 text-start">انتهاء الاشتراك</th>
                        <th class="px-4 py-2 text-start">حالة</th>
                        <th class="px-4 py-2 text-start">إجراءات</th>
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
                                    <form method="POST" action="{{ route('admin.companies.destroy',$c) }}" x-data="{open:false}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" @click="open=true" class="px-3 py-1.5 rounded-lg bg-red-700 hover:bg-red-800 text-white text-xs">حذف الشركة</button>
                                        <x-confirm-modal title="تأكيد الحذف" message="سيتم حذف الشركة وجميع وظائفها. هل أنت متأكد؟" />
                                    </form>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

