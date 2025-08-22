<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\MasterSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CompanyJobController extends Controller
{
    public function index(): View
    {
        $companyId = Auth::user()->company?->id;
        $jobs = Job::where('company_id', $companyId)->orderByDesc('id')->get();
        return view('company.jobs.index', compact('jobs'));
    }

    public function create(): View
    {
        $titles = MasterSetting::where('setting_type','job_title')->pluck('value');
        $provinces = MasterSetting::where('setting_type','province')->pluck('value');
        $specialities = MasterSetting::where('setting_type','speciality')->pluck('value');
        return view('company.jobs.create', compact('titles','provinces','specialities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'province' => 'required|string|max:100',
            'districts' => 'nullable|array',
            'districts.*' => 'string|max:150',
            'jd_file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        $companyId = Auth::user()->company?->id;
        $path = null;
        if ($request->hasFile('jd_file')) {
            $path = $request->file('jd_file')->store('jd','public');
        }
        $job = Job::create([
            'company_id' => $companyId,
            'title' => $request->title,
            'description' => $request->description,
            'requirements' => $request->requirements,
            'province' => $request->province,
            'districts' => $request->input('districts', []),
            'status' => 'draft',
            'approved_by_admin' => false,
            'jd_file' => $path,
        ]);

        // Dispatch job alerts asynchronously
        \App\Jobs\SendJobAlerts::dispatch($job);

        return redirect()->route('company.jobs.index')->with('status','تم إنشاء الوظيفة وستظهر بعد موافقة الإدارة، وتم إرسال إخطار للباحثين المناسبين.');
    }

    public function togglePublish(Job $job): RedirectResponse
    {
        // Enforce ownership: only the company that owns the job can toggle it
        $companyId = Auth::user()->company?->id;
        if ($job->company_id !== $companyId) {
            abort(403, 'Unauthorized');
        }
        // Allow toggling only after admin approval
        if (!$job->approved_by_admin) {
            return back()->with('status','لا يمكن النشر قبل موافقة الإدارة.');
        }
        $next = $job->status === 'open' ? 'paused' : 'open';
        $job->update(['status' => $next]);
        // Notify company user itself (optional) or admin
        auth()->user()?->notify(new \App\Notifications\GenericNotification(
            title: __('notifications.job_publish_toggled_title'),
            message: $next==='open' ? __('notifications.job_published_body') : __('notifications.job_paused_body')
        ));
        return back()->with('status','تم تغيير حالة النشر.');
    }

    public function show(Job $job): View
    {
        $this->authorize('manage', $job);
        $job->loadCount('applications');
        $latestApplicants = \App\Models\Application::with('jobSeeker')
            ->where('job_id',$job->id)->orderByDesc('applied_at')->take(5)->get();
        // Basic stats for charts/cards
        $stats = [
            'avg_match' => (float) (\App\Models\Application::where('job_id',$job->id)->avg('matching_percentage') ?? 0),
            'by_province' => \App\Models\JobSeeker::select('province', \DB::raw('COUNT(*) as c'))
                ->whereIn('id', \App\Models\Application::where('job_id',$job->id)->pluck('job_seeker_id'))
                ->groupBy('province')->orderByDesc('c')->take(5)->get(),
            'by_speciality' => \App\Models\JobSeeker::select('speciality', \DB::raw('COUNT(*) as c'))
                ->whereIn('id', \App\Models\Application::where('job_id',$job->id)->pluck('job_seeker_id'))
                ->groupBy('speciality')->orderByDesc('c')->take(5)->get(),
        ];
        return view('company.jobs.show', compact('job','latestApplicants','stats'));
    }


    public function edit(Job $job): View
    {
        $this->authorize('manage', $job);
        $titles = MasterSetting::where('setting_type','job_title')->pluck('value');
        $provinces = MasterSetting::where('setting_type','province')->pluck('value');
        $specialities = MasterSetting::where('setting_type','speciality')->pluck('value');
        return view('company.jobs.edit', compact('job','titles','provinces','specialities'));
    }

    public function update(Request $request, Job $job): RedirectResponse
    {
        $this->authorize('manage', $job);
        $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'province' => 'required|string|max:100',
            'districts' => 'nullable|array',
            'districts.*' => 'string|max:150',
            'jd_file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);
        $data = $request->only(['title','description','requirements','province']);
        $data['districts'] = $request->input('districts', []);
        if ($request->hasFile('jd_file')) {
            $data['jd_file'] = $request->file('jd_file')->store('jd','public');
        }
        $job->update($data);
        return redirect()->route('company.jobs.index')->with('status','تم تحديث الوظيفة');
    }

    public function destroy(Job $job): RedirectResponse
    {
        $this->authorize('manage', $job);
        if ($job->status === 'open') {
            return back()->with('status','لا يمكن حذف وظيفة منشورة. الرجاء إيقاف النشر أولاً.');
        }
        $job->delete();
        return redirect()->route('company.jobs.index')->with('status','تم حذف الوظيفة');
    }

}

