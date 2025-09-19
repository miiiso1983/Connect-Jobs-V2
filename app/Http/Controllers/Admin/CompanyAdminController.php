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
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:5000',
        ]);
        $email = $company->user?->email;
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return back()->with('status','لا يمكن الإرسال: بريد غير صالح.');
        }
        try {
            Mail::raw($request->input('message'), function($m) use ($email, $request){
                $m->to($email)->subject($request->input('subject'));
            });
            return back()->with('status','تم إرسال الرسالة بنجاح.');
        } catch (\Throwable $e) {
            return back()->with('status','تعذر الإرسال: '.$e->getMessage());
        }
    }
}

