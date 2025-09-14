<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">فلترة المتقدمين</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Filter Form -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-header">
                <h3 class="card-title text-lg font-semibold p-6 pb-0">فلترة المتقدمين</h3>
            </div>
            <div class="card-body">
                <form method="GET" class="space-y-6">
                    <!-- Row 1: Job and Job Title -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="job_id" value="الوظيفة" />
                            <select id="job_id" name="job_id" class="select select-bordered w-full mt-1">
                                <option value="">الكل</option>
                                @foreach ($jobs as $job)
                                    <option value="{{ $job->id }}" @selected(request('job_id')==$job->id)>{{ $job->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="job_title" value="المسمى الوظيفي" />
                            <select id="job_title" name="job_title" class="select select-bordered w-full mt-1">
                                <option value="">الكل</option>
                                @foreach ($titles->sort() as $t)
                                    <option value="{{ $t }}" @selected(request('job_title')===$t)>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="gender" value="الجنس" />
                            <select id="gender" name="gender" class="select select-bordered w-full mt-1">
                                <option value="">الكل</option>
                                <option value="male" @selected(request('gender')==='male')>ذكر</option>
                                <option value="female" @selected(request('gender')==='female')>أنثى</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 2: Province and Districts -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div x-data="districtPicker()" x-init="init('{{ request('province') }}', @js(request()->input('districts', [])))">
                            <x-input-label for="province" value="المحافظة" />
                            <select id="province" name="province" @change="load()" class="select select-bordered w-full mt-1">
                                <option value="">الكل</option>
                                @foreach ($provinces->sort() as $p)
                                    <option value="{{ $p }}" @selected(request('province')===$p)>{{ $p }}</option>
                                @endforeach
                            </select>
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
                        <div x-data="{options: @js($specialities->sort()->values()), selected: @js(request()->input('specialities', []))}">
                            <x-input-label value="التخصصات" />
                            <div class="mt-2 max-h-48 overflow-y-auto rounded border border-base-300 p-3 space-y-2 bg-base-50">
                                <template x-for="s in options" :key="s">
                                    <label class="flex items-center gap-2 cursor-pointer hover:bg-base-100 p-1 rounded">
                                        <input type="checkbox" :value="s" name="specialities[]" x-model="selected" class="checkbox checkbox-sm checkbox-primary">
                                        <span x-text="s" class="text-sm"></span>
                                    </label>
                                </template>
                                <template x-if="!options.length">
                                    <div class="text-sm text-gray-500">لا توجد تخصصات</div>
                                </template>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-2" x-show="selected.length">
                                <template x-for="s in selected" :key="s">
                                    <span class="badge badge-outline badge-sm" x-text="s"></span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: Own Car and Search Button -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <x-input-label for="own_car" value="يملك سيارة" />
                            <select id="own_car" name="own_car" class="select select-bordered w-full mt-1">
                                <option value="">الكل</option>
                                <option value="1" @selected(request('own_car')==='1')>نعم</option>
                                <option value="0" @selected(request('own_car')==='0')>لا</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" class="btn btn-primary w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                بحث وفلترة
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- Active Filters -->
        @php($chips = [])
        @if($filters['job_id'])
            @php($chips[] = ['label'=>'الوظيفة','value'=>optional($jobs->firstWhere('id',(int)$filters['job_id']))->title,'param'=>'job_id','val'=>$filters['job_id']])
        @endif
        @if($filters['job_title'])
            @php($chips[] = ['label'=>'المسمى','value'=>$filters['job_title'],'param'=>'job_title','val'=>null])
        @endif
        @if($filters['province'])
            @php($chips[] = ['label'=>'المحافظة','value'=>$filters['province'],'param'=>'province','val'=>null])
        @endif
        @if(!empty(request()->input('districts', [])))
            @foreach(request()->input('districts', []) as $d)
                @php($chips[] = ['label'=>'منطقة','value'=>$d,'param'=>'districts[]','val'=>$d])
            @endforeach
        @endif
        @if(!empty(request()->input('specialities', [])))
            @foreach(request()->input('specialities', []) as $s)
                @php($chips[] = ['label'=>'تخصص','value'=>$s,'param'=>'specialities[]','val'=>$s])
            @endforeach
        @endif
        @if($filters['gender'])
            @php($chips[] = ['label'=>'الجنس','value'=>($filters['gender'] === 'male' ? 'ذكر' : 'أنثى'),'param'=>'gender','val'=>null])
        @endif
        @if($filters['own_car']!==null && $filters['own_car']!=='')
            @php($chips[] = ['label'=>'سيارة','value'=>$filters['own_car']?'نعم':'لا','param'=>'own_car','val'=>null])
        @endif

        @if(!empty($chips))
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-medium text-gray-600">الفلاتر النشطة:</span>
                    @foreach($chips as $c)
                        <a href="{{ request()->fullUrlWithQuery([$c['param'] => null]) }}"
                           class="badge badge-primary gap-2 hover:badge-error transition-colors">
                            <span class="font-medium">{{ $c['label'] }}:</span>
                            <span>{{ $c['value'] }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3">
                                <path fill-rule="evenodd" d="M6.225 4.811a.75.75 0 011.06 0L12 9.525l4.715-4.714a.75.75 0 111.06 1.06L13.06 10.585l4.715 4.714a.75.75 0 11-1.06 1.061L12 11.646l-4.715 4.714a.75.75 0 01-1.06-1.06l4.714-4.715-4.714-4.714a.75.75 0 010-1.06z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endforeach
                    <a href="{{ route('company.applicants.index') }}"
                       class="btn btn-ghost btn-sm mr-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        مسح الكل
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Results Table -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-header">
                <h3 class="card-title text-lg font-semibold p-6 pb-0">
                    نتائج البحث
                    @if(count($applicants) > 0)
                        <span class="badge badge-primary">{{ count($applicants) }} متقدم</span>
                    @endif
                </h3>
            </div>
            <div class="card-body p-0">
                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr class="bg-base-200">
                                <th class="text-right">#</th>
                                <th class="text-right">الاسم الكامل</th>
                                <th class="text-right">المسمى الوظيفي</th>
                                <th class="text-right">المحافظة</th>
                                <th class="text-right">التخصصات</th>
                                <th class="text-right">المناطق</th>
                                <th class="text-right">سيارة</th>
                                <th class="text-right">نسبة المطابقة</th>
                                <th class="text-right">العمليات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($applicants as $a)
                                <tr class="hover">
                                    <td class="font-mono text-sm">{{ $a->id }}</td>
                                    <td class="font-semibold">
                                        <div class="flex items-center gap-2">
                                            <div class="avatar">
                                                <div class="w-8 h-8 rounded-full overflow-hidden">
                                                    @if(!empty($a->profile_image))
                                                        <img src="{{ Storage::url($a->profile_image) }}" alt="Avatar">
                                                    @else
                                                        <img src="https://api.dicebear.com/7.x/initials/svg?seed={{ urlencode($a->full_name) }}" alt="Avatar">
                                                    @endif
                                                </div>
                                            </div>
                                            <span>{{ $a->full_name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $a->job_title }}</td>
                                    <td>{{ $a->province }}</td>
                                    <td>
                                        <div class="flex flex-wrap gap-1">
                                            @forelse((array)($a->specialities ?? []) as $sp)
                                                <span class="badge badge-outline badge-sm">{{ $sp }}</span>
                                            @empty
                                                <span class="text-gray-400">-</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex flex-wrap gap-1">
                                            @forelse((array)($a->districts ?? []) as $d)
                                                <span class="badge badge-ghost badge-sm">{{ $d }}</span>
                                            @empty
                                                <span class="text-gray-400">-</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $a->own_car ? 'badge-success' : 'badge-ghost' }} badge-sm">
                                            {{ $a->own_car ? 'نعم' : 'لا' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info badge-sm font-bold">{{ $a->matching_percentage }}%</span>
                                    </td>
                                    <td>
                                        @php($application = \App\Models\Application::where('job_seeker_id',$a->id)->latest('applied_at')->first())
                                        <div class="flex flex-wrap gap-1">
                                            <a href="{{ route('company.applicants.show', $a) }}" class="btn btn-xs btn-ghost" title="عرض">عرض</a>

                                            <form method="POST" action="{{ route('company.applications.update', $application) }}" class="inline">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="action" value="accept">
                                                <button class="btn btn-xs btn-success" title="قبول الطلب">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('company.applications.update', $application) }}" class="inline">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="action" value="reject">
                                                <button class="btn btn-xs btn-error" title="رفض الطلب">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('company.applications.update', $application) }}" class="inline">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="action" value="archive">
                                                <button class="btn btn-xs btn-ghost" title="أرشفة الطلب">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-8 text-gray-500">
                                        <div class="flex flex-col items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span>لا توجد نتائج مطابقة للفلاتر المحددة</span>
                                            <span class="text-sm">(الحد الأدنى للمطابقة: 50%)</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden space-y-4 p-4">
                    @forelse ($applicants as $a)
                        <div class="card bg-base-50 border border-base-300">
                            <div class="card-body p-4">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <div class="avatar">
                                                <div class="w-10 h-10 rounded-full overflow-hidden">
                                                    @if(!empty($a->profile_image))
                                                        <img src="{{ Storage::url($a->profile_image) }}" alt="Avatar">
                                                    @else
                                                        <img src="https://api.dicebear.com/7.x/initials/svg?seed={{ urlencode($a->full_name) }}" alt="Avatar">
                                                    @endif
                                                </div>
                                            </div>
                                            <h4 class="font-semibold text-lg">{{ $a->full_name }}</h4>
                                        </div>
                                        <p class="text-sm text-gray-600">{{ $a->job_title }}</p>
                                        <p class="text-xs text-gray-500">#{{ $a->id }}</p>
                                    </div>
                                    <span class="badge badge-info badge-lg font-bold">{{ $a->matching_percentage }}%</span>
                                </div>

                                <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                                    <div>
                                        <span class="font-medium text-gray-600">المحافظة:</span>
                                        <span>{{ $a->province }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-600">سيارة:</span>
                                        <span class="badge {{ $a->own_car ? 'badge-success' : 'badge-ghost' }} badge-sm">
                                            {{ $a->own_car ? 'نعم' : 'لا' }}
                                        </span>
                                    </div>
                                </div>

                                @if(!empty($a->specialities))
                                <div class="mb-3">
                                    <span class="font-medium text-gray-600 text-sm">التخصصات:</span>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach((array)($a->specialities ?? []) as $sp)
                                            <span class="badge badge-outline badge-sm">{{ $sp }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                @if(!empty($a->districts))
                                <div class="mb-4">
                                    <span class="font-medium text-gray-600 text-sm">المناطق:</span>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach((array)($a->districts ?? []) as $d)
                                            <span class="badge badge-ghost badge-sm">{{ $d }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                @php($application = \App\Models\Application::where('job_seeker_id',$a->id)->latest('applied_at')->first())
                                <div class="flex gap-2">
                                    <a href="{{ route('company.applicants.show', $a) }}" class="btn btn-ghost btn-sm">عرض</a>

                                    <form method="POST" action="{{ route('company.applications.update', $application) }}" class="flex-1">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="action" value="accept">
                                        <button class="btn btn-success btn-sm w-full">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            قبول
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('company.applications.update', $application) }}" class="flex-1">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="action" value="reject">
                                        <button class="btn btn-error btn-sm w-full">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            رفض
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('company.applications.update', $application) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="action" value="archive">
                                        <button class="btn btn-ghost btn-sm" title="أرشفة">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-600 mb-2">لا توجد نتائج</h3>
                            <p class="text-gray-500">لا توجد نتائج مطابقة للفلاتر المحددة</p>
                            <p class="text-sm text-gray-400">(الحد الأدنى للمطابقة: 50%)</p>
                        </div>
                    @endforelse
                </div>
            </div>
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

</x-app-layout>

