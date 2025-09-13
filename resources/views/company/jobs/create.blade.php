<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">إنشاء وظيفة جديدة</h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 space-y-6">
        @if (session('status'))
            <div class="alert alert-success shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <div class="card bg-base-100 shadow-lg">
            <div class="card-header">
                <h3 class="card-title text-xl font-bold p-6 pb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 ml-2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    إنشاء وظيفة جديدة
                </h3>
                <p class="text-gray-600 px-6 pb-4">املأ البيانات التالية لإنشاء وظيفة جديدة</p>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('company.jobs.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
            <div>
                <x-input-label for="title" value="عنوان الوظيفة" />
                <input list="job_titles" id="title" name="title" value="{{ old('title') }}" class="input input-bordered w-full" placeholder="أدخل عنوان الوظيفة" />
                <datalist id="job_titles">
                    @foreach ($titles as $t)
                        <option value="{{ $t }}" />
                    @endforeach
                </datalist>
                <x-input-error :messages="$errors->get('title')" class="mt-1" />
            </div>
                <div>
                    <x-input-label for="specialities" value="التخصصات (اختر أكثر من واحد)" />
                    <select id="specialities" name="specialities[]" multiple size="5" class="select select-bordered w-full">
                        @foreach ($specialities as $s)
                            <option value="{{ $s }}" @selected(collect(old('specialities', []))->contains($s))>{{ $s }}</option>
                        @endforeach
                    </select>
                    <div class="text-xs text-gray-500 mt-1">يمكنك اختيار أكثر من تخصص بالضغط على Ctrl/⌘ أثناء التحديد.</div>
                    <x-input-error :messages="$errors->get('specialities')" class="mt-1" />
                </div>

            <div x-data="districtPicker()" x-init="init('{{ old('province') }}', @js(old('districts', [])))">
                <x-input-label for="province" value="المحافظة" />
                <select id="province" name="province" @change="load()" class="select select-bordered w-full">
                    <option value="">اختر المحافظة</option>
                    @foreach ($provinces as $p)
                        <option value="{{ $p }}" @selected(old('province')===$p)>{{ $p }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('province')" class="mt-1" />
                <div class="mt-3">
                    <x-input-label value="المناطق" />
                    <div class="mt-2 border border-base-300 rounded-lg bg-base-50 max-h-48 overflow-y-auto relative">
                        <!-- Loading State -->
                        <div x-show="loading" class="p-4 text-center">
                            <div class="loading loading-spinner loading-sm text-primary"></div>
                            <span class="text-sm text-gray-500 mr-2">جاري تحميل المناطق...</span>
                        </div>

                        <!-- Empty State -->
                        <div x-show="!loading && !districts.length && !selected.length" class="p-4 text-center text-gray-500 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            اختر محافظة أولاً لعرض المناطق
                        </div>

                        <!-- Districts List -->
                        <div x-show="!loading && districts.length" class="p-3 space-y-1">
                            <template x-for="district in districts" :key="district">
                                <label class="flex items-center gap-3 p-2 hover:bg-base-100 rounded-md cursor-pointer transition-all duration-200 group">
                                    <input
                                        type="checkbox"
                                        :value="district"
                                        name="districts[]"
                                        x-model="selected"
                                        class="checkbox checkbox-sm checkbox-primary"
                                    >
                                    <span x-text="district" class="text-sm flex-1 group-hover:text-primary transition-colors"></span>
                                    <svg x-show="selected.includes(district)" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </label>
                            </template>
                        </div>
                    </div>

                    <!-- Selected Districts Tags -->
                    <div x-show="selected.length" class="mt-3">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs font-medium text-gray-600">المناطق المختارة:</span>
                            <span class="badge badge-ghost badge-xs" x-text="selected.length"></span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="s in selected" :key="s">
                                <span class="badge badge-primary badge-sm gap-1 hover:badge-error transition-colors group">
                                    <span x-text="s"></span>
                                    <button type="button" @click="removeDistrict(s)" class="hover:scale-110 transition-transform" title="إزالة">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </span>
                            </template>
                            <button type="button" @click="selected = []" x-show="selected.length > 1" class="badge badge-ghost badge-sm hover:badge-error transition-colors" title="مسح الكل">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                مسح الكل
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <x-input-label for="requirements" value="متطلبات الوظيفة" />
                <textarea id="requirements" name="requirements" class="textarea textarea-bordered w-full" rows="3" placeholder="اكتب متطلبات الوظيفة هنا...">{{ old('requirements') }}</textarea>
                <x-input-error :messages="$errors->get('requirements')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="description" value="وصف الوظيفة" />
                <textarea id="description" name="description" class="textarea textarea-bordered w-full" rows="5" placeholder="اكتب وصف مفصل للوظيفة...">{{ old('description') }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-1" />
            </div>
            <div>
@push('scripts')
<script>
function districtPicker(){
  return {
    districts: [],
    selected: [],
    loading: false,

    init(initialProvince, initialSelected){
        this.selected = Array.isArray(initialSelected) ? [...initialSelected] : [];
        if(initialProvince){
            this.load();
        }
    },

    async load(){
        const prov = document.getElementById('province').value;
        if(!prov){
            this.districts = [];
            this.selected = [];
            return;
        }

        this.loading = true;
        try {
            const response = await fetch(`/districts?province=${encodeURIComponent(prov)}`);
            const list = await response.json();
            this.districts = Array.isArray(list) ? list : [];

            // Keep only selected districts that exist in the new province
            this.selected = this.selected.filter(s => this.districts.includes(s));
        } catch (error) {
            console.error('Error loading districts:', error);
            this.districts = [];
        } finally {
            this.loading = false;
        }
    },

    removeDistrict(district) {
        this.selected = this.selected.filter(s => s !== district);
    },

    toggleDistrict(district) {
        if (this.selected.includes(district)) {
            this.removeDistrict(district);
        } else {
            this.selected.push(district);
        }
    }
  }
}
</script>
@endpush
                <x-input-label for="jd_file" value="ملف وصف الوظيفة (PDF/Word)" />
                <input id="jd_file" name="jd_file" type="file" class="file-input file-input-bordered w-full mt-1" accept=".pdf,.doc,.docx" />
                <div class="text-xs text-gray-500 mt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    يمكنك رفع ملف PDF أو Word يحتوي على وصف مفصل للوظيفة
                </div>
                <x-input-error :messages="$errors->get('jd_file')" class="mt-1" />
            </div>
            <div class="flex gap-3 pt-4">
                <button type="submit" class="btn btn-primary flex-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    إنشاء الوظيفة
                </button>
                <a href="{{ route('company.jobs.index') }}" class="btn btn-ghost">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    إلغاء
                </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

