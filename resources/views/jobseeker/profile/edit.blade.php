<x-app-layout>
    <x-slot name="header">
	        <div class="rounded-xl bg-gradient-to-br from-[#5B21B6] via-[#6D28D9] to-[#4C1D95] text-white p-6">
            <h2 class="text-xl font-bold">الملف الشخصي</h2>
	            <p class="text-[#38BDF8] text-sm mt-1">أكمل بياناتك لزيادة فرصك في الحصول على الوظيفة المناسبة</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))
            <div class="alert alert-success shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('jobseeker.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Profile Header Card --}}
	            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
	                <div class="bg-gradient-to-r from-[#5B21B6] to-[#6D28D9] p-6">
                    <div class="flex flex-col md:flex-row items-center gap-6">
                        {{-- Profile Image --}}
                        <div class="relative group">
	                            <div class="w-28 h-28 rounded-full ring-4 ring-[#38BDF8] ring-offset-4 ring-offset-[#5B21B6] overflow-hidden bg-white">
                                @if(!empty($js->profile_image))
                                    @php
                                        $imgPath = $js->profile_image;
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
                                    <img src="{{ Storage::url($js->profile_image) }}" @if($srcset) srcset="{{ $srcset }}" sizes="112px" @endif alt="Avatar" class="w-full h-full object-cover" />
                                @else
                                    <img src="https://api.dicebear.com/7.x/initials/svg?seed={{ urlencode($js->full_name ?? auth()->user()->name) }}&backgroundColor=0D2660&textColor=E7C66A" alt="Avatar" class="w-full h-full object-cover" />
                                @endif
                            </div>
                            <label for="profile_image" class="absolute inset-0 flex items-center justify-center bg-black/50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </label>
                            <input id="profile_image" name="profile_image" type="file" class="hidden" accept="image/png,image/jpeg,image/webp" />
                        </div>
                        {{-- User Info --}}
                        <div class="text-center md:text-right flex-1">
                            <h3 class="text-2xl font-bold text-white">{{ $js->full_name ?? auth()->user()->name ?? 'الباحث عن عمل' }}</h3>
	                            <p class="text-[#38BDF8] mt-1">{{ $js->job_title ?? 'لم يتم تحديد المسمى الوظيفي' }}</p>
                            <div class="flex flex-wrap justify-center md:justify-start gap-2 mt-3">
                                @if($js->profile_completed ?? false)
                                    <span class="badge badge-success gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> الملف مكتمل</span>
                                @else
                                    <span class="badge badge-warning gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg> أكمل ملفك</span>
                                @endif
                                @if($js->province ?? false)
                                    <span class="badge badge-ghost bg-white/20 text-white">{{ $js->province }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 text-sm text-gray-600 dark:text-gray-300">
                    <p>💡 انقر على الصورة لتغييرها • الصيغ المدعومة: PNG, JPG, WebP (حتى 2MB)</p>
                </div>
            </div>

            {{-- Basic Information Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
	                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
	                    <div class="w-10 h-10 rounded-lg bg-[#5B21B6] flex items-center justify-center">
	                        <svg class="w-5 h-5 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <h4 class="text-lg font-bold text-gray-800 dark:text-white">المعلومات الأساسية</h4>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="full_name" value="الاسم الكامل" class="text-gray-700 dark:text-gray-300 font-medium" />
	                        <x-text-input id="full_name" name="full_name" class="block mt-2 w-full border-gray-300 dark:border-gray-600 focus:border-[#5B21B6] focus:ring-[#5B21B6]" value="{{ old('full_name', $js->full_name ?? '') }}" placeholder="أدخل اسمك الكامل" />
                    </div>
                    <div x-data="districtPicker()" x-init="init('{{ old('province', $js->province ?? '') }}', @js(old('districts', $js->districts ?? [])))" class="md:col-span-2">
                        <x-input-label for="province" value="المحافظة" class="text-gray-700 dark:text-gray-300 font-medium" />
                        <select id="province" name="province" @change="load()" class="select select-bordered w-full mt-2 bg-white dark:bg-gray-700">
                            <option value="">— اختر المحافظة —</option>
                            @foreach($provinces as $p)
                                <option value="{{ $p }}" @selected(old('province', $js->province ?? '')===$p)>{{ $p }}</option>
                            @endforeach
                        </select>
                        <div class="mt-4">
                            <x-input-label value="المناطق المفضلة للعمل" class="text-gray-700 dark:text-gray-300 font-medium" />
                            <div class="mt-2 max-h-40 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-600 p-3 bg-gray-50 dark:bg-gray-700/50">
                                <template x-for="d in districts" :key="d">
                                    <label class="flex items-center gap-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-600 px-2 rounded cursor-pointer">
                                        <input type="checkbox" :value="d" name="districts[]" x-model="selected" class="checkbox checkbox-sm checkbox-primary">
                                        <span x-text="d" class="text-sm text-gray-700 dark:text-gray-200"></span>
                                    </label>
                                </template>
                                <template x-if="!districts.length">
                                    <div class="text-sm text-gray-500 text-center py-4">اختر محافظة لعرض المناطق</div>
                                </template>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2" x-show="selected.length">
	                                <template x-for="s in selected" :key="s">
	                                    <span class="px-3 py-1 rounded-full bg-[#5B21B6] text-white text-xs font-medium" x-text="s"></span>
	                                </template>
                            </div>
                        </div>
                    </div>
                    <div>
                        <x-input-label for="job_title" value="المسمى الوظيفي" class="text-gray-700 dark:text-gray-300 font-medium" />
                        <select id="job_title" name="job_title" class="select select-bordered w-full mt-2 bg-white dark:bg-gray-700">
                            <option value="">— اختر المسمى —</option>
                            @foreach(($titles->sort() ?? collect()) as $t)
                                <option value="{{ $t }}" @selected(old('job_title', $js->job_title ?? '')===$t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="gender" value="الجنس" class="text-gray-700 dark:text-gray-300 font-medium" />
                        <select id="gender" name="gender" class="select select-bordered w-full mt-2 bg-white dark:bg-gray-700">
                            <option value="">— اختر —</option>
                            <option value="male" @selected(($js->gender ?? '')==='male')>ذكر</option>
                            <option value="female" @selected(($js->gender ?? '')==='female')>أنثى</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="own_car" value="امتلاك سيارة" class="text-gray-700 dark:text-gray-300 font-medium" />
                        <select id="own_car" name="own_car" class="select select-bordered w-full mt-2 bg-white dark:bg-gray-700">
                            <option value="0" @selected(($js->own_car ?? 0)==0)>لا</option>
                            <option value="1" @selected(($js->own_car ?? 0)==1)>نعم</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Specializations Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
	                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
	                    <div class="w-10 h-10 rounded-lg bg-[#38BDF8] flex items-center justify-center">
	                        <svg class="w-5 h-5 text-[#4C1D95]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    </div>
                    <h4 class="text-lg font-bold text-gray-800 dark:text-white">التخصصات</h4>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div x-data="{selected: @js(old('specialities', $js->specialities ?? [])), options: @js($specialities->sort()->values())}">
                        <x-input-label value="التخصصات المتاحة" class="text-gray-700 dark:text-gray-300 font-medium" />
                        <div class="mt-2 max-h-48 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-600 p-3 bg-gray-50 dark:bg-gray-700/50">
                            <template x-for="s in options" :key="s">
                                <label class="flex items-center gap-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-600 px-2 rounded cursor-pointer">
                                    <input type="checkbox" :value="s" name="specialities[]" x-model="selected" class="checkbox checkbox-sm checkbox-primary">
                                    <span x-text="s" class="text-sm text-gray-700 dark:text-gray-200"></span>
                                </label>
                            </template>
                            <template x-if="!options.length">
                                <div class="text-sm text-gray-500 text-center py-4">لا توجد تخصصات</div>
                            </template>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2" x-show="selected.length">
	                            <template x-for="s in selected" :key="s">
	                                <span class="px-3 py-1 rounded-full bg-[#38BDF8] text-[#4C1D95] text-xs font-medium" x-text="s"></span>
	                            </template>
                        </div>
                    </div>
                    <div>
                        <x-input-label for="speciality" value="تخصص إضافي (اختياري)" class="text-gray-700 dark:text-gray-300 font-medium" />
                        <input list="specialities_list" id="speciality" name="speciality" class="input input-bordered w-full mt-2 bg-white dark:bg-gray-700" value="{{ old('speciality', $js->speciality ?? '') }}" placeholder="أدخل تخصصاً آخر..." />
                        <datalist id="specialities_list">@foreach($specialities as $s)<option value="{{ $s }}" />@endforeach</datalist>
                    </div>
                </div>
            </div>

	            {{-- Education Section --}}
	            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
		            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
		                <div class="w-10 h-10 rounded-lg bg-[#5B21B6] flex items-center justify-center">
		                    <svg class="w-5 h-5 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422A12.083 12.083 0 0121 12.5c0 4.418-4.03 8-9 8s-9-3.582-9-8c0-.64.06-1.267.176-1.875L12 14z"/></svg>
	                    </div>
	                    <h4 class="text-lg font-bold text-gray-800 dark:text-white">المؤهل الدراسي</h4>
	                </div>
	                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
	                    <div>
	                        <x-input-label for="university_name" value="اسم الجامعة" class="text-gray-700 dark:text-gray-300 font-medium" />
	                        <x-text-input id="university_name" name="university_name" class="block mt-2 w-full" value="{{ old('university_name', $js->university_name ?? '') }}" placeholder="مثال: جامعة بغداد" />
	                    </div>
	                    <div>
	                        <x-input-label for="college_name" value="اسم الكلية" class="text-gray-700 dark:text-gray-300 font-medium" />
	                        <x-text-input id="college_name" name="college_name" class="block mt-2 w-full" value="{{ old('college_name', $js->college_name ?? '') }}" placeholder="مثال: كلية الصيدلة" />
	                    </div>
	                    <div>
	                        <x-input-label for="department_name" value="اسم القسم" class="text-gray-700 dark:text-gray-300 font-medium" />
	                        <x-text-input id="department_name" name="department_name" class="block mt-2 w-full" value="{{ old('department_name', $js->department_name ?? '') }}" placeholder="مثال: صيدلة" />
	                    </div>
	                    <div>
	                        <x-input-label for="graduation_year" value="سنة التخرج" class="text-gray-700 dark:text-gray-300 font-medium" />
	                        <input id="graduation_year" name="graduation_year" type="number" min="1950" max="2100" class="input input-bordered w-full mt-2 bg-white dark:bg-gray-700" value="{{ old('graduation_year', $js->graduation_year ?? '') }}" placeholder="مثال: 2024" />
	                    </div>
	                    <div class="md:col-span-2">
	                        <div class="flex items-center justify-between gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
	                            <div>
	                                <div class="font-medium text-gray-800 dark:text-white">الخريجين الجدد</div>
	                                <div class="text-sm text-gray-600 dark:text-gray-300">فعّل الخيار إذا كنت خريجاً جديداً.</div>
	                            </div>
	                            <label class="flex items-center gap-2 cursor-pointer">
	                                <input type="checkbox" name="is_fresh_graduate" value="1" class="toggle toggle-primary" @checked((bool) old('is_fresh_graduate', $js->is_fresh_graduate ?? false))>
	                                <span class="text-sm text-gray-700 dark:text-gray-200">نعم</span>
	                            </label>
	                        </div>
	                    </div>
	                </div>
	            </div>

            {{-- About Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
	                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
	                    <div class="w-10 h-10 rounded-lg bg-[#5B21B6] flex items-center justify-center">
	                        <svg class="w-5 h-5 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h4 class="text-lg font-bold text-gray-800 dark:text-white">نبذة عني</h4>
                </div>
                <div class="space-y-6">
                    <div>
                        <x-input-label for="summary" value="الملخص الشخصي" class="text-gray-700 dark:text-gray-300 font-medium" />
                        <textarea id="summary" name="summary" rows="3" class="textarea textarea-bordered w-full mt-2 bg-white dark:bg-gray-700" placeholder="اكتب نبذة مختصرة عن نفسك وأهدافك المهنية...">{{ old('summary', $js->summary ?? '') }}</textarea>
                    </div>
                    <div>
                        <x-input-label for="qualifications" value="المؤهلات العلمية" class="text-gray-700 dark:text-gray-300 font-medium" />
                        <textarea id="qualifications" name="qualifications" rows="3" class="textarea textarea-bordered w-full mt-2 bg-white dark:bg-gray-700" placeholder="مثال: بكالوريوس صيدلة - جامعة بغداد 2020">{{ old('qualifications', $js->qualifications ?? '') }}</textarea>
                    </div>
                    <div>
                        <x-input-label for="experiences" value="الخبرات العملية" class="text-gray-700 dark:text-gray-300 font-medium" />
                        <textarea id="experiences" name="experiences" rows="3" class="textarea textarea-bordered w-full mt-2 bg-white dark:bg-gray-700" placeholder="اذكر خبراتك السابقة مع المدة الزمنية...">{{ old('experiences', $js->experiences ?? '') }}</textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="languages" value="اللغات" class="text-gray-700 dark:text-gray-300 font-medium" />
                            <textarea id="languages" name="languages" rows="2" class="textarea textarea-bordered w-full mt-2 bg-white dark:bg-gray-700" placeholder="مثال: العربية (ممتاز)، الإنجليزية (جيد)">{{ old('languages', $js->languages ?? '') }}</textarea>
                        </div>
                        <div>
                            <x-input-label for="skills" value="المهارات" class="text-gray-700 dark:text-gray-300 font-medium" />
                            <textarea id="skills" name="skills" rows="2" class="textarea textarea-bordered w-full mt-2 bg-white dark:bg-gray-700" placeholder="مثال: Excel, التواصل، العمل الجماعي">{{ old('skills', $js->skills ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CV Upload Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
	                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
	                    <div class="w-10 h-10 rounded-lg bg-[#38BDF8] flex items-center justify-center">
	                        <svg class="w-5 h-5 text-[#4C1D95]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    </div>
                    <h4 class="text-lg font-bold text-gray-800 dark:text-white">السيرة الذاتية</h4>
                </div>
                <div class="flex flex-col md:flex-row gap-6 items-start"
                     x-data="{
                        fileName: '',
                        fileSize: '',
                        fileIcon: '',
                        error: '',
                        ready: false,
                        allowedTypes: ['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                        allowedExts: ['pdf','doc','docx'],
                        maxSize: 5 * 1024 * 1024,
                        handleFile(e) {
                            this.error = ''; this.ready = false; this.fileName = ''; this.fileSize = ''; this.fileIcon = '';
                            const file = e.target.files[0];
                            if (!file) return;
                            const ext = file.name.split('.').pop().toLowerCase();
                            if (!this.allowedExts.includes(ext) && !this.allowedTypes.includes(file.type)) {
                                this.error = 'صيغة الملف غير مدعومة. الصيغ المسموحة: PDF, DOC, DOCX';
                                e.target.value = '';
                                return;
                            }
                            if (file.size > this.maxSize) {
                                this.error = 'حجم الملف يتجاوز الحد الأقصى (5MB). حجم الملف: ' + (file.size / 1024 / 1024).toFixed(1) + 'MB';
                                e.target.value = '';
                                return;
                            }
                            this.fileName = file.name;
                            this.fileSize = (file.size / 1024).toFixed(0) + ' KB';
                            this.fileIcon = ext === 'pdf' ? 'pdf' : 'word';
                            this.ready = true;
                        },
                        clear() {
                            this.fileName = ''; this.fileSize = ''; this.error = ''; this.ready = false; this.fileIcon = '';
                            document.getElementById('cv').value = '';
                        }
                     }">
                    <div class="flex-1">
                        <x-input-label for="cv" value="رفع السيرة الذاتية (PDF/Word)" class="text-gray-700 dark:text-gray-300 font-medium" />
                        <div class="mt-2 flex items-center justify-center w-full">
                            <label for="cv"
                                   class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer transition-colors"
                                   :class="error ? 'border-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:border-red-600' : (ready ? 'border-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:border-green-600' : 'border-gray-300 bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-700')">
                                {{-- Default state --}}
                                <div x-show="!ready && !error" class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">انقر للرفع</span> أو اسحب الملف هنا</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">PDF, DOC, DOCX (حتى 5MB)</p>
                                </div>
                                {{-- Ready / preview state --}}
                                <div x-show="ready" x-cloak class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <div class="flex items-center gap-2 mb-2">
                                        <template x-if="fileIcon === 'pdf'">
                                            <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4zM8.5 13h1c.55 0 1 .45 1 1s-.45 1-1 1h-1v1.5a.5.5 0 01-1 0V13a.5.5 0 01.5-.5h1zm3.5 0h1c.83 0 1.5.67 1.5 1.5v1c0 .83-.67 1.5-1.5 1.5h-1a.5.5 0 01-.5-.5V13a.5.5 0 01.5-.5h1zm4 0a.5.5 0 01.5.5.5.5 0 01-.5.5H15v.5h1a.5.5 0 010 1h-1v1a.5.5 0 01-1 0V13a.5.5 0 01.5-.5H16z"/></svg>
                                        </template>
                                        <template x-if="fileIcon === 'word'">
                                            <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4zM8 13l1.5 5.5L11 14l1.5 4.5L14 13h1.5l-2.5 8h-1l-1.5-4.5L10 21H9l-2.5-8H8z"/></svg>
                                        </template>
                                        <span class="text-green-600 dark:text-green-400 font-bold text-lg">✓</span>
                                    </div>
                                    <p class="text-sm font-semibold text-green-700 dark:text-green-400 truncate max-w-xs px-4" x-text="fileName"></p>
                                    <p class="text-xs text-green-600 dark:text-green-500 mt-1">جاهز للرفع • <span x-text="fileSize"></span></p>
                                </div>
                                {{-- Error state --}}
                                <div x-show="error" x-cloak class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p class="text-sm text-red-600 dark:text-red-400 font-medium text-center px-4" x-text="error"></p>
                                    <p class="text-xs text-red-500 mt-1">انقر لاختيار ملف آخر</p>
                                </div>
                                <input id="cv" name="cv" type="file" class="hidden" accept=".pdf,.doc,.docx" @change="handleFile($event)" />
                            </label>
                        </div>
                        {{-- Clear selection button --}}
                        <div x-show="ready" x-cloak class="mt-2 flex justify-end">
                            <button type="button" @click="clear()" class="text-xs text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 flex items-center gap-1 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                إزالة الملف المحدد
                            </button>
                        </div>
                        @if(!empty($js->cv_file))
                            <div class="mt-3 flex items-center gap-2 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-sm text-green-700 dark:text-green-400">لديك سيرة ذاتية مرفوعة:</span>
							<a href="{{ Storage::url($js->cv_file) }}" class="text-sm font-medium text-[#5B21B6] dark:text-[#38BDF8] hover:underline" target="_blank">عرض الملف</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
	                        <button type="submit" class="btn bg-[#5B21B6] hover:bg-[#4C1D95] text-white border-none px-8">
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            حفظ التغييرات
                        </button>
                        <a href="{{ route('jobseeker.dashboard') }}" class="btn btn-ghost">إلغاء</a>
                    </div>
	                    <a href="{{ route('jobseeker.profile.pdf') }}" target="_blank" class="btn bg-[#38BDF8] hover:bg-[#0EA5E9] text-[#4C1D95] border-none gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"/></svg>
                        تصدير كـ PDF
                    </a>
                </div>
            </div>
        </form>
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
</x-app-layout>

