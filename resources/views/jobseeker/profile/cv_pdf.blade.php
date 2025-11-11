<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>CV</title>
    <style>
        @page { margin: 24px 28px; }
        body { font-family: DejaVu Sans, Cairo, Arial, sans-serif; color: #111827; }
        .header { background:#1f3a5f; color:#fff; padding:16px 18px; border-radius:4px; }
        .name { font-size: 28px; font-weight: 700; margin: 0; }
        .title { font-size: 12px; margin: 2px 0 0 0; }
        .grid { display: grid; grid-template-columns: 5fr 2fr; grid-gap: 24px; margin-top: 16px; }
        .section-title { color:#1f3a5f; padding:0 0 6px 0; border-bottom:1px solid #e5e7eb; border-radius:0; font-size:13px; font-weight:700; margin:10px 0 8px; }
        .text { font-size: 18px; line-height: 1.7; margin: 0 0 6px 0; }
        .bullet { font-size: 18px; margin: 0 0 4px 0; }
        ul { padding:0 14px 0 0; margin:0; }
        .muted { color:#6b7280; }
        .right-col { border-right:1px solid #e5e7eb; padding-right:18px; }

    </style>
</head>
<body>
    <div class="header">
        <p class="name">{{ ($js->full_name ?: auth()->user()->name) }}</p>
        @if(!empty($js->job_title))
            <p class="title">{{ $js->job_title }}</p>
        @endif
    </div>

    <div class="grid">
        <div>
            @php
                $img = $js->profile_image ?? '';
                $imgUrl = $img ? (preg_match('/^https?:\/\//', $img) ? $img : asset('storage/'.ltrim($img,'/'))) : '';
            @endphp
            @if(!empty($imgUrl))
                <img src="{{ $imgUrl }}" alt="avatar" style="width: 110px; height: 110px; object-fit: cover; border-radius: 999px; margin-bottom: 12px; border:4px solid #fff;"/>
            @endif

            @if(!empty($js->summary))
                <div class="section-title">نبذة عني</div>
                <p class="text">{!! nl2br(e($js->summary)) !!}</p>
            @endif

            @if(!empty($js->experiences))
                <div class="section-title">الخبرات المهنية</div>
                <ul>
                    @foreach(preg_split("/\r?\n/", trim($js->experiences)) as $line)
                        @if(strlen(trim($line)))<li class="bullet">{{ trim($line) }}</li>@endif
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="right-col">
            <div class="section-title">معلومات التواصل</div>
            @if(!empty(auth()->user()->email))<p class="text">البريد: {{ auth()->user()->email }}</p>@endif
            @if(!empty($js->phone))<p class="text">رقم الموبايل: {{ $js->phone }}</p>@endif
            @if(!empty($js->province))<p class="text">المحافظة: {{ $js->province }}</p>@endif
            <p class="text">امتلاك السيارة: {{ ($js->own_car ?? false) ? 'نعم' : 'لا' }}</p>

            @if(!empty($js->districts) && is_array($js->districts))
                <div class="section-title">المناطق</div>
                <ul>
                    @foreach($js->districts as $d)
                        @if(strlen(trim($d)))<li class="bullet">{{ trim($d) }}</li>@endif
                    @endforeach
                </ul>
            @endif

            @if(!empty($js->specialities) && is_array($js->specialities))
                <div class="section-title">التخصصات</div>
                <ul>
                    @foreach($js->specialities as $s)
                        @if(strlen(trim($s)))<li class="bullet">{{ trim($s) }}</li>@endif
                    @endforeach
                </ul>
            @endif

            @if(!empty($js->skills))
                <div class="section-title">المهارات</div>
                <ul>
                    @foreach(preg_split("/\r?\n/", trim($js->skills)) as $line)
                        @if(strlen(trim($line)))<li class="bullet">{{ trim($line) }}</li>@endif
                    @endforeach
                </ul>
            @endif

            @if(!empty($js->languages))
                <div class="section-title">اللغات</div>
                <ul>
                    @foreach(preg_split("/\r?\n/", is_string($js->languages) ? trim($js->languages) : implode("\n", (array)$js->languages)) as $line)
                        @if(strlen(trim($line)))<li class="bullet">{{ trim($line) }}</li>@endif
                    @endforeach
                </ul>
            @endif

            @if(!empty($js->job_title))
                <div class="section-title">






                    المسمى الوظيفي
</div>
                <p class="text">{{ $js->job_title }}</p>
                <hr style="border:none;border-top:1px solid #e5e7eb;margin:10px 0;"/>
            @endif




            @if(!empty($js->education_level))
                <div class="section-title">التعليم</div>
                <p class="text">{{ $js->education_level }}</p>
            @endif

            @if(!empty($js->qualifications))
                <div class="section-title">المؤهلات</div>
                <ul>
                    @foreach(preg_split("/\r?\n/", trim($js->qualifications)) as $line)
                        @if(strlen(trim($line)))<li class="bullet">{{ trim($line) }}</li>@endif
                    @endforeach
                </ul>
            @endif

            @if(!empty($js->speciality))
                <div class="section-title">التخصص</div>
                <p class="text">{{ $js->speciality }}</p>
            @endif
        </div>
    </div>

    <p class="text muted" style="margin-top: 16px;">تم إنشاء هذا الملف تلقائيًا من ملفك الشخصي في Connect Jobs.</p>
</body>
</html>

