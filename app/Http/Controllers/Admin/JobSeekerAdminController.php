<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobSeeker;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobSeekerAdminController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filtersFromRequest($request);
        $seekers = $this->buildFilteredQuery($filters)
            ->orderByDesc('id')
            ->paginate($filters['perPage'])
            ->withQueryString();

        $totalSeekers = JobSeeker::count();
        $activeUsers = User::where('role','jobseeker')->where('status','active')->count();
        $suspendedUsers = User::where('role','jobseeker')->where('status','suspended')->count();

        $completedCount = null;
        $cvCount = null;
        if (Schema::hasTable('job_seekers')) {
            if ($this->hasJobSeekerColumn('profile_completed')) {
                $completedCount = JobSeeker::where('profile_completed', true)->count();
            }
            if ($this->hasJobSeekerColumn('cv_file')) {
                $cvCount = JobSeeker::whereNotNull('cv_file')->where('cv_file','!=','')->count();
            }
        }

        $provinces = \App\Models\MasterSetting::where('setting_type','province')->pluck('value');

        // Last seen map for current page
        $lastSeenTs = collect();
        if ($this->hasSessionsTable()) {
            $userIds = $seekers->pluck('user_id')->filter()->unique()->values();
            if ($userIds->isNotEmpty()) {
                $lastSeenTs = DB::table('sessions')
                    ->whereIn('user_id', $userIds)
                    ->select('user_id', DB::raw('MAX(last_activity) as ts'))
                    ->groupBy('user_id')
                    ->pluck('ts','user_id');
            }
        }

        return view('admin.jobseekers.index', array_merge($filters, [
            'seekers' => $seekers,
            'totalSeekers' => $totalSeekers,
            'activeUsers' => $activeUsers,
            'suspendedUsers' => $suspendedUsers,
            'provinces' => $provinces,
            'lastSeenTs' => $lastSeenTs,
            'completedCount' => $completedCount,
            'cvCount' => $cvCount,
        ]));
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filtersFromRequest($request);
        $seekersQ = $this->buildFilteredQuery($filters);
        $hasSessionsTable = $this->hasSessionsTable();
        $filename = 'jobseekers_'.date('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($seekersQ, $hasSessionsTable) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($out, [
                'ID',
                'Full Name',
                'Account Name',
                'Email',
                'Mobile Number',
                'Province',
                'Job Title',
                'Speciality',
                'Gender',
                'Status',
                'Profile Completed',
                'CV Uploaded',
                'CV Verified',
                'University',
                'College',
                'Department',
                'Graduation Year',
                'Fresh Graduate',
                'Created At',
                'Last Seen At',
            ]);

            $seekersQ->orderByDesc('id')->chunk(500, function ($chunk) use ($out, $hasSessionsTable) {
                $lastSeenTs = collect();

                if ($hasSessionsTable) {
                    $userIds = $chunk->pluck('user_id')->filter()->unique()->values();
                    if ($userIds->isNotEmpty()) {
                        $lastSeenTs = DB::table('sessions')
                            ->whereIn('user_id', $userIds)
                            ->select('user_id', DB::raw('MAX(last_activity) as ts'))
                            ->groupBy('user_id')
                            ->pluck('ts', 'user_id');
                    }
                }

                foreach ($chunk as $seeker) {
                    $speciality = array_filter(array_merge(
                        [(string) ($seeker->speciality ?? '')],
                        array_map('strval', (array) ($seeker->specialities ?? [])),
                    ));

                    $lastSeenAt = $lastSeenTs[$seeker->user_id] ?? null;

                    fputcsv($out, [
                        $seeker->id,
                        (string) ($seeker->full_name ?? ''),
                        (string) ($seeker->user->name ?? ''),
                        (string) ($seeker->user->email ?? ''),
                        (string) ($seeker->user->whatsapp_number ?? ''),
                        (string) ($seeker->province ?? ''),
                        (string) ($seeker->job_title ?? ''),
                        implode(' | ', $speciality),
                        (string) ($seeker->gender ?? ''),
                        (string) ($seeker->user->status ?? ''),
                        ($seeker->profile_completed ?? false) ? 'Yes' : 'No',
                        filled($seeker->cv_file ?? null) ? 'Yes' : 'No',
                        ($seeker->cv_verified ?? false) ? 'Yes' : 'No',
                        (string) ($seeker->university_name ?? ''),
                        (string) ($seeker->college_name ?? ''),
                        (string) ($seeker->department_name ?? ''),
                        (string) ($seeker->graduation_year ?? ''),
                        ($seeker->is_fresh_graduate ?? false) ? 'Yes' : 'No',
                        (string) ($seeker->user->created_at ?? ''),
                        $lastSeenAt ? \Carbon\Carbon::createFromTimestamp($lastSeenAt)->toDateTimeString() : '',
                    ]);
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filtersFromRequest(Request $request): array
    {
        $perPage = (int) $request->get('per_page', 20);
        if ($perPage < 5) {
            $perPage = 5;
        }
        if ($perPage > 200) {
            $perPage = 200;
        }

        return [
            'q' => trim((string) $request->get('q', '')),
            'province' => trim((string) $request->get('province', '')),
            'status' => trim((string) $request->get('status', '')),
            'perPage' => $perPage,
            'createdFrom' => trim((string) $request->get('created_from', '')),
            'createdTo' => trim((string) $request->get('created_to', '')),
            'lastSeenFrom' => trim((string) $request->get('last_seen_from', '')),
            'lastSeenTo' => trim((string) $request->get('last_seen_to', '')),
            'profileCompleted' => trim((string) $request->get('profile_completed', '')),
            'hasCv' => trim((string) $request->get('has_cv', '')),
            'universityName' => trim((string) $request->get('university_name', '')),
            'collegeName' => trim((string) $request->get('college_name', '')),
            'departmentName' => trim((string) $request->get('department_name', '')),
            'graduationYear' => trim((string) $request->get('graduation_year', '')),
            'isFreshGraduate' => (string) $request->get('is_fresh_graduate', ''),
            'cvVerified' => (string) $request->get('cv_verified', ''),
        ];
    }

    private function buildFilteredQuery(array $filters): Builder
    {
        $seekersQ = JobSeeker::query()->with('user');

        if ($filters['q'] !== '') {
            $seekersQ->where(function ($qq) use ($filters) {
                $qq->where('full_name', 'like', "%{$filters['q']}%")
                    ->orWhere('job_title', 'like', "%{$filters['q']}%")
                    ->orWhere('speciality', 'like', "%{$filters['q']}%")
                    ->orWhereHas('user', function ($u) use ($filters) {
                        $u->where('email', 'like', "%{$filters['q']}%")
                            ->orWhere('name', 'like', "%{$filters['q']}%");
                    });
            });
        }

        if ($filters['province'] !== '') {
            $seekersQ->where('province', $filters['province']);
        }

        if ($filters['status'] !== '') {
            $seekersQ->whereHas('user', function ($u) use ($filters) {
                $u->where('status', $filters['status']);
            });
        }

        if ($filters['profileCompleted'] !== '' && $this->hasJobSeekerColumn('profile_completed')) {
            $seekersQ->where('profile_completed', $filters['profileCompleted'] === '1');
        }

        if ($filters['hasCv'] !== '' && $this->hasJobSeekerColumn('cv_file')) {
            if ($filters['hasCv'] === '1') {
                $seekersQ->whereNotNull('cv_file')->where('cv_file', '!=', '');
            } else {
                $seekersQ->where(function ($q) {
                    $q->whereNull('cv_file')->orWhere('cv_file', '');
                });
            }
        }

        if ($filters['createdFrom'] !== '' || $filters['createdTo'] !== '') {
            $seekersQ->whereHas('user', function ($u) use ($filters) {
                if ($filters['createdFrom'] !== '') {
                    $u->whereDate('created_at', '>=', $filters['createdFrom']);
                }
                if ($filters['createdTo'] !== '') {
                    $u->whereDate('created_at', '<=', $filters['createdTo']);
                }
            });
        }

        if ($this->hasSessionsTable() && ($filters['lastSeenFrom'] !== '' || $filters['lastSeenTo'] !== '')) {
            $fromEpoch = $filters['lastSeenFrom'] !== '' ? strtotime($filters['lastSeenFrom'].' 00:00:00') : null;
            $toEpoch = $filters['lastSeenTo'] !== '' ? strtotime($filters['lastSeenTo'].' 23:59:59') : null;

            $seekersQ->whereHas('user', function ($u) use ($fromEpoch, $toEpoch) {
                $sub = DB::table('sessions')->select('user_id');
                if (! is_null($fromEpoch)) {
                    $sub->where('last_activity', '>=', $fromEpoch);
                }
                if (! is_null($toEpoch)) {
                    $sub->where('last_activity', '<=', $toEpoch);
                }
                $sub->groupBy('user_id');

                $u->whereIn('id', $sub);
            });
        }

        if ($filters['universityName'] !== '' && $this->hasJobSeekerColumn('university_name')) {
            $seekersQ->where('university_name', 'like', "%{$filters['universityName']}%");
        }
        if ($filters['collegeName'] !== '' && $this->hasJobSeekerColumn('college_name')) {
            $seekersQ->where('college_name', 'like', "%{$filters['collegeName']}%");
        }
        if ($filters['departmentName'] !== '' && $this->hasJobSeekerColumn('department_name')) {
            $seekersQ->where('department_name', 'like', "%{$filters['departmentName']}%");
        }
        if ($filters['graduationYear'] !== '' && $this->hasJobSeekerColumn('graduation_year')) {
            $seekersQ->where('graduation_year', (int) $filters['graduationYear']);
        }
        if ($filters['isFreshGraduate'] !== '' && $this->hasJobSeekerColumn('is_fresh_graduate')) {
            $seekersQ->where('is_fresh_graduate', $filters['isFreshGraduate'] === '1');
        }
        if ($filters['cvVerified'] !== '' && $this->hasJobSeekerColumn('cv_verified')) {
            $seekersQ->where('cv_verified', $filters['cvVerified'] === '1');
        }

        return $seekersQ;
    }

    private function hasJobSeekerColumn(string $column): bool
    {
        static $cache = [];

        return $cache[$column] ??= Schema::hasColumn('job_seekers', $column);
    }

    private function hasSessionsTable(): bool
    {
        static $hasSessions = null;

        return $hasSessions ??= Schema::hasTable('sessions');
    }

    /**
     * Update admin notes for a job seeker
     */
    public function updateNotes(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        $jobSeeker = JobSeeker::find($id) ?? JobSeeker::where('user_id', $id)->firstOrFail();
        $jobSeeker->update(['admin_notes' => $request->input('admin_notes')]);

        return back()->with('status', 'تم حفظ الملاحظات بنجاح.');
    }

    public function toggle(User $user): RedirectResponse
    {
        abort_unless($user->role === 'jobseeker', 404);
        $new = ($user->status === 'active') ? 'suspended' : 'active';
        $user->update(['status' => $new]);
        return back()->with('status', 'تم تحديث حالة المستخدم إلى: '.$new);
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->role === 'jobseeker', 404);
        // حذف الطلبات والملفات المرتبطة قبل حذف الحساب
        $js = JobSeeker::firstWhere('user_id', $user->id);
        if ($js) {
            try { \App\Models\Application::where('job_seeker_id', $js->id)->delete(); } catch (\Throwable $e) { /* ignore */ }
            $js->delete();
        }
        $user->delete();
        return back()->with('status', 'تم حذف الباحث عن عمل.');
    }
}

