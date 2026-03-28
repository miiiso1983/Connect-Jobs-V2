@extends('emails.layouts.base')

@section('content')
  <h3 style="margin-top:0;color:#4A00B8">مرحباً {{ $name ?? '' }}</h3>
  <p>هذا رمز التفعيل الخاص بك، صالح لمدة 15 دقيقة:</p>
  <div style="font-size:28px;font-weight:bold;letter-spacing:6px;text-align:center;background:#f1f5f9;border-radius:8px;padding:12px 0;margin:12px 0">{{ $code }}</div>
  <p class="muted">إذا لم تقم بطلب هذا الرمز، يمكنك تجاهل هذه الرسالة.</p>
@endsection

