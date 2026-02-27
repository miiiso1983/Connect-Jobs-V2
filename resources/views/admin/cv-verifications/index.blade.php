<x-app-layout>
    <x-slot name="header">
        <div class="rounded-xl bg-gradient-to-br from-[#0D2660] via-[#102E66] to-[#0A1E46] text-white p-6">
            <h2 class="text-xl font-bold">طلبات توثيق السيرة الذاتية</h2>
            <p class="text-[#E7C66A] text-sm mt-1">مراجعة طلبات توثيق CV (للصيادلة)</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="p-3 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-200">{{ session('status') }}</div>
        @endif

        <form method="GET" class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow grid grid-cols-1 md:grid-cols-6 gap-3">
            <div class="md:col-span-3">
                <x-input-label for="q" value="بحث (اسم، بريد، مسمى)" />
                <input type="text" id="q" name="q" value="{{ $q ?? '' }}" class="input input-bordered w-full" />
            </div>
            <div>
                <x-input-label for="status" value="الحالة" />
                <select id="status" name="status" class="select select-bordered w-full">
                    @foreach(['pending'=>'قيد المراجعة','approved'=>'مقبول','rejected'=>'مرفوض','all'=>'الكل'] as $k=>$v)
                        <option value="{{ $k }}" @selected(($status ?? 'pending')===$k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2 flex gap-2 items-end">
                <button class="btn bg-[#0D2660] hover:bg-[#0a1d4d] text-white border-none">تطبيق</button>
                <a href="{{ route('admin.cv_verifications.index') }}" class="btn btn-ghost">تفريغ</a>
            </div>
        </form>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>الباحث</th>
                        <th>المسمى</th>
                        <th>الحالة</th>
                        <th>CV</th>
                        <th>أُنشئ</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $r)
                        <tr>
                            <td>
                                <div class="text-sm">
                                    <div class="font-semibold">{{ $r->jobSeeker->full_name ?? $r->jobSeeker->user->name ?? '—' }}</div>
                                    <div class="text-gray-500">{{ $r->jobSeeker->user->email ?? '—' }}</div>
                                </div>
                            </td>
                            <td>{{ $r->jobSeeker->job_title ?? '—' }}</td>
                            <td>
                                @php($st = $r->status ?? 'pending')
                                <span class="badge {{ $st==='approved' ? 'badge-success' : ($st==='rejected' ? 'badge-error' : 'badge-ghost') }} badge-sm">{{ $st }}</span>
                            </td>
                            <td>
                                @if(!empty($r->cv_file))
                                    <a href="{{ Storage::url($r->cv_file) }}" target="_blank" class="btn btn-xs">عرض</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $r->created_at?->toDateString() ?? '—' }}</td>
                            <td class="min-w-[320px]">
                                @if(($r->status ?? null) === \App\Models\CvVerificationRequest::STATUS_PENDING)
                                    <div class="grid grid-cols-1 gap-2">
                                        <form method="POST" action="{{ route('admin.cv_verifications.approve', $r) }}" class="flex flex-col gap-2">
                                            @csrf
                                            @method('PUT')
                                            <textarea name="admin_notes" rows="2" class="textarea textarea-bordered w-full" placeholder="ملاحظة (اختياري)"></textarea>
                                            <button class="btn btn-sm btn-success">تأكيد التوثيق</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.cv_verifications.reject', $r) }}" class="flex flex-col gap-2">
                                            @csrf
                                            @method('PUT')
                                            <textarea name="admin_notes" rows="2" class="textarea textarea-bordered w-full" placeholder="سبب الرفض (إجباري)" required></textarea>
                                            <button class="btn btn-sm btn-error">رفض الطلب</button>
                                        </form>
                                    </div>
                                @else
                                    <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
                                        <div>اتخذ القرار بواسطة: {{ $r->adminUser->name ?? '—' }}</div>
                                        <div>التاريخ: {{ $r->decided_at?->toDateTimeString() ?? '—' }}</div>
                                        @if(!empty($r->admin_notes))
                                            <div>ملاحظات: {{ $r->admin_notes }}</div>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-gray-500">لا توجد طلبات.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3">
            {{ $requests->links() }}
        </div>
    </div>
</x-app-layout>
