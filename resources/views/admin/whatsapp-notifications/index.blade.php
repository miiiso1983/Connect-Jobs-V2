<x-app-layout>
    <x-slot name="header">
        <div class="rounded-xl bg-gradient-to-br from-[#4A00B8] via-[#5A00E1] to-[#3C0094] text-white p-6">
            <h2 class="text-xl font-bold">📱 إشعارات WhatsApp</h2>
            <p class="text-[#38BDF8] text-sm mt-1">تصفية الباحثين وإرسال رسائل تذكيرية عبر واتساب</p>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6" x-data="whatsappNotify()">
        {{-- Flash --}}
        @if (session('status'))
            <div class="p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                <span class="text-green-800 dark:text-green-400 whitespace-pre-line">{{ session('status') }}</span>
            </div>
        @endif

        {{-- Twilio Warning --}}
        @unless($twilioConfigured)
            <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-300 dark:border-amber-700">
                <span class="text-amber-800 dark:text-amber-400">⚠️ إعدادات Twilio غير مكتملة. أضف <code>TWILIO_SID</code> و <code>TWILIO_TOKEN</code> و <code>TWILIO_WHATSAPP_FROM</code> في ملف <code>.env</code></span>
            </div>
        @endunless

        {{-- Stats --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                <div class="w-10 h-10 rounded-lg bg-[#4A00B8] flex items-center justify-center">
                    <svg class="w-5 h-5 text-[#38BDF8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">إحصائيات (لديهم رقم واتساب)</h3>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-4 rounded-lg bg-gradient-to-br from-[#4A00B8]/5 to-[#4A00B8]/10 dark:from-[#4A00B8]/20 dark:to-[#4A00B8]/30">
                    <div class="text-3xl font-bold text-[#4A00B8] dark:text-[#38BDF8]">{{ $stats['total'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">إجمالي</div>
                </div>
                <div class="text-center p-4 rounded-lg bg-gradient-to-br from-red-500/5 to-red-500/10 dark:from-red-500/20 dark:to-red-500/30">
                    <div class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $stats['incomplete'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">بيانات غير مكتملة</div>
                </div>
                <div class="text-center p-4 rounded-lg bg-gradient-to-br from-amber-500/5 to-amber-500/10 dark:from-amber-500/20 dark:to-amber-500/30">
                    <div class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['no_cv'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">بدون CV</div>
                </div>
                <div class="text-center p-4 rounded-lg bg-gradient-to-br from-emerald-500/5 to-emerald-500/10 dark:from-emerald-500/20 dark:to-emerald-500/30">
                    <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['complete'] }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">مكتمل بالكامل</div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <form method="GET" action="{{ route('admin.whatsapp.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="label"><span class="label-text font-semibold">حالة الملف</span></label>
                    <select name="filter" class="select select-bordered w-full">
                        <option value="all" @selected($filter==='all')>الكل</option>
                        <option value="incomplete" @selected($filter==='incomplete')>بيانات غير مكتملة</option>
                        <option value="no-cv" @selected($filter==='no-cv')>بدون CV</option>
                        <option value="complete" @selected($filter==='complete')>مكتمل بالكامل</option>
                    </select>
                </div>
                <div>
                    <label class="label"><span class="label-text font-semibold">المحافظة</span></label>
                    <select name="province" class="select select-bordered w-full">
                        <option value="">الكل</option>
                        @foreach($provinces as $p)
                            <option value="{{ $p }}" @selected($province===$p)>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label"><span class="label-text font-semibold">بحث</span></label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="اسم، بريد، رقم..." class="input input-bordered w-full">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn bg-[#4A00B8] hover:bg-[#3C0094] text-white w-full">🔍 تصفية</button>
                </div>
            </form>
        </div>

        {{-- Results + Send Form --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">النتائج ({{ $seekers->total() }})</h3>
                <div class="flex items-center gap-3">
                    <button type="button" @click="toggleAll()" class="btn btn-sm btn-outline">
                        <span x-text="allSelected ? 'إلغاء تحديد الكل' : 'تحديد الكل'"></span>
                    </button>
                    <span class="text-sm text-gray-500" x-show="selected.length > 0" x-text="'محدد: ' + selected.length"></span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th><input type="checkbox" class="checkbox checkbox-sm" @change="toggleAll()" :checked="allSelected"></th>
                            <th>الاسم</th>
                            <th>البريد</th>
                            <th>رقم الواتساب</th>
                            <th>المحافظة</th>
                            <th>الحالة</th>
                            <th>CV</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($seekers as $s)
                            <tr>
                                <td><input type="checkbox" class="checkbox checkbox-sm" value="{{ $s->user_id }}" x-model.number="selected"></td>
                                <td class="font-semibold text-gray-900 dark:text-white">{{ $s->full_name }}</td>
                                <td class="text-sm text-gray-500">{{ $s->user->email ?? '—' }}</td>
                                <td class="text-sm font-mono">{{ $s->user->whatsapp_number ?? '—' }}</td>
                                <td class="text-sm">{{ $s->province ?? '—' }}</td>
                                <td>
                                    @if($s->profile_completed)
                                        <span class="badge badge-success badge-sm">مكتمل</span>
                                    @else
                                        <span class="badge badge-error badge-sm">غير مكتمل</span>
                                    @endif
                                </td>
                                <td>
                                    @if($s->cv_file)
                                        <span class="badge badge-info badge-sm">مرفوع</span>
                                    @else
                                        <span class="badge badge-warning badge-sm">لا يوجد</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-gray-400 py-8">لا توجد نتائج</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $seekers->links() }}</div>
        </div>

        {{-- Send Message Panel --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6" x-show="selected.length > 0" x-transition>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-lg bg-green-600 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.832-1.438A9.955 9.955 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18a7.96 7.96 0 01-4.11-1.14l-.29-.174-3.01.79.81-2.95-.19-.3A7.96 7.96 0 014 12c0-4.41 3.59-8 8-8s8 3.59 8 8-3.59 8-8 8z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">إرسال رسالة واتساب (<span x-text="selected.length"></span> مستخدم)</h3>
            </div>

            <form method="POST" action="{{ route('admin.whatsapp.send') }}" @submit="submitting = true">
                @csrf
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="user_ids[]" :value="id">
                </template>
                <input type="hidden" name="send_mode" :value="sendMode">

                {{-- Send Mode Toggle --}}
                <div class="mb-4">
                    <label class="label"><span class="label-text font-semibold">طريقة الإرسال</span></label>
                    <div class="flex gap-3">
                        <label class="flex items-center gap-2 cursor-pointer p-3 rounded-lg border-2 transition-all" :class="sendMode === 'template' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-700'">
                            <input type="radio" x-model="sendMode" value="template" class="radio radio-sm radio-success">
                            <span class="font-medium">📋 قالب معتمد (يصل بدون شروط)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer p-3 rounded-lg border-2 transition-all" :class="sendMode === 'custom' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700'">
                            <input type="radio" x-model="sendMode" value="custom" class="radio radio-sm radio-info">
                            <span class="font-medium">✏️ رسالة مخصصة (تتطلب محادثة سابقة)</span>
                        </label>
                    </div>
                </div>

                {{-- Template Selection --}}
                <div x-show="sendMode === 'template'" class="mb-4">
                    <label class="label"><span class="label-text font-semibold">اختر القالب</span></label>
                    <div class="flex flex-wrap gap-3">
                        <label class="flex items-center gap-2 cursor-pointer p-3 rounded-lg border-2 transition-all" :class="selectedTemplate === 'profile' ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-700'">
                            <input type="radio" name="template" x-model="selectedTemplate" value="profile" class="radio radio-sm radio-error">
                            <div>
                                <div class="font-medium text-red-600">📋 تذكير إكمال البيانات</div>
                                <div class="text-xs text-gray-500">للمستخدمين الذين لم يكملوا ملفهم</div>
                            </div>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer p-3 rounded-lg border-2 transition-all" :class="selectedTemplate === 'cv' ? 'border-amber-400 bg-amber-50 dark:bg-amber-900/20' : 'border-gray-200 dark:border-gray-700'"
                            @if(!config('services.twilio.content_sid_cv')) title="قالب CV غير معتمد بعد" @endif>
                            <input type="radio" name="template" x-model="selectedTemplate" value="cv" class="radio radio-sm radio-warning" @if(!config('services.twilio.content_sid_cv')) disabled @endif>
                            <div>
                                <div class="font-medium text-amber-600">📄 تذكير رفع CV</div>
                                <div class="text-xs text-gray-500">
                                    @if(config('services.twilio.content_sid_cv'))
                                        للمستخدمين الذين لم يرفعوا سيرتهم الذاتية
                                    @else
                                        ⚠️ غير معتمد بعد — أعد إنشاءه في Twilio
                                    @endif
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Custom Message --}}
                <div x-show="sendMode === 'custom'" class="mb-4">
                    <label class="label"><span class="label-text font-semibold">نص الرسالة</span></label>
                    <textarea name="message" x-model="message" rows="6" class="textarea textarea-bordered w-full" :required="sendMode === 'custom'" minlength="10" maxlength="2000" placeholder="اكتب رسالتك هنا..."></textarea>
                    <label class="label"><span class="label-text-alt" x-text="message.length + '/2000'"></span></label>
                </div>

                <button type="submit" class="btn bg-green-600 hover:bg-green-700 text-white w-full md:w-auto" :disabled="submitting || selected.length === 0 || (sendMode === 'custom' && !message.trim()) || (sendMode === 'template' && !selectedTemplate)" @if(!$twilioConfigured) disabled title="Twilio غير مفعّل" @endif>
                    <span x-show="!submitting">📤 إرسال عبر WhatsApp</span>
                    <span x-show="submitting" class="loading loading-spinner loading-sm"></span>
                </button>
            </form>
        </div>
    </div>

    <script>
    function whatsappNotify() {
        const pageIds = @json($seekers->pluck('user_id')->values());
        return {
            selected: [],
            allSelected: false,
            sendMode: 'template',
            selectedTemplate: 'profile',
            message: '',
            submitting: false,
            toggleAll() {
                if (this.allSelected) {
                    this.selected = [];
                    this.allSelected = false;
                } else {
                    this.selected = [...pageIds];
                    this.allSelected = true;
                }
            }
        };
    }
    </script>
</x-app-layout>
