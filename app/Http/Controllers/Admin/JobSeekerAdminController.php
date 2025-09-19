<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobSeeker;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $seekers = $seekersQ->orderByDesc('id')->paginate($perPage)->withQueryString();

        $totalSeekers = JobSeeker::count();
        $activeUsers = User::where('role','jobseeker')->where('status','active')->count();
        $suspendedUsers = User::where('role','jobseeker')->where('status','suspended')->count();

        $provinces = \App\Models\MasterSetting::where('setting_type','province')->pluck('value');

        return view('admin.jobseekers.index', compact('seekers','q','province','status','perPage','totalSeekers','activeUsers','suspendedUsers','provinces'));
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

