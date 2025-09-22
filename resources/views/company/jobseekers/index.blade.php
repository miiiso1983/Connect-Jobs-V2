<x-app-layout>
    <x-slot name="header">
        <div class="rounded-xl bg-gradient-to-br from-[#0D2660] via-[#102E66] to-[#0A1E46] text-white p-6">
            <h2 class="text-xl font-bold">قاعدة بيانات الباحثين عن عمل</h2>
            <p class="text-[#E7C66A] text-sm mt-1">استعرض وفلتر جميع الباحثين — متاح للشركات والإدمن</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <form method="GET" class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow grid grid-cols-1 md:grid-cols-6 gap-3" x-data x-on:change.debounce.400ms="
            const f = $el.closest('form');
            const url = new URL(f.action || window.location.href);
            const fd = new FormData(f);
            const params = new URLSearchParams(fd);
            fetch(url.pathname + '?' + params.toString(), { headers: { 'X-Requested-With':'XMLHttpRequest' } })
              .then(r => r.text())
              .then(html => { document.getElementById('results').innerHTML = html; window.history.replaceState({}, '', url.pathname + '?' + params.toString()); })
              .catch(console.error);
        ">
            <div class="md:col-span-2">
                <x-input-label for="q" value="بحث (اسم، بريد، مسمى، تخصص، مهارة)" />
                <input type="text" id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="input input-bordered w-full" />
            </div>
            <div>
                <x-input-label for="province" value="المحافظة" />
                <select id="province" name="province" class="select select-bordered w-full">
                    <option value="">—</option>
                    @foreach($provinces->sort() as $p)
                        <option value="{{ $p }}" @selected(($filters['province'] ?? '')===$p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div x-data="districtPicker()" x-init="init('{{ $filters['province'] ?? '' }}', @js(request()->input('districts', [])))" class="md:col-span-2">
                <x-input-label value="المناطق" />
                <div class="mt-2 border border-base-300 rounded-lg bg-base-50 max-h-48 overflow-y-auto relative">
                    <div class="p-3" x-show="loading">
                        <div class="loading loading-spinner loading-sm text-primary"></div>
                        <span class="text-sm text-gray-500 mr-2">جاري تحميل المناطق...</span>
                    </div>
                    <div x-show="!loading && !districts.length && !selected.length" class="p-4 text-center text-gray-500 text-sm">اختر محافظة أولاً لعرض المناطق</div>
                    <div x-show="!loading && districts.length" class="p-3 space-y-1">
                        <template x-for="district in districts" :key="district">
                            <label class="flex items-center gap-3 p-2 hover:bg-base-100 rounded-md cursor-pointer">
                                <input type="checkbox" :value="district" name="districts[]" x-model="selected" class="checkbox checkbox-sm checkbox-primary">
                                <span x-text="district" class="text-sm flex-1"></span>
                                <svg x-show="selected.includes(district)" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            </label>
                        </template>
                    </div>
                </div>
                <div x-show="selected.length" class="mt-2 flex flex-wrap gap-2">
                    <template x-for="s in selected" :key="s"><span class="badge badge-primary badge-sm"> <span x-text="s"></span> </span></template>
                </div>
            </div>
            <div>
                <x-input-label value="التخصصات" />
                <div class="mt-2 max-h-48 overflow-y-auto rounded border border-base-300 p-3 space-y-2 bg-base-50">
                    @foreach($specialities->sort() as $s)
                        <label class="flex items-center gap-2 cursor-pointer hover:bg-base-100 p-1 rounded">
                            <input type="checkbox" value="{{ $s }}" name="specialities[]" @checked(in_array($s, request()->input('specialities', []))) class="checkbox checkbox-sm checkbox-primary">
                            <span class="text-sm">{{ $s }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div>
                <x-input-label for="gender" value="الجنس" />
                <select id="gender" name="gender" class="select select-bordered w-full">
                    <option value="">الكل</option>
                    <option value="male" @selected(($filters['gender'] ?? '')==='male')>ذكر</option>
                    <option value="female" @selected(($filters['gender'] ?? '')==='female')>أنثى</option>
                </select>
            </div>
            <div>
                <x-input-label for="own_car" value="سيارة" />
                <select id="own_car" name="own_car" class="select select-bordered w-full">
                    <option value="">—</option>
                    <option value="1" @selected(($filters['own_car'] ?? '')==='1')>نعم</option>
                    <option value="0" @selected(($filters['own_car'] ?? '')==='0')>لا</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <x-input-label for="skills" value="مهارات (كلمات مفصولة بفواصل)" />
                <input type="text" id="skills" name="skills" value="{{ $filters['skills'] ?? '' }}" class="input input-bordered w-full" />
            </div>
            @if($hasEducation)
                <div>
                    <x-input-label for="education_level" value="المؤهل العلمي" />
                    <select id="education_level" name="education_level" class="select select-bordered w-full">
                        <option value="">الكل</option>
                        @foreach(($educationLevels ?? collect()) as $e)
                            <option value="{{ $e }}" @selected(($filters['education_level'] ?? '')===$e)>{{ $e }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if($hasExperience)
                <div>
                    <x-input-label for="experience_level" value="سنوات الخبرة/المستوى" />
                    <select id="experience_level" name="experience_level" class="select select-bordered w-full">
                        <option value="">الكل</option>
                        @foreach(($experienceLevels ?? collect()) as $e)
                            <option value="{{ $e }}" @selected(($filters['experience_level'] ?? '')===$e)>{{ $e }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <x-input-label for="created_from" value="تاريخ التسجيل من" />
                <input type="date" id="created_from" name="created_from" value="{{ $filters['created_from'] ?? '' }}" class="input input-bordered w-full" />
            </div>
            <div>
                <x-input-label for="created_to" value="تاريخ التسجيل إلى" />
                <input type="date" id="created_to" name="created_to" value="{{ $filters['created_to'] ?? '' }}" class="input input-bordered w-full" />
            </div>
            <div>
                <x-input-label for="per_page" value="عدد النتائج/صفحة" />
                <select id="per_page" name="per_page" class="select select-bordered w-full">
                    @foreach([10,20,50,100,200] as $pp)
                        <option value="{{ $pp }}" @selected(($perPage ?? 20)==$pp)>{{ $pp }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-6 flex flex-wrap gap-2 items-end">
                <x-primary-button>تطبيق</x-primary-button>
                <a href="{{ $context==='admin' ? route('admin.seekers.browse') : route('company.seekers.browse') }}" class="btn">تفريغ</a>
                <a href="{{ request()->fullUrlWithQuery(['export'=>'csv']) }}" class="btn btn-ghost text-primary">تصدير CSV</a>
            </div>
        </form>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-x-auto" id="results">
            @include('company.jobseekers._results', ['seekers'=>$seekers,'context'=>$context])
        </div>
    </div>

    @push('scripts')
    <script>
    function districtPicker(){
      return {
        districts: [],
        selected: [],
        loading: false,

        init(initialProvince, initialSelected){
            this.selected = Array.isArray(initialSelected) ? [...initialSelected] : [];
            if(initialProvince){ this.load(); }
        },
        async load(){
            const prov = document.getElementById('province').value;
            if(!prov){ this.districts = []; this.selected = []; return; }
            this.loading = true;
            try {
                const response = await fetch(`/districts?province=${encodeURIComponent(prov)}`);
                const list = await response.json();
                this.districts = Array.isArray(list) ? list : [];
                this.selected = this.selected.filter(s => this.districts.includes(s));
            } catch (e) {
                console.error(e); this.districts = [];
            } finally { this.loading = false; }
        },
      }
    }
    </script>
    @endpush
</x-app-layout>

