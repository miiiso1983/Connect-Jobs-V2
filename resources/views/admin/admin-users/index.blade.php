<x-app-layout>
    <x-slot name="header">
		        <div class="rounded-xl bg-gradient-to-br from-[#4A00B8] via-[#5A00E1] to-[#3C0094] text-white p-6">
            <h2 class="text-xl font-bold">إدارة المستخدمين الإداريين</h2>
	            <p class="text-[#38BDF8] text-sm mt-1">إضافة/تعديل/حذف الأدمن وتحديد الصلاحيات</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="p-3 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-200">{{ session('status') }}</div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 flex items-center justify-between">
            <div class="text-sm text-gray-600 dark:text-gray-300">
                ملاحظة: <span class="font-semibold">mustafa@teamiapps.com</span> هو الماستر أدمن ولا يمكن تعديله أو حذفه.
            </div>
            <a href="{{ route('admin.admin_users.create') }}" class="btn btn-primary btn-sm">إضافة أدمن جديد</a>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>المستخدم</th>
                        <th>الحالة</th>
                        <th>الصلاحيات</th>
                        <th class="text-left">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $a)
                        @php($isMaster = $a->isMasterAdmin())
                        <tr>
                            <td>
                                <div class="text-sm">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $a->name }}</div>
                                    <div class="text-gray-500">{{ $a->email }}</div>
                                </div>
                                @if($isMaster)
                                    <div class="mt-1"><span class="badge badge-warning badge-sm">ماستر أدمن</span></div>
                                @endif
                            </td>
                            <td>
	                                @php($st = $a->status ?? 'active')
	                                @php($stLabel = match($st){
	                                    'active' => 'نشط',
	                                    'inactive' => 'غير نشط',
	                                    'suspended' => 'موقوف',
	                                    default => $st,
	                                })
	                                <span class="badge {{ $st==='active' ? 'badge-success' : ($st==='suspended' ? 'badge-error' : 'badge-ghost') }} badge-sm">{{ $stLabel }}</span>
                            </td>
                            <td class="min-w-[320px]">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($labels as $key => $label)
                                        @php($allowed = $isMaster ? true : ($a->adminPermission?->allows($key) ?? false))
                                        <span class="badge {{ $allowed ? 'badge-info' : 'badge-ghost' }} badge-sm">{{ $label }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="whitespace-nowrap text-left">
                                @if(!$isMaster)
                                    <a href="{{ route('admin.admin_users.edit', $a) }}" class="btn btn-xs">تعديل</a>
                                    <form method="POST" action="{{ route('admin.admin_users.destroy', $a) }}" class="inline" onsubmit="return confirm('تأكيد حذف المستخدم الإداري؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-xs btn-error">حذف</button>
                                    </form>
                                @else
                                    <span class="text-gray-500 text-sm">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-gray-500">لا يوجد مستخدمون إداريون.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex justify-center">
            {{ $admins->links() }}
        </div>
    </div>
</x-app-layout>
