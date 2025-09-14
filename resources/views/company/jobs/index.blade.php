<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">وظائف الشركة</h2>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="alert alert-success shadow">{{ session('status') }}</div>
        @endif

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('company.jobs.create') }}" class="btn btn-primary">وظيفة جديدة</a>
            <a href="{{ route('company.applicants.index') }}" class="btn btn-secondary">فلترة المتقدمين</a>
        </div>

        <div class="overflow-x-auto card bg-base-100 shadow">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>العنوان</th>
                        <th>المحافظة</th>
                        <th>الوصف الوظيفي</th>
                        <th>نشر/إيقاف</th>
                        <th>الحالة</th>
                        <th>موافقة الأدمن</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($jobs as $j)
                        <tr>
                            <td class="px-4 py-2">{{ $j->id }}</td>
                            <td class="px-4 py-2">
                                <a href="{{ route('company.jobs.edit',$j) }}" class="text-primary hover:underline">{{ $j->title }}</a>
                            </td>
                            <td class="px-4 py-2">{{ $j->province }}</td>
                            <td class="px-4 py-2">
                                @if($j->jd_file)
                                    <a href="{{ Storage::url($j->jd_file) }}" class="text-primary" target="_blank">عرض</a>
                                @else - @endif
                            </td>
                            <td class="px-4 py-2">
                                <form method="POST" action="{{ route('company.jobs.toggle',$j) }}">
                                    @csrf
                                    @method('PUT')
                                    <button class="btn btn-xs {{ $j->status==='open' ? 'btn-error' : 'btn-success' }}">
                                        {{ $j->status==='open' ? 'إيقاف' : 'نشر' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-2"><span class="badge {{ $j->status==='open' ? 'badge-success':'badge-ghost' }}">{{ $j->status }}</span></td>
                            <td class="px-4 py-2">
                                <span class="badge {{ $j->approved_by_admin ? 'badge-primary' : 'badge-ghost' }}">{{ $j->approved_by_admin ? 'مقبول' : 'بانتظار' }}</span>
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('company.jobs.show',$j) }}" class="btn btn-xs btn-ghost">تفاصيل</a>
                                    <a href="{{ route('company.jobs.edit',$j) }}" class="btn btn-xs btn-primary">تعديل</a>
                                    <form method="POST" action="{{ route('company.jobs.destroy',$j) }}" x-data="{open:false}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" @click="open=true" class="btn btn-xs btn-error">حذف</button>
                                        <x-confirm-modal title="تأكيد الحذف" message="هل أنت متأكد من حذف هذه الوظيفة؟ لا يمكن التراجع." />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

