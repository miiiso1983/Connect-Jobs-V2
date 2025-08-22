<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicantActionController extends Controller
{
    public function update(Request $request, Application $application): RedirectResponse
    {
        $request->validate(['action' => 'required|in:accept,reject,archive']);

        $companyId = Auth::user()->company?->id;
        if (!$application->job || ($application->job->company_id ?? null) !== $companyId) {
            abort(403, 'Unauthorized');
        }

        $new = match($request->input('action')){
            'accept' => 'accepted',
            'reject' => 'rejected',
            'archive' => 'archived',
        };
        $application->update(['status' => $new]);

        return back()->with('status', __('تم تنفيذ العملية بنجاح.'));
    }
}

