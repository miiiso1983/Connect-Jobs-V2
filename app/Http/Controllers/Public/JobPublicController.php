<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\MasterSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobPublicController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string)$request->query('q', ''));
        $province = trim((string)$request->query('province', ''));
        $sort = $request->query('sort', 'latest');

        $jobsQ = Job::query()
            ->with('company')
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

        match ($sort) {
            'oldest' => $jobsQ->orderBy('id','asc'),
            default => $jobsQ->orderBy('id','desc'),
        };

        $jobs = $jobsQ->paginate(12)->withQueryString();

        $provinces = MasterSetting::where('setting_type','province')->pluck('value');
        if ($provinces->isEmpty()) {
            $provinces = collect(['بغداد','أربيل','البصرة','نينوى','النجف','كربلاء','الأنبار','ديالى','دهوك','السليمانية','صلاح الدين','كركوك','بابل','واسط','الديوانية','ميسان','المثنى','ذي قار']);
        }

        return view('public.jobs.index', compact('jobs','q','province','sort','provinces'));
    }

    public function show(Job $job): View
    {
        abort_if(!$job->approved_by_admin || $job->status !== 'open', 404);
        return view('public.jobs.show', compact('job'));
    }
}

