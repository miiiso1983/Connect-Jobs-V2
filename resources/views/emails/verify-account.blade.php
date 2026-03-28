@extends('emails.layouts.base')

@section('content')
  <h3 style="margin-top:0;color:#4A00B8">مرحباً {{ $name ?? '' }}</h3>
  <p>يرجى تأكيد بريدك الإلكتروني لإكمال إنشاء الحساب.</p>
  @isset($verifyUrl)
    <p style="text-align:center;margin:24px 0"><a class="btn" href="{{ $verifyUrl }}">تفعيل الحساب</a></p>
  @endisset
  @isset($code)
    <p>رمز التفعيل الخاص بك:</p>
    <div style="font-size:28px;font-weight:bold;letter-spacing:6px;text-align:center;background:#f1f5f9;border-radius:8px;padding:12px 0;margin:12px 0">{{ $code }}</div>
  @endisset
  <p class="muted">إذا لم تقم بإنشاء هذا الحساب، يمكنك تجاهل هذه الرسالة.</p>
@endsection
