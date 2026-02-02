<x-app-layout>
    <x-slot name="header">
        <div class="rounded-xl bg-gradient-to-br from-[#0D2660] via-[#102E66] to-[#0A1E46] text-white p-6">
            <h2 class="text-xl font-bold">ملف الشركة</h2>
            <p class="text-[#E7C66A] text-sm mt-1">إدارة معلومات وشعار الشركة</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="flex items-center gap-3 p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 mb-6">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-green-800 dark:text-green-400">{{ session('status') }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            {{-- Header --}}
            <div class="flex items-center gap-3 p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="w-10 h-10 rounded-lg bg-[#0D2660] flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#E7C66A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">معلومات الشركة</h3>
            </div>

            <form method="POST" action="{{ route('company.profile.update') }}" enctype="multipart/form-data" class="p-6">
                @csrf

                {{-- Company Logo Section --}}
                <div class="flex flex-col md:flex-row items-center gap-6 mb-8">
                    <div class="relative group">
                        <div class="w-28 h-28 rounded-xl overflow-hidden ring-4 ring-[#E7C66A]/30 shadow-lg">
                            @if(!empty($company->profile_image))
                                @php
                                    $imgPath = $company->profile_image;
                                    $base = \Illuminate\Support\Str::of($imgPath)->beforeLast('.');
                                    $sm = (string)$base . '_sm.webp';
                                    $md = (string)$base . '_md.webp';
                                    $lg = (string)$base . '_lg.webp';
                                    $srcsetArr = [];
                                    if (Storage::disk('public')->exists($sm)) { $srcsetArr[] = Storage::url($sm).' 160w'; }
                                    if (Storage::disk('public')->exists($md)) { $srcsetArr[] = Storage::url($md).' 320w'; }
                                    if (Storage::disk('public')->exists($lg)) { $srcsetArr[] = Storage::url($lg).' 640w'; }
                                    $srcset = implode(', ', $srcsetArr);
                                @endphp
                                <img src="{{ Storage::url($company->profile_image) }}" @if($srcset) srcset="{{ $srcset }}" sizes="112px" @endif alt="Logo" class="w-full h-full object-cover" />
                            @else
                                <img src="https://api.dicebear.com/7.x/initials/svg?seed={{ urlencode($company->company_name ?? auth()->user()->name) }}" alt="Logo" class="w-full h-full object-cover" />
                            @endif
                        </div>
                        <div class="absolute inset-0 rounded-xl bg-[#0D2660]/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer" onclick="document.getElementById('profile_image').click()">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                    </div>
                    <div class="flex-1 text-center md:text-right">
                        <h4 class="text-xl font-bold text-gray-800 dark:text-white mb-1">{{ $company->company_name ?? auth()->user()->name }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">شعار الشركة يظهر في صفحات الوظائف والإعلانات</p>
                    </div>
                </div>

                {{-- Upload Section --}}
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-6 text-center hover:border-[#E7C66A] transition-colors mb-6">
                    <input id="profile_image" name="profile_image" type="file" class="hidden" accept="image/png,image/jpeg,image/webp" onchange="updateFileName(this)" />
                    <label for="profile_image" class="cursor-pointer">
                        <div class="w-12 h-12 rounded-full bg-[#0D2660]/10 dark:bg-[#E7C66A]/10 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-[#0D2660] dark:text-[#E7C66A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        </div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">اضغط لرفع شعار جديد</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, WebP - حتى 2MB</p>
                        <p id="fileName" class="text-sm text-[#0D2660] dark:text-[#E7C66A] font-medium mt-2 hidden"></p>
                    </label>
                </div>
                @error('profile_image')
                    <div class="flex items-center gap-2 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-sm mb-6">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $message }}
                    </div>
                @enderror

                {{-- Actions --}}
                <div class="flex justify-end gap-3">
                    <a href="{{ route('dashboard') }}" class="px-6 py-3 rounded-xl border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        إلغاء
                    </a>
                    <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[#0D2660] to-[#1a3a7a] text-white font-medium shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5">
                        حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateFileName(input) {
            const fileName = document.getElementById('fileName');
            if (input.files && input.files[0]) {
                fileName.textContent = input.files[0].name;
                fileName.classList.remove('hidden');
            }
        }
    </script>
    @endpush
</x-app-layout>

