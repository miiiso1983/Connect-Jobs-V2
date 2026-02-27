<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CvVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CvVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $status = trim((string) $request->get('status', 'pending'));
        if ($status === '') { $status = 'pending'; }

        $q = trim((string) $request->get('q', ''));

        $requestsQ = CvVerificationRequest::query()->with(['jobSeeker.user', 'adminUser']);

        if ($status !== 'all') {
            $requestsQ->where('status', $status);
        }

        if ($q !== '') {
            $requestsQ->where(function ($qq) use ($q) {
                $qq->whereHas('jobSeeker', function ($js) use ($q) {
                    $js->where(function ($jqq) use ($q) {
                        $jqq->where('full_name', 'like', "%$q%")
                            ->orWhere('job_title', 'like', "%$q%");
                    });
                })->orWhereHas('jobSeeker.user', function ($u) use ($q) {
                    $u->where('email', 'like', "%$q%")
                        ->orWhere('name', 'like', "%$q%");
                });
            });
        }

        $requests = $requestsQ->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.cv-verifications.index', compact('requests', 'status', 'q'));
    }

    public function approve(Request $request, CvVerificationRequest $cvVerificationRequest): RedirectResponse
    {
        $request->validate(['admin_notes' => 'nullable|string|max:2000']);

        $cvVerificationRequest->update([
            'status' => CvVerificationRequest::STATUS_APPROVED,
            'admin_user_id' => Auth::id(),
            'admin_notes' => $request->input('admin_notes'),
            'decided_at' => now(),
        ]);

        $cvVerificationRequest->jobSeeker?->update(['cv_verified' => true]);

        return back()->with('status', 'تم توثيق السيرة الذاتية بنجاح.');
    }

    public function reject(Request $request, CvVerificationRequest $cvVerificationRequest): RedirectResponse
    {
        $request->validate(['admin_notes' => 'required|string|max:2000']);

        $cvVerificationRequest->update([
            'status' => CvVerificationRequest::STATUS_REJECTED,
            'admin_user_id' => Auth::id(),
            'admin_notes' => $request->input('admin_notes'),
            'decided_at' => now(),
        ]);

        $cvVerificationRequest->jobSeeker?->update(['cv_verified' => false]);

        return back()->with('status', 'تم رفض طلب التوثيق.');
    }
}

