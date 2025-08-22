<x-app-layout>
<div x-data='{ prov: @js($stats["by_province"]), spec: @js($stats["by_speciality"]) }'></div>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">تفاصيل الوظيفة</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('company.jobs.edit',$job) }}" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm">تعديل</a>
                <form method="POST" action="{{ route('company.jobs.destroy',$job) }}" x-data="{open:false}">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="open=true" class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm">حذف</button>
                    <x-confirm-modal title="تأكيد الحذف" message="هل أنت متأكد من حذف هذه الوظيفة؟ لا يمكن التراجع." />
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow md:col-span-2">
                <div class="text-sm text-gray-500">العنوان</div>
                <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $job->title }}</div>
                <div class="mt-4 text-sm text-gray-500">الوصف</div>
                <div class="prose prose-sm dark:prose-invert max-w-none">{!! nl2br(e($job->description)) !!}</div>
                @if($job->requirements)
                    <div class="mt-4 text-sm text-gray-500">المتطلبات</div>
                    <div class="prose prose-sm dark:prose-invert max-w-none">{!! nl2br(e($job->requirements)) !!}</div>
                @endif
                @if($job->jd_file)
                    <div class="mt-4 text-sm text-gray-500">الوصف الوظيفي (ملف)</div>
                    <a class="text-indigo-600" href="{{ Storage::url($job->jd_file) }}" target="_blank">عرض الملف</a>
                @endif
            </div>
            <div class="space-y-4">
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
                    <div class="text-sm text-gray-500">المحافظة</div>
                    <div class="text-lg font-semibold">{{ $job->province }}</div>
                    <div class="mt-3 text-sm text-gray-500">حالة النشر</div>
                    <div><span class="px-2 py-1 rounded-full text-xs {{ $job->status==='open'?'bg-emerald-100 text-emerald-800':'bg-gray-100 text-gray-700' }}">{{ $job->status }}</span></div>
                    <div class="mt-3 text-sm text-gray-500">موافقة الأدمن</div>
                    <div><span class="px-2 py-1 rounded-full text-xs {{ $job->approved_by_admin ? 'bg-emerald-100 text-emerald-800':'bg-yellow-100 text-yellow-800' }}">{{ $job->approved_by_admin ? 'معتمدة' : 'بانتظار' }}</span></div>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
                    <div class="text-sm text-gray-500">عدد المتقدمين</div>
                    <div class="text-2xl font-bold">{{ $job->applications_count }}</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow">
                <div class="p-4 font-semibold">آخر المتقدمين</div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($latestApplicants as $app)
                        <div class="p-4 flex items-center justify-between">
                            <div>
                                <div class="font-semibold">{{ $app->jobSeeker->full_name ?? ('#'.$app->job_seeker_id) }}</div>
                                <div class="text-xs text-gray-500">{{ $app->applied_at }}</div>
                            </div>
                            <div class="text-sm text-gray-600">مطابقة: {{ $app->matching_percentage }}%</div>
                        </div>
                    @empty
                        <div class="p-4 text-gray-500">لا يوجد متقدمون حتى الآن</div>
                    @endforelse
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
                <div class="text-sm text-gray-500">متوسط نسبة المطابقة</div>
                <div class="text-3xl font-bold">{{ number_format($stats['avg_match'],1) }}%</div>
            </div>

            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow">
                <div class="text-sm text-gray-500">أعلى المحافظات</div>
                <ul class="mt-2 space-y-1 text-sm">
                    @foreach($stats['by_province'] as $row)
                        <li class="flex items-center justify-between"><span>{{ $row->province ?: 'غير محدد' }}</span><span class="text-gray-500">{{ $row->c }}</span></li>
                    @endforeach
            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow md:col-span-3">
                <div class="text-sm text-gray-500 mb-2">توزيع المحافظات (Top 5)</div>
                <div class="space-y-2" x-data>
                    <template x-for="row in prov" :key="row.province">
                        <div class="flex items-center gap-3">
                            <div class="w-28 text-xs text-gray-600" x-text="row.province || 'غير محدد'"></div>
                            <div class="flex-1 h-2 rounded-full bg-gray-200 dark:bg-gray-700">
                                <div class="h-2 rounded-full bg-gradient-to-r from-sky-500 to-indigo-600" :style="`width: ${Math.min(100, (row.c / (prov[0]?.c||1)) * 100)}%`"></div>
                            </div>
                            <div class="w-10 text-xs text-gray-500" x-text="row.c"></div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow md:col-span-3">
            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow md:col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="text-sm text-gray-500 mb-2">Pie المحافظات</div>
                        <canvas id="pieProvince" height="160"></canvas>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 mb-2">Pie التخصصات</div>
                        <canvas id="pieSpec" height="160"></canvas>
                    </div>
                </div>
                <script>
                    document.addEventListener('alpine:init', () => {
                        const prov = @json($stats['by_province']);
                        const spec = @json($stats['by_speciality']);
                        const ctx1 = document.getElementById('pieProvince');
                        const ctx2 = document.getElementById('pieSpec');
                        const labels1 = prov.map(r => r.province || 'غير محدد');
                        const data1 = prov.map(r => r.c);
                        const labels2 = spec.map(r => r.speciality || 'غير محدد');
                        const data2 = spec.map(r => r.c);
                        const colors1 = ['#0ea5e9','#6366f1','#06b6d4','#22d3ee','#38bdf8'];
                        const colors2 = ['#10b981','#34d399','#059669','#16a34a','#4ade80'];
                        if (ctx1) new Chart(ctx1, { type: 'pie', data: { labels: labels1, datasets: [{ data: data1, backgroundColor: colors1 }] }, options: { plugins: { legend: { position: 'bottom' }}}});
                        if (ctx2) new Chart(ctx2, { type: 'pie', data: { labels: labels2, datasets: [{ data: data2, backgroundColor: colors2 }] }, options: { plugins: { legend: { position: 'bottom' }}}});
                    });
                </script>
            </div>
                <div class="text-sm text-gray-500 mb-2">توزيع التخصصات (Top 5)</div>
                <div class="space-y-2" x-data>
                    <template x-for="row in spec" :key="row.speciality">
                        <div class="flex items-center gap-3">
                            <div class="w-28 text-xs text-gray-600" x-text="row.speciality || 'غير محدد'"></div>
                            <div class="flex-1 h-2 rounded-full bg-gray-200 dark:bg-gray-700">
                                <div class="h-2 rounded-full bg-gradient-to-r from-emerald-500 to-green-600" :style="`width: ${Math.min(100, (row.c / (spec[0]?.c||1)) * 100)}%`"></div>
                            </div>
                            <div class="w-10 text-xs text-gray-500" x-text="row.c"></div>
                        </div>
                    </template>
                </div>
            </div>
                </ul>
            </div>

            <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow md:col-span-3">
                <div class="text-sm text-gray-500">أعلى التخصصات</div>
                <div class="mt-2 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 text-sm">
                    @foreach($stats['by_speciality'] as $row)
                        <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/40 rounded px-3 py-2">
                            <span>{{ $row->speciality ?: 'غير محدد' }}</span>
                            <span class="text-gray-500">{{ $row->c }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

