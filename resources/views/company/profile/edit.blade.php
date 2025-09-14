<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content">ملف الشركة</h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('company.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="flex items-center gap-4">
                        <div class="avatar">
                            <div class="w-20 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2 overflow-hidden">
                                @if(!empty($company->profile_image))
                                    <img src="{{ Storage::url($company->profile_image) }}" alt="Logo" />
                                @else
                                    <img src="https://api.dicebear.com/7.x/initials/svg?seed={{ urlencode($company->company_name ?? auth()->user()->name) }}" alt="Logo" />
                                @endif
                            </div>
                        </div>
                        <div class="flex-1">
                            <x-input-label for="profile_image" value="شعار/صورة الشركة (PNG/JPG/WebP، حتى 2MB)" />
                            <input id="profile_image" name="profile_image" type="file" class="mt-1 block w-full" accept="image/png,image/jpeg,image/webp" />
                            @error('profile_image')
                                <div class="text-error text-sm mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="text-right">
                        <button class="btn btn-primary">
                            حفظ التغييرات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

