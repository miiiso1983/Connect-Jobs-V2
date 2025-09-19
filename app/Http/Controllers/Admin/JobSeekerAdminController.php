<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobSeeker;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class JobSeekerAdminController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $province = trim((string) $request->get('province', ''));
        $status = trim((string) $request->get('status', ''));
        $perPage = (int) $request->get('per_page', 20);
        if ($perPage < 5) $perPage = 5; if ($perPage > 200) $perPage = 200;
        $createdFrom = trim((string) $request->get('created_from', ''));
        $createdTo = trim((string) $request->get('created_to', ''));
        $lastSeenFrom = trim((string) $request->get('last_seen_from', ''));
        $lastSeenTo = trim((string) $request->get('last_seen_to', ''));
        $profileCompleted = trim((string) $request->get('profile_completed', ''));
        $hasCv = trim((string) $request->get('has_cv', ''));

        $seekersQ = JobSeeker::query()->with('user');
        if ($q !== '') {
            $seekersQ->where(function($qq) use ($q){
                $qq->where('full_name', 'like', "%$q%")
                   ->orWhere('job_title', 'like', "%$q%")
                   ->orWhere('speciality', 'like', "%$q%")
                   ->orWhereHas('user', function($u) use ($q){
                       $u->where('email','like',"%$q%")
                         ->orWhere('name','like',"%$q%");
                   });
            });
        }
        if ($province !== '') {
            $seekersQ->where('province', $province);
        }
        if ($status !== '') {
            // status lives on users table (active/suspended)
            $seekersQ->whereHas('user', function($u) use ($status){
                $u->where('status', $status);
            });
        }
        if ($profileCompleted !== '') {
            $seekersQ->where('profile_completed', $profileCompleted === '1');
        }
        if ($hasCv !== '') {
            if ($hasCv === '1') {
                $seekersQ->whereNotNull('cv_file')->where('cv_file', '!=', '');
            } else {
                $seekersQ->where(function($q){
                    $q->whereNull('cv_file')->orWhere('cv_file','');
                });
            }
        }
        if ($createdFrom !== '' || $createdTo !== '') {
            $seekersQ->whereHas('user', function($u) use ($createdFrom, $createdTo){
                if ($createdFrom !== '') { $u->whereDate('created_at', '>=', $createdFrom); }
                if ($createdTo !== '') { $u->whereDate('created_at', '<=', $createdTo); }
            });
        }
        if (Schema::hasTable('sessions') && ($lastSeenFrom !== '' || $lastSeenTo !== '')) {
            $fromEpoch = $lastSeenFrom !== '' ? strtotime($lastSeenFrom.' 00:00:00') : null;
            $toEpoch = $lastSeenTo !== '' ? strtotime($lastSeenTo.' 23:59:59') : null;
            $seekersQ->whereHas('user', function($u) use ($fromEpoch, $toEpoch){
                $sub = DB::table('sessions')->select('user_id');
                if (!is_null($fromEpoch)) { $sub->where('last_activity', '>=', $fromEpoch); }
                if (!is_null($toEpoch)) { $sub->where('last_activity', '<=', $toEpoch); }
                $sub->groupBy('user_id');
                $u->whereIn('id', $sub);
            });
        }

        $seekers = $seekersQ->orderByDesc('id')->paginate($perPage)->withQueryString();

        $totalSeekers = JobSeeker::count();
        $activeUsers = User::where('role','jobseeker')->where('status','active')->count();
        $suspendedUsers = User::where('role','jobseeker')->where('status','suspended')->count();

        $provinces = \App\Models\MasterSetting::where('setting_type','province')->pluck('value');

        // Last seen map for current page
        $lastSeenTs = collect();
        if (Schema::hasTable('sessions')) {
            $userIds = $seekers->pluck('user_id')->filter()->unique()->values();
            if ($userIds->isNotEmpty()) {
                $lastSeenTs = DB::table('sessions')
                    ->whereIn('user_id', $userIds)
                    ->select('user_id', DB::raw('MAX(last_activity) as ts'))
                    ->groupBy('user_id')
                    ->pluck('ts','user_id');
            }
        }

        return view('admin.jobseekers.index', [
            'seekers' => $seekers,
            'q' => $q,
            'province' => $province,
            'status' => $status,
            'perPage' => $perPage,
            'totalSeekers' => $totalSeekers,
            'activeUsers' => $activeUsers,
            'suspendedUsers' => $suspendedUsers,
            'provinces' => $provinces,
            'createdFrom' => $createdFrom,
            'createdTo' => $createdTo,
            'lastSeenFrom' => $lastSeenFrom,
            'lastSeenTo' => $lastSeenTo,
            'lastSeenTs' => $lastSeenTs,
            'profileCompleted' => $profileCompleted,
            'hasCv' => $hasCv,
        ]);
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
        // حذف ملفاته الأساسية فقط بدون لمس حسابات أخرى
        $js = JobSeeker::firstWhere('user_id', $user->id);
        if ($js) { $js->delete(); }
        $user->delete();
        return back()->with('status', 'تم حذف الباحث عن عمل.');
    }
}

