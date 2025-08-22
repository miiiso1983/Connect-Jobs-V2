<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">بروفايل المتقدم</h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
        @endif
        <form method="POST" action="{{ route('jobseeker.profile.update') }}" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 p-6 rounded shadow grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf

            <div>
                <x-input-label for="full_name" value="الاسم الكامل" />
                <x-text-input id="full_name" name="full_name" class="block mt-1 w-full" value="{{ old('full_name', $js->full_name ?? '') }}" />
            </div>
            <div x-data="districtPicker()" x-init="init('{{ old('province', $js->province ?? '') }}', @js(old('districts', $js->districts ?? [])))">
                <x-input-label for="province" value="المحافظة" />
                <select id="province" name="province" @change="load()" class="block mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                    <option value="">—</option>
                    @foreach($provinces as $p)
                        <option value="{{ $p }}" @selected(old('province', $js->province ?? '')===$p)>{{ $p }}</option>
                    @endforeach
                </select>
                <div class="mt-3">
                    <x-input-label value="المناطق" />
                    <div class="mt-2 max-h-48 overflow-y-auto rounded border border-gray-200 dark:border-gray-700 p-3 space-y-2">
                        <template x-for="d in districts" :key="d">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" :value="d" name="districts[]" x-model="selected" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span x-text="d" class="text-sm text-gray-700 dark:text-gray-200"></span>
                            </label>
                        </template>
                        <template x-if="!districts.length">
                            <div class="text-sm text-gray-500">اختر محافظة لعرض المناطق</div>
                        </template>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-2" x-show="selected.length">
                        <template x-for="s in selected" :key="s">
                            <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-xs" x-text="s"></span>
                        </template>
                    </div>
                </div>
            </div>
            <div>
                <x-input-label for="job_title" value="المسمى الوظيفي" />
                <select id="job_title" name="job_title" class="block mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                    <option value="">—</option>
                    @foreach(($titles->sort() ?? collect()) as $t)
                        <option value="{{ $t }}" @selected(old('job_title', $js->job_title ?? '')===$t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <div x-data="{selected: @js(old('specialities', $js->specialities ?? [])), options: @js($specialities->sort()->values())}">
                <x-input-label value="التخصصات" />
                <div class="mt-2 max-h-48 overflow-y-auto rounded border border-gray-200 dark:border-gray-700 p-3 space-y-2">
                    <template x-for="s in options" :key="s">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" :value="s" name="specialities[]" x-model="selected" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span x-text="s" class="text-sm text-gray-700 dark:text-gray-200"></span>
                        </label>
                    </template>
                    <template x-if="!options.length">
                        <div class="text-sm text-gray-500">لا توجد تخصصات</div>
                    </template>
                </div>
                <div class="mt-2 flex flex-wrap gap-2" x-show="selected.length">
                    <template x-for="s in selected" :key="s">
                        <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-xs" x-text="s"></span>
                    </template>
                </div>
            </div>
            <div>
                <x-input-label for="speciality" value="التخصص" />
                <input list="specialities" id="speciality" name="speciality" class="block mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700" value="{{ old('speciality', $js->speciality ?? '') }}" />
                <datalist id="specialities">@foreach($specialities as $s)<option value="{{ $s }}" />@endforeach</datalist>
            </div>
            <div>
                <x-input-label for="gender" value="الجنس" />
                <select id="gender" name="gender" class="block mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                    <option value="">--</option>
                    <option value="male" @selected(($js->gender ?? '')==='male')>Male</option>
                    <option value="female" @selected(($js->gender ?? '')==='female')>Female</option>

                </select>
            </div>
            <div>
                <x-input-label for="own_car" value="امتلاك سيارة" />
                <select id="own_car" name="own_car" class="block mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                    <option value="0" @selected(($js->own_car ?? 0)==0)>No</option>
                    <option value="1" @selected(($js->own_car ?? 0)==1)>Yes</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <x-input-label for="summary" value="الملخص" />
                <textarea id="summary" name="summary" rows="3" class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700">{{ old('summary', $js->summary ?? '') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <x-input-label for="qualifications" value="المؤهلات" />
                <textarea id="qualifications" name="qualifications" rows="3" class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700">{{ old('qualifications', $js->qualifications ?? '') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <x-input-label for="experiences" value="الخبرات" />
                <textarea id="experiences" name="experiences" rows="3" class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700">{{ old('experiences', $js->experiences ?? '') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <x-input-label for="languages" value="اللغات" />
                <textarea id="languages" name="languages" rows="2" class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700">{{ old('languages', $js->languages ?? '') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <x-input-label for="skills" value="المهارات" />
                <textarea id="skills" name="skills" rows="2" class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700" placeholder="مثال: excel, sales, pediatrics">{{ old('skills', $js->skills ?? '') }}</textarea>
            </div>
@push('scripts')
<script>
function districtPicker(){
  return {
    districts: [],
    selected: [],
    init(initialProvince, initialSelected){ this.selected = initialSelected || []; if(initialProvince){ this.load(initialProvince); } },
    load(){ const prov = document.getElementById('province').value; if(!prov){ this.districts=[]; this.selected=[]; return; }
      fetch(`/districts?province=${encodeURIComponent(prov)}`).then(r=>r.json()).then(list=>{ this.districts=list; });
    }
  }
}
</script>
@endpush
            <div class="md:col-span-2">
                <x-input-label for="cv" value="السيرة الذاتية (PDF/Word)" />
                <input id="cv" name="cv" type="file" class="mt-1 block w-full" accept=".pdf,.doc,.docx" />
                @if(!empty($js->cv_file))
                    <p class="text-sm mt-1">الملف الحالي: <a href="{{ Storage::url($js->cv_file) }}" class="text-indigo-600" target="_blank">عرض</a></p>
                @endif
            </div>

            <div class="md:col-span-2">
                <x-primary-button>حفظ</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>

