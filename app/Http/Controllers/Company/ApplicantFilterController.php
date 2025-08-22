<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\JobSeeker;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicantFilterController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['job_id','job_title','province','districts','specialities','own_car','gender']);
        $filters = array_merge([
            'job_id' => null,
            'job_title' => null,
            'province' => null,
            'districts' => [],
            'specialities' => [],
            'own_car' => null,
            'gender' => null,
        ], $filters);

        // Load master settings for dropdowns
        $titles = \App\Models\MasterSetting::where('setting_type','job_title')->pluck('value');
        $provinces = \App\Models\MasterSetting::where('setting_type','province')->pluck('value');
        $specialities = \App\Models\MasterSetting::where('setting_type','speciality')->pluck('value');

        // Restrict to applicants who applied to this company's jobs only
        $companyId = auth()->user()->company?->id;
        $companyJobIds = \App\Models\Job::where('company_id', $companyId)->pluck('id');

        // If a job is selected, ensure it belongs to the company and narrow to it
        if (!empty($filters['job_id'])) {
            if (!$companyJobIds->contains((int)$filters['job_id'])) {
                abort(403, 'Unauthorized');
            }
            $companyJobIds = collect([(int)$filters['job_id']]);
        }

        $appliedSeekerIds = \App\Models\Application::whereIn('job_id', $companyJobIds)->pluck('job_seeker_id')->unique();

        $q = JobSeeker::query()->whereIn('id', $appliedSeekerIds->isNotEmpty() ? $appliedSeekerIds : [-1]);
        if (!empty($filters['job_title'])) $q->where('job_title', $filters['job_title']);
        if (!empty($filters['province'])) $q->where('province', $filters['province']);
        if (!empty($filters['districts']) && is_array($filters['districts'])) {
            $q->where(function($qq) use ($filters){
                foreach ($filters['districts'] as $d) {
                    $qq->orWhereJsonContains('districts', $d);
                }
            });
        }
        if (!empty($filters['specialities']) && is_array($filters['specialities'])) {
            $q->where(function($qq) use ($filters){
                foreach ($filters['specialities'] as $s) {
                    $qq->orWhereJsonContains('specialities', $s);
                }
            });
        }
        if (!empty($filters['gender'])) $q->where('gender', $filters['gender']);
        if (isset($filters['own_car']) && $filters['own_car'] !== '') $q->where('own_car', (bool)$filters['own_car']);

        $seekers = $q->get();

        // Calculate match percentage against provided filters (simple heuristic)
        $required = collect([]);
        if (!empty($filters['job_title'])) $required->push('job_title');
        if (!empty($filters['province'])) $required->push('province');
        if (!empty($filters['gender'])) $required->push('gender');
        if ($filters['own_car'] !== null && $filters['own_car'] !== '') $required->push('own_car');
        if (!empty($filters['districts']) && is_array($filters['districts'])) $required->push('districts');
        if (!empty($filters['specialities']) && is_array($filters['specialities'])) $required->push('specialities');

        $results = $seekers->filter(function(JobSeeker $s) use ($filters, $required){
            $score = 0; $total = max(1, $required->count());
            foreach ($required as $k) {
                if ($k==='own_car') {
                    if ((bool)$s->own_car === (bool)$filters['own_car']) $score++;
                } elseif ($k==='districts') {
                    $sd = (array)($s->districts ?? []);
                    $sel = (array)$filters['districts'];
                    if (count(array_intersect($sd, $sel)) > 0) $score++;
                } elseif ($k==='specialities') {
                    $ss = (array)($s->specialities ?? []);
                    $sel = (array)$filters['specialities'];
                    if (count(array_intersect($ss, $sel)) > 0) $score++;
                } else {
                    if (strcasecmp((string)($s->$k ?? ''), (string)$filters[$k])===0) $score++;
                }
            }
            $percentage = ($score / $total) * 100;
            $s->matching_percentage = round($percentage,2);
            return $percentage >= 50; // at least 50%
        })->values();

        // Jobs for the dropdown (of current company)
        $companyId = auth()->user()->company?->id;
        $jobs = \App\Models\Job::where('company_id',$companyId)->orderByDesc('id')->get(['id','title']);

        return view('company.applicants.index', compact('filters','results'))
            ->with(['titles'=>$titles,'provinces'=>$provinces,'specialities'=>$specialities,'jobs'=>$jobs])
            ->with(['applicants'=>$results]);
    }
}

