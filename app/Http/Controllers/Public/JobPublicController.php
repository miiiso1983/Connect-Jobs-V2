<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Job;
use App\Models\MasterSetting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JobPublicController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        // Restore last used filters if no query provided (and not clearing)
        if (!$request->boolean('clear') && count($request->query()) === 0) {
            $saved = $request->session()->get('jobs.filters', []);
            if (!empty($saved)) {
                return redirect()->route('jobs.index', $saved);
            }
        }
        // Clear saved filters
        if ($request->boolean('clear')) {
            $request->session()->forget('jobs.filters');
        }

        // Read current query params
        $q = trim((string)$request->query('q', ''));
        $province = trim((string)$request->query('province', ''));
        $sort = $request->query('sort', 'latest');
        $industry = trim((string)$request->query('industry', ''));
        $jobTitleFilter = trim((string)$request->query('job_title', ''));
        $companyId = (int) $request->query('company_id', 0);
        $companyName = trim((string)$request->query('company', ''));

        $jobsQ = Job::query()
            ->with('company')
            ->withCount('applications')
            ->where('approved_by_admin', true)
            ->where('status', 'open');

        if ($q !== '') {
            $jobsQ->where(function($qq) use ($q){
                $qq->where('title','like',"%{$q}%")
                   ->orWhere('description','like',"%{$q}%");
            });
        }
        if ($province !== '') {
            $jobsQ->where('province', $province);
        }
        if ($industry !== '') {
            $jobsQ->whereHas('company', function($cq) use ($industry){ $cq->where('industry', $industry); });
        }
        if ($companyId > 0) {
            $jobsQ->where('company_id', $companyId);
        } elseif ($companyName !== '') {
            $jobsQ->whereHas('company', function($cq) use ($companyName){ $cq->where('company_name','like',"%{$companyName}%"); });
        }
        if ($jobTitleFilter !== '') {
            // Filter by standardized job title value against free-text title
            $jobsQ->where('title','like',"%{$jobTitleFilter}%");
        }

        match ($sort) {
            'oldest' => $jobsQ->orderBy('id','asc'),
            default => $jobsQ->orderBy('id','desc'),
        };

        // Persist filters if provided
        $toSave = [
            'q' => $q,
            'province' => $province,
            'industry' => $industry,
            'job_title' => $jobTitleFilter,
            'company_id' => $companyId,
            'company' => $companyName,
            'sort' => $sort,
        ];
        $nonEmpty = array_filter($toSave, function($v){ return !(is_null($v) || $v === '' || $v === 0); });
        if (!empty($nonEmpty) || ($sort !== 'latest')) {
            $request->session()->put('jobs.filters', $toSave);
        }

        $jobs = $jobsQ->paginate(12)->withQueryString();

        // Filters data
        $provinces = MasterSetting::where('setting_type','province')->pluck('value');
        if ($provinces->isEmpty()) {
            $provinces = collect(['بغداد','أربيل','البصرة','نينوى','النجف','كربلاء','الأنبار','ديالى','دهوك','السليمانية','صلاح الدين','كركوك','بابل','واسط','الديوانية','ميسان','المثنى','ذي قار']);
        }
        $jobTitles = MasterSetting::where('setting_type','job_title')->pluck('value');
        if ($jobTitles->isEmpty()) { $jobTitles = collect(['صيدلاني','صيدلاني مساعد','مندوب مبيعات طبية','فني مختبر','محاسب','سكرتير/ة']); }
        $industries = Company::query()->whereNotNull('industry')->distinct()->orderBy('industry')->pluck('industry');
        $companies = Company::query()
            ->whereHas('jobs', function($q){ $q->where('approved_by_admin',true)->where('status','open'); })
            ->orderBy('company_name')
            ->get(['id','company_name']);

        // Saved jobs for current user (jobseeker)
        $savedIds = [];
        if (Auth::check() && Auth::user()->role === 'jobseeker') {
            $savedIds = DB::table('saved_jobs')->where('user_id', Auth::id())->pluck('job_id')->all();
        }

        $breadcrumbsJson = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'الصفحة الرئيسية', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'الوظائف المتاحة', 'item' => url('/jobs')],
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return view('public.jobs.index', compact('jobs','q','province','sort','provinces','industries','jobTitles','industry','jobTitleFilter','savedIds','companies','companyId','companyName','breadcrumbsJson'));
    }

    public function show(Job $job): View
    {
        abort_if(!$job->approved_by_admin || $job->status !== 'open', 404);

        // Ensure company relation is available for SEO schema
        $job->load('company');

        $isSaved = false;
        if (Auth::check() && Auth::user()->role === 'jobseeker') {
            $isSaved = DB::table('saved_jobs')
                ->where('user_id', Auth::id())
                ->where('job_id', $job->id)
                ->exists();
        }

        return view('public.jobs.show', compact('job','isSaved'));
    }

    public function save(Job $job)
    {
        DB::table('saved_jobs')->updateOrInsert([
            'user_id' => Auth::id(),
            'job_id' => $job->id,
        ], [
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return back()->with('status','تم حفظ الوظيفة.');
    }

    public function unsave(Job $job)
    {
        DB::table('saved_jobs')->where('user_id', Auth::id())->where('job_id', $job->id)->delete();
        return back()->with('status','تم إلغاء الحفظ.');
    }
}

