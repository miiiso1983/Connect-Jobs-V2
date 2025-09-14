<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Mail\ProfileViewedMail;
use App\Models\Application;
use App\Models\Job;
use App\Models\JobSeeker;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class ApplicantShowController extends Controller
{
    public function __invoke(JobSeeker $jobSeeker): View
    {
        $company = Auth::user()->company;
        abort_if(!$company, 403);

        // Subscription expiry check
        $expiresAt = optional($company)->subscription_expires_at
            ?: (optional($company)->subscription_expiry ? \Carbon\Carbon::parse($company->subscription_expiry)->endOfDay() : null);
        if ($expiresAt && now()->greaterThan($expiresAt)) {
            return redirect()->route('company.dashboard')->with('status','\u0627\u0646\u062a\u0647\u0649 \u0627\u0634\u062a\u0631\u0627\u0643\u0643. \u0627\u0644\u0631\u062c\u0627\u0621 \u062a\u062c\u062f\u064a\u062f \u0627\u0644\u0627\u0634\u062a\u0631\u0627\u0643 \u0644\u0644\u0648\u0635\u0648\u0644 \u0625\u0644\u0649 \u0627\u0644\u0645\u062a\u0642\u062f\u0645\u064a\u0646.');
        }

        // Ensure this jobseeker has applied to at least one job owned by this company
        $application = Application::where('job_seeker_id', $jobSeeker->id)
            ->whereIn('job_id', Job::where('company_id', $company->id)->pluck('id'))
            ->latest('applied_at')->with('job')->first();
        abort_if(!$application, 403, 'Unauthorized');

        // Send profile-viewed mail to jobseeker (respect opt-out if present)
        $jsUser = $jobSeeker->user;
        if ($jsUser && data_get($jsUser, 'profile_view_notifications_opt_in', true) !== false) {
            try {
                Mail::to([$jsUser->email => $jobSeeker->full_name ?? $jsUser->name])
                    ->queue(new ProfileViewedMail($jobSeeker, $application->job, $company->company_name ?? 'Company'));
                // log queued email
                DB::table('email_logs')->insert([
                    'mailable' => \App\Mail\ProfileViewedMail::class,
                    'to_email' => $jsUser->email,
                    'to_name' => $jobSeeker->full_name ?? $jsUser->name,
                    'payload' => json_encode(['job_seeker_id' => $jobSeeker->id, 'job_id' => $application->job_id]),
                    'status' => 'queued',
                    'queued_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to queue ProfileViewedMail: '.$e->getMessage(), [
                    'job_seeker_id' => $jobSeeker->id,
                    'company_id' => $company->id,
                ]);
                try {
                    DB::table('email_logs')->insert([
                        'mailable' => \App\Mail\ProfileViewedMail::class,
                        'to_email' => $jsUser->email,
                        'to_name' => $jobSeeker->full_name ?? $jsUser->name,
                        'payload' => json_encode(['job_seeker_id' => $jobSeeker->id, 'job_id' => $application->job_id]),
                        'status' => 'failed',
                        'queued_at' => now(),
                    ]);
                } catch (\Throwable $ignore) {}
            }
        }

        // Render a simple profile view (minimal)
        return view('company.applicants.show', [
            'jobSeeker' => $jobSeeker,
            'application' => $application,
        ]);
    }
}

