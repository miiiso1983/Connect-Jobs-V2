<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\JobSeeker;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobSeekerBrowseController extends Controller
{
    public function index(Request $request): View|StreamedResponse
    {
        $this->authorizeAccess();

        $filters = [
            'q' => trim((string)$request->get('q','')),
            'province' => trim((string)$request->get('province','')),
            'districts' => (array)$request->input('districts', []),
            'specialities' => (array)$request->input('specialities', []),
            'gender' => trim((string)$request->get('gender','')),
            'own_car' => $request->get('own_car', ''),
            'skills' => trim((string)$request->get('skills','')),
            'status' => trim((string)$request->get('status','')),
            'created_from' => trim((string)$request->get('created_from','')),
            'created_to' => trim((string)$request->get('created_to','')),
        ];

        // Optional structured fields if exist
        $hasEducation = Schema::hasColumn('job_seekers', 'education_level');
        $hasExperience = Schema::hasColumn('job_seekers', 'experience_level');
        if ($hasEducation) { $filters['education_level'] = trim((string)$request->get('education_level','')); }
        if ($hasExperience) { $filters['experience_level'] = trim((string)$request->get('experience_level','')); }

        $perPage = (int) $request->get('per_page', 20);
        if ($perPage < 5) $perPage = 5; if ($perPage > 200) $perPage = 200;

        $seekersQ = JobSeeker::query()->with('user');

        // Text search across main fields and related user
        if ($filters['q'] !== '') {
            $q = $filters['q'];
            $seekersQ->where(function(Builder $qq) use ($q){
                $qq->where('full_name','like',"%$q%")
                   ->orWhere('job_title','like',"%$q%")
                   ->orWhere('speciality','like',"%$q%")
                   ->orWhere('skills','like',"%$q%")
                   ->orWhereHas('user', function($u) use ($q){
                       $u->where('email','like',"%$q%")
                         ->orWhere('name','like',"%$q%");
                   });
            });
        }

        if ($filters['province'] !== '') { $seekersQ->where('province', $filters['province']); }

        if (!empty($filters['districts'])) {
            $dArr = (array)$filters['districts'];
            $seekersQ->where(function(Builder $qq) use ($dArr){
                foreach ($dArr as $d) { $qq->orWhereJsonContains('districts', $d); }
            });
        }

        if (!empty($filters['specialities'])) {
            $sArr = (array)$filters['specialities'];
            $seekersQ->where(function(Builder $qq) use ($sArr){
                foreach ($sArr as $s) { $qq->orWhereJsonContains('specialities', $s); }
            });
        }

        if ($filters['gender'] !== '') { $seekersQ->where('gender', $filters['gender']); }
        if ($filters['own_car'] !== '') { $seekersQ->where('own_car', (bool)$filters['own_car']); }

        if ($filters['skills'] !== '') {
            $skills = preg_split('/[,\s]+/', strtolower($filters['skills']));
            $skills = array_values(array_filter(array_unique($skills), fn($v) => $v !== ''));
            if (!empty($skills)) {
                $seekersQ->where(function(Builder $qq) use ($skills){
                    foreach ($skills as $kw) { $qq->orWhere(DB::raw('LOWER(skills)'), 'like', "%$kw%"); }
                });
            }
        }

        if (!empty($filters['status'])) {
            $seekersQ->whereHas('user', function($u) use ($filters){
                $u->where('status', $filters['status']);
            });
        }

        if ($filters['created_from'] !== '' || $filters['created_to'] !== '') {
            $seekersQ->whereHas('user', function($u) use ($filters){
                if ($filters['created_from'] !== '') { $u->whereDate('created_at', '>=', $filters['created_from']); }
                if ($filters['created_to'] !== '') { $u->whereDate('created_at', '<=', $filters['created_to']); }
            });
        }

        if ($hasEducation && ($filters['education_level'] ?? '') !== '') {
            $seekersQ->where('education_level', $filters['education_level']);
        }
        if ($hasExperience && ($filters['experience_level'] ?? '') !== '') {
            $seekersQ->where('experience_level', $filters['experience_level']);
        }

        // Pagination or export
        if ($request->get('export') === 'csv') {
            if ($this->context() !== 'admin') {
                abort(403, 'CSV export is only available to admin.');
            }
            $filename = 'jobseekers_'.date('Ymd_His').'.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ];
            return response()->streamDownload(function() use ($seekersQ){
                $out = fopen('php://output', 'w');
                // UTF-8 BOM for Excel
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($out, ['ID','Full Name','Email','Province','Job Title','Specialities','Districts','Gender','Own Car','Created At']);
                $seekersQ->orderByDesc('id')->chunk(500, function($chunk) use ($out){
                    foreach ($chunk as $s) {
                        $spec = implode(', ', (array)($s->specialities ?? []));
                        $dist = implode(', ', (array)($s->districts ?? []));
                        fputcsv($out, [
                            $s->id,
                            (string)($s->full_name ?? ''),
                            (string)($s->user->email ?? ''),
                            (string)($s->province ?? ''),
                            (string)($s->job_title ?? ''),
                            $spec,
                            $dist,
                            (string)($s->gender ?? ''),
                            $s->own_car ? 'yes' : 'no',
                            (string)($s->user->created_at ?? ''),
                        ]);
                    }
                });
                fclose($out);
            }, $filename, $headers);
        }

        /** @var LengthAwarePaginator $seekers */
        $seekers = $seekersQ->orderByDesc('id')->paginate($perPage)->withQueryString();

        $provinces = \App\Models\MasterSetting::where('setting_type','province')->pluck('value');
        $specialities = \App\Models\MasterSetting::where('setting_type','speciality')->pluck('value');
        $educationLevels = \App\Models\MasterSetting::where('setting_type','education_level')->pluck('value');
        $experienceLevels = \App\Models\MasterSetting::where('setting_type','experience_level')->pluck('value');

        $context = $this->context(); // 'company' or 'admin'

        if ($request->ajax()) {
            return view('company.jobseekers._results', [
                'seekers' => $seekers,
                'context' => $context,
            ]);
        }

        return view('company.jobseekers.index', [
            'seekers' => $seekers,
            'filters' => $filters,
            'provinces' => $provinces,
            'specialities' => $specialities,
            'educationLevels' => $educationLevels,
            'experienceLevels' => $experienceLevels,
            'hasEducation' => $hasEducation,
            'hasExperience' => $hasExperience,
            'perPage' => $perPage,
            'context' => $context,
        ]);
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && in_array(($user->role ?? ''), ['company','admin'], true), 403);
        if ($user->role === 'company') {
            // company.approved middleware will also be applied by route, but double-check here if needed
            // no extra checks
        }
    }

    protected function context(): string
    {
        $user = auth()->user();
        return ($user && $user->role === 'admin') ? 'admin' : 'company';
    }
}

