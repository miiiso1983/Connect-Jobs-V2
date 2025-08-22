<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">تعديل وظيفة</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto px-4">
        <form method="POST" action="{{ route('company.jobs.update',$job) }}" enctype="multipart/form-data" class="card bg-base-100 p-6 shadow space-y-4">
            @csrf
            @method('PUT')

            <div>
                <x-input-label for="title" value="Job Title" />
                <input list="job_titles" id="title" name="title" value="{{ old('title',$job->title) }}" class="input input-bordered w-full" />
                <datalist id="job_titles">
                    @foreach ($titles as $t)
                        <option value="{{ $t }}" />
                    @endforeach
                </datalist>
                <x-input-error :messages="$errors->get('title')" class="mt-1" />
            </div>

            <div x-data="districtPicker()" x-init="init('{{ old('province',$job->province) }}')">
                <x-input-label for="province" value="Province" />
                <select id="province" name="province" @change="load()" class="select select-bordered w-full">
                    <option value="">—</option>
                    @foreach ($provinces as $p)
                        <option value="{{ $p }}" @selected(old('province',$job->province)===$p)>{{ $p }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('province')" class="mt-1" />
                <div class="mt-3">
                    <x-input-label for="districts" value="Districts" />
                    <div id="districts-data" data-initial-districts="{{ json_encode(old('districts', $job->districts ?? [])) }}" style="display: none;"></div>
                    <select id="districts" name="districts[]" multiple size="5" class="select select-bordered w-full">
                        <template x-for="d in districts" :key="d">
                            <option :value="d" x-text="d" :selected="selected.includes(d)"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div>
                <x-input-label for="requirements" value="Requirements" />
                <textarea id="requirements" name="requirements" class="textarea textarea-bordered w-full" rows="3">{{ old('requirements',$job->requirements) }}</textarea>
            </div>

            <div>
                <x-input-label for="description" value="Description" />
                <textarea id="description" name="description" class="textarea textarea-bordered w-full" rows="5">{{ old('description',$job->description) }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-1" />
            </div>

            <div>
@push('scripts')
<script>
function districtPicker(){
  const dataElement = document.getElementById('districts-data');
  const initialDistricts = dataElement ? JSON.parse(dataElement.dataset.initialDistricts || '[]') : [];

  return {
    districts: [],
    selected: initialDistricts,
    init(initialProvince){ if(initialProvince){ this.load(); } },
    load(){ const prov = document.getElementById('province').value; if(!prov){ this.districts=[]; this.selected=[]; return; }
      fetch(`/districts?province=${encodeURIComponent(prov)}`).then(r=>r.json()).then(list=>{ this.districts=list; });
    }
  }
}
</script>
@endpush
                <x-input-label for="jd_file" value="Job Description (PDF/Doc)" />
                <input id="jd_file" name="jd_file" type="file" class="mt-1 block w-full text-sm" accept=".pdf,.doc,.docx" />
                @if($job->jd_file)
                    <div class="text-xs mt-1">
                        الملف الحالي: <a class="text-indigo-600" target="_blank" href="{{ Storage::url($job->jd_file) }}">عرض</a>
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-between">
                <x-primary-button>حفظ</x-primary-button>
                <form method="POST" action="{{ route('company.jobs.destroy',$job) }}" x-data="{open:false}">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="open=true" class="px-3 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white">حذف</button>
                    <x-confirm-modal title="تأكيد الحذف" message="هل أنت متأكد من حذف هذه الوظيفة؟ لا يمكن التراجع." />
                </form>
            </div>
        </form>
    </div>
</x-app-layout>

