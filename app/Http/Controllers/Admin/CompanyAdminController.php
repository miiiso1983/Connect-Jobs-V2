<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class CompanyAdminController extends Controller
{
    public function index(): View
    {
        $companies = Company::with('user')->orderBy('id','desc')->get();
        return view('admin.companies.index', compact('companies'));
    }

    public function show(Company $company): View
    {
        $company->load(['user','jobs' => function($q){ $q->orderByDesc('id'); }]);
        $jobsOpen = $company->jobs->where('status','open')->count();
        $jobsPending = $company->jobs->where('approved_by_admin', false)->count();
        return view('admin.companies.show', compact('company','jobsOpen','jobsPending'));
    }

    public function approve(Company $company): RedirectResponse
    {
        // Activate both user and company
        $company->update(['status' => 'active']);
        $company->user?->update(['status' => 'active']);
        // Notify company user
        if ($company->user) {
            $company->user->notify(new \App\Notifications\GenericNotification(
                title: __('notifications.company_approved_title'),
                message: __('notifications.company_approved_body')
            ));
        }
        return back()->with('status','تمت الموافقة على الشركة.');
    }

    public function updateSubscription(Request $request, Company $company): RedirectResponse
    {
        $request->validate([
            'subscription_plan' => 'required|in:free,basic,pro,enterprise',
            'subscription_expiry' => 'nullable|date',
            'subscription_expires_at' => 'nullable|date',
        ]);

        $expiresAt = null;
        if ($request->filled('subscription_expires_at')) {
            $expiresAt = Carbon::parse($request->input('subscription_expires_at'));
        } elseif ($request->filled('subscription_expiry')) {
            $expiresAt = Carbon::parse($request->input('subscription_expiry'))->endOfDay();
        }

        $company->update(array_filter([
            'subscription_plan' => $request->input('subscription_plan'),
            'subscription_expiry' => $request->input('subscription_expiry'),
            'subscription_expires_at' => $expiresAt,
        ], fn($v) => !is_null($v)));

        return back()->with('status','تم تحديث خطة الاشتراك.');
    }

    public function toggleUser(Company $company): RedirectResponse
    {
        $user = $company->user;
        if (!$user) {
            return back()->with('status','لا يوجد مستخدم مرتبط بالشركة.');
        }
        $new = ($user->status === 'active') ? 'suspended' : 'active';
        $user->update(['status' => $new]);
        return back()->with('status', 'تم تحديث حالة مستخدم الشركة إلى: '.$new);
    }

    public function emailUser(Request $request, Company $company): RedirectResponse
    {
        $request->validate([
            'subject' => 'nullable|string|max:200',
            'message' => 'nullable|string|max:5000',
            'template' => 'nullable|string|in:approval_reminder,subscription_soon,general_notice',
        ]);

        $email = $company->user?->email;
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return back()->with('status','لا يمكن الإرسال: بريد غير صالح.');
        }

        // Canned templates
        $templates = [
            'approval_reminder' => [
                'subject' => 'تذكير موافقة الحساب',
                'body' => "مرحبا {{name}}،\n\nتمت مراجعة حساب شركتكم {{company}}. نرجو استكمال البيانات المطلوبة ليتم التفعيل النهائي.\n\nشكرا لكم."
            ],
            'subscription_soon' => [
                'subject' => 'تنبيه: اشتراككم يقترب من الانتهاء',
                'body' => "السادة {{company}}،\n\nاشتراككم يقترب من الانتهاء. يرجى التجديد لضمان استمرار نشر الوظائف والتقديمات.\n\nفريق Connect Job"
            ],
            'general_notice' => [
                'subject' => 'رسالة إدارية',
                'body' => "السادة {{company}}،\n\nهذه رسالة إدارية عامة من فريق الدعم.\n\nمع التحية"
            ],
        ];

        $tplKey = $request->input('template');
        $subject = trim((string)($request->input('subject') ?? ''));
        $body = trim((string)($request->input('message') ?? ''));
        if ($tplKey && isset($templates[$tplKey])) {
            if ($subject === '') { $subject = $templates[$tplKey]['subject']; }
            if ($body === '') { $body = $templates[$tplKey]['body']; }
        }
        // Replace placeholders
        $repl = [
            '{{name}}' => $company->user->name ?? 'عميلنا',
            '{{company}}' => $company->company_name ?? 'شركتكم',
        ];
        $subject = strtr($subject !== '' ? $subject : 'رسالة من المشرف', $repl);
        $body = strtr($body !== '' ? $body : '—', $repl);

        try {
            Mail::raw($body, function($m) use ($email, $subject){
                $m->to($email)->subject($subject);
            });
            return back()->with('status','تم إرسال الرسالة بنجاح.');
        } catch (\Throwable $e) {
            return back()->with('status','تعذر الإرسال: '.$e->getMessage());
        }
    }
}

