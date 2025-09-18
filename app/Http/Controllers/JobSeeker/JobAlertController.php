<?php

namespace App\Http\Controllers\JobSeeker;

use App\Http\Controllers\Controller;
use App\Models\JobAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class JobAlertController extends Controller
{
    public function index(): View
    {
        $alerts = JobAlert::where('user_id', Auth::id())
            ->orderByDesc('id')
            ->get();
        return view('jobseeker.alerts.index', compact('alerts'));
    }

    public function store(Request $request)
    {
        // role is enforced by route middleware

        $data = $request->validate([
            'q' => ['nullable','string','max:255'],
            'province' => ['nullable','string','max:255'],
            'industry' => ['nullable','string','max:255'],
            'job_title' => ['nullable','string','max:255'],
        ]);
        $data['frequency'] = 'weekly';
        $data['channel'] = 'email';
        $data['enabled'] = true;
        $data['user_id'] = Auth::id();

        // Find existing alert with same filters
        $existing = JobAlert::where('user_id', Auth::id())
            ->where('q', $data['q'] ?? null)
            ->where('province', $data['province'] ?? null)
            ->where('industry', $data['industry'] ?? null)
            ->where('job_title', $data['job_title'] ?? null)
            ->first();

        if ($existing) {
            if (!$existing->enabled) {
                $existing->enabled = true;
                $existing->save();
            }
            return Redirect::back()->with('status', 'تم تفعيل التنبيه الموجود مسبقاً.');
        }

        JobAlert::create($data);
        return Redirect::back()->with('status', 'تم حفظ التنبيه الأسبوعي عبر البريد.');
    }

    public function toggle(Request $request, int $id)
    {
        $alert = JobAlert::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $alert->enabled = !$alert->enabled;
        $alert->save();
        return Redirect::back()->with('status', $alert->enabled ? 'تم تفعيل التنبيه.' : 'تم إيقاف التنبيه.');
    }

    public function destroy(int $id)
    {
        JobAlert::where('id', $id)->where('user_id', Auth::id())->delete();
        return Redirect::back()->with('status', 'تم حذف التنبيه.');
    }
}

