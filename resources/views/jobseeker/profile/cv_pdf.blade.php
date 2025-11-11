<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>CV</title>
    <style>
        @page { margin: 24px 28px; }
        body { font-family: DejaVu Sans, Cairo, Arial, sans-serif; color: #111827; }
        .header { background:#2563eb; color:#fff; padding:12px 14px; border-radius:4px; }
        .name { font-size: 22px; font-weight: 700; margin: 0; }
        .title { font-size: 12px; margin: 2px 0 0 0; }
        .grid { display: grid; grid-template-columns: 2fr 5fr; grid-gap: 18px; margin-top: 14px; }
        .section-title { background:#2563eb; color:#fff; padding:6px 8px; border-radius:4px; font-size:13px; font-weight:700; margin:10px 0 6px; }
        .text { font-size: 11px; line-height: 1.7; margin: 0 0 6px 0; }
        .bullet { font-size: 11px; margin: 0 0 4px 0; }
        ul { padding:0 14px 0 0; margin:0; }
        .muted { color:#6b7280; }
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
            <div class="section-title">بيانات التواصل</div>
            @if(!empty(auth()->user()->email))<p class="text">البريد: {{ auth()->user()->email }}</p>@endif
            @if(!empty($js->phone))<p class="text">الهاتف: {{ $js->phone }}</p>@endif
            @if(!empty($js->province))<p class="text">الموقع: {{ $js->province }}</p>@endif

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
        </div>
        <div>
            @if(!empty($js->summary))
                <div class="section-title">الملخص</div>
                <p class="text">{!! nl2br(e($js->summary)) !!}</p>
            @endif

            @if(!empty($js->experiences))
                <div class="section-title">الخبرات</div>
                <ul>
                    @foreach(preg_split("/\r?\n/", trim($js->experiences)) as $line)
                        @if(strlen(trim($line)))<li class="bullet">{{ trim($line) }}</li>@endif
                    @endforeach
                </ul>
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

