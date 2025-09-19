<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use App\Models\EmailTemplate;
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
        $emailTemplates = collect();
        if (class_exists(EmailTemplate::class) && Schema::hasTable('email_templates')) {
            $emailTemplates = EmailTemplate::where('scope','company')->where('active',true)->orderBy('name')->get();
        }
        return view('admin.companies.show', compact('company','jobsOpen','jobsPending','emailTemplates'));
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
            'template' => 'nullable|string|max:100',
        ]);

        $email = $company->user?->email;
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return back()->with('status','لا يمكن الإرسال: بريد غير صالح.');
        }

        $tplParam = (string) ($request->input('template') ?? '');
        $subject = trim((string)($request->input('subject') ?? ''));
        $body = trim((string)($request->input('message') ?? ''));

        // If template param is a numeric ID and table exists, load it
        if ($tplParam !== '' && ctype_digit($tplParam) && class_exists(EmailTemplate::class) && Schema::hasTable('email_templates')) {
            $tpl = EmailTemplate::where('id', (int)$tplParam)->where('scope','company')->where('active',true)->first();
            if ($tpl) {
                if ($subject === '') { $subject = $tpl->subject; }
                if ($body === '') { $body = $tpl->body; }
            }
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

