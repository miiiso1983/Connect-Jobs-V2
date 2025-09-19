<x-app-layout>
    <x-slot name="header">
        <div class="rounded-xl bg-gradient-to-br from-[#0D2660] via-[#102E66] to-[#0A1E46] text-white p-6">
            <h2 class="text-xl font-bold">إدارة الباحثين عن عمل</h2>
            <p class="text-[#E7C66A] text-sm mt-1">فلترة، عرض، تغيير الحالة، حذف</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="alert alert-success shadow">{{ session('status') }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="stats bg-white shadow">
                <div class="stat">
                    <div class="stat-title">إجمالي الباحثين</div>
                    <div class="stat-value text-primary">{{ $totalSeekers }}</div>
                </div>
            </div>
            <div class="stats bg-white shadow">
                <div class="stat">
                    <div class="stat-title">نشط</div>
                    <div class="stat-value text-emerald-600">{{ $activeUsers }}</div>
                </div>
            </div>
            <div class="stats bg-white shadow">
                <div class="stat">
                    <div class="stat-title">موقوف</div>
                    <div class="stat-value text-amber-600">{{ $suspendedUsers }}</div>
                </div>
            </div>
        </div>

        <form method="GET" class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow grid grid-cols-1 md:grid-cols-6 gap-3">
            <div class="md:col-span-2">
                <x-input-label for="q" value="بحث (اسم، بريد، مسمى، تخصص)" />
                <input type="text" id="q" name="q" value="{{ $q }}" class="input input-bordered w-full" />
            </div>
            <div>
                <x-input-label for="province" value="المحافظة" />
                <select id="province" name="province" class="select select-bordered w-full">
                    <option value="">—</option>
                    @foreach($provinces as $p)
                        <option value="{{ $p }}" @selected($province===$p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="status" value="الحالة" />
                <select id="status" name="status" class="select select-bordered w-full">
                    <option value="">—</option>
                    <option value="active" @selected($status==='active')>نشط</option>
                    <option value="suspended" @selected($status==='suspended')>موقوف</option>
                </select>
            </div>
            <div>
                <x-input-label for="per_page" value="عدد النتائج/صفحة" />
                <select id="per_page" name="per_page" class="select select-bordered w-full">
                    @foreach([10,20,50,100,200] as $pp)
                        <option value="{{ $pp }}" @selected($perPage==$pp)>{{ $pp }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="created_from" value="إنشاء من" />
                <input type="date" id="created_from" name="created_from" value="{{ $createdFrom }}" class="input input-bordered w-full" />
            </div>
            <div>
                <x-input-label for="created_to" value="إنشاء إلى" />
                <input type="date" id="created_to" name="created_to" value="{{ $createdTo }}" class="input input-bordered w-full" />
            </div>
            <div>
                <x-input-label for="last_seen_from" value="آخر دخول من" />
                <input type="date" id="last_seen_from" name="last_seen_from" value="{{ $lastSeenFrom }}" class="input input-bordered w-full" />
            </div>
            <div>
                <x-input-label for="last_seen_to" value="آخر دخول إلى" />
                <input type="date" id="last_seen_to" name="last_seen_to" value="{{ $lastSeenTo }}" class="input input-bordered w-full" />
            </div>
            <div>
                <x-input-label for="profile_completed" value="أكمل الملف؟" />
                <select id="profile_completed" name="profile_completed" class="select select-bordered w-full">
                    <option value="">—</option>
                    <option value="1" @selected(($profileCompleted ?? '')==='1')>نعم</option>
                    <option value="0" @selected(($profileCompleted ?? '')==='0')>لا</option>
                </select>
            </div>
            <div>
                <x-input-label for="has_cv" value="لديه سيرة ذاتية؟" />
                <select id="has_cv" name="has_cv" class="select select-bordered w-full">
                    <option value="">—</option>
                    <option value="1" @selected(($hasCv ?? '')==='1')>نعم</option>
                    <option value="0" @selected(($hasCv ?? '')==='0')>لا</option>
                </select>
            </div>
            <div class="md:col-span-6 flex gap-2">
                <x-primary-button>تطبيق</x-primary-button>
                <a href="{{ route('admin.jobseekers.index') }}" class="btn">تفريغ</a>
            </div>
        </form>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>المستخدم</th>
                        <th>الاسم الكامل</th>
                        <th>المحافظة</th>
                        <th>المسمى</th>
                        <th>الحالة</th>
                        <th>أُنشئ</th>
                        <th>آخر ظهور</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($seekers as $s)
                        <tr>
                            <td class="whitespace-nowrap">
                                <div class="text-sm">
                                    <div class="font-semibold">{{ $s->user->name ?? '—' }}</div>
                                    <div class="text-gray-500">{{ $s->user->email ?? '—' }}</div>
                                </div>
                            </td>
                            <td>{{ $s->full_name ?? '—' }}</td>
                            <td>{{ $s->province ?? '—' }}</td>
                            <td>{{ $s->job_title ?? '—' }}</td>
                            <td>
                                <span class="badge {{ ($s->user->status ?? 'active')==='active' ? 'badge-success' : 'badge-ghost' }}">
                                    {{ $s->user->status ?? 'active' }}
                                </span>
                            </td>
                            <td>{{ $s->user->created_at ?? '—' }}</td>
                            @php($ts = $lastSeenTs[$s->user_id] ?? null)
                            <td>{{ $ts ? \Carbon\Carbon::createFromTimestamp($ts)->toDateTimeString() : '—' }}</td>
                            <td class="whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.jobseekers.toggle', $s->user) }}" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <button class="btn btn-xs">{{ ($s->user->status ?? 'active')==='active' ? 'إيقاف' : 'تنشيط' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.jobseekers.destroy', $s->user) }}" class="inline" onsubmit="return confirm('تأكيد الحذف؟ لا يمكن التراجع.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-xs btn-error">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-gray-500">لا نتائج.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $seekers->links() }}
        </div>
    </div>
</x-app-layout>

