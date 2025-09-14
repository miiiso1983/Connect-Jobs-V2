<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class CompanyAdminController extends Controller
{
    public function index(): View
    {
        $companies = Company::with('user')->orderBy('id','desc')->get();
        return view('admin.companies.index', compact('companies'));
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
}

