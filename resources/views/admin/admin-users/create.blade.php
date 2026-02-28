<x-app-layout>
    <x-slot name="header">
	        <div class="rounded-xl bg-gradient-to-br from-[#5B21B6] via-[#6D28D9] to-[#4C1D95] text-white p-6">
            <h2 class="text-xl font-bold">إضافة مستخدم إداري</h2>
	            <p class="text-[#38BDF8] text-sm mt-1">إنشاء أدمن جديد وتحديد الصلاحيات</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if ($errors->any())
            <div class="p-3 rounded-lg bg-rose-100 text-rose-800 border border-rose-200">
                <div class="font-semibold mb-1">يرجى تصحيح الأخطاء التالية:</div>
                <ul class="list-disc ms-6 text-sm">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.admin_users.store') }}" class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="name" value="الاسم" />
                    <input id="name" name="name" type="text" value="{{ old('name') }}" class="input input-bordered w-full" required />
                </div>
                <div>
                    <x-input-label for="email" value="البريد الإلكتروني" />
                    <input id="email" name="email" type="email" value="{{ old('email') }}" class="input input-bordered w-full" required />
                </div>
                <div>
                    <x-input-label for="password" value="كلمة المرور" />
                    <input id="password" name="password" type="password" class="input input-bordered w-full" required />
                </div>
                <div>
                    <x-input-label for="password_confirmation" value="تأكيد كلمة المرور" />
                    <input id="password_confirmation" name="password_confirmation" type="password" class="input input-bordered w-full" required />
                </div>
                <div>
                    <x-input-label for="status" value="الحالة" />
                    <select id="status" name="status" class="select select-bordered w-full" required>
                        @foreach(['active'=>'نشط','inactive'=>'غير نشط','suspended'=>'موقوف'] as $k=>$v)
                            <option value="{{ $k }}" @selected(old('status','active')===$k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="divider">الصلاحيات</div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($labels as $key => $label)
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700">
                        <input type="checkbox" class="checkbox" name="permissions[{{ $key }}]" value="1" @checked((bool) data_get(old('permissions', []), $key)) />
                        <span class="text-sm font-medium">{{ $label }}</span>
                    </label>
                @endforeach
            </div>

            <div class="flex items-center gap-3">
                <button class="btn btn-primary">حفظ</button>
                <a href="{{ route('admin.admin_users.index') }}" class="btn btn-ghost">إلغاء</a>
            </div>
        </form>
    </div>
</x-app-layout>
