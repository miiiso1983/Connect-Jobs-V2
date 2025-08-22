<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


// Locale switcher
Route::get('/locale/{locale}', function(string $locale){
    if (!in_array($locale,['en','ar','ku'])) { $locale = 'en'; }
    session(['locale'=>$locale]);
    return back();
})->name('locale.switch');

// Landing: guests see landing, authenticated users go to their dashboards
Route::middleware(['setlocale'])->get('/', function () {
    $user = auth()->user();
    if ($user) {
        $dest = match($user->role ?? 'jobseeker'){
            'admin' => 'admin.dashboard',
            'company' => 'company.dashboard',
            default => 'jobseeker.dashboard',
        };
        return redirect()->route($dest);
    }
    return view('landing');
});

// Public jobs
Route::middleware('setlocale')->group(function(){
// Optional: GET logout that redirects to POST logout for convenience in old links
Route::middleware(['auth'])->get('/logout', function(){
    // Prefer POST for Laravel's CSRF protection, but support legacy GET by logging out and redirecting
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout.get');

    Route::get('/jobs', [\App\Http\Controllers\Public\JobPublicController::class,'index'])->name('jobs.index');
    Route::get('/jobs/{job}', [\App\Http\Controllers\Public\JobPublicController::class,'show'])->name('jobs.show');
});

// Generic dashboard redirects to role dashboards
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user) {
        $dest = match($user->role ?? 'jobseeker'){
            'admin' => 'admin.dashboard',
            'company' => 'company.dashboard',
            default => 'jobseeker.dashboard',
        };
        return redirect()->route($dest);
    }
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');
// Notifications
Route::middleware(['setlocale','auth'])->group(function(){
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class,'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class,'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class,'markAllRead'])->name('notifications.read_all');
});

// Public API for districts (used by forms)
Route::get('/districts', [\App\Http\Controllers\DistrictController::class, 'index'])->name('districts.index');


// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Auth routes (login/register/...) provided by Breeze
require __DIR__.'/auth.php';

// Admin area
Route::middleware(['setlocale','auth','role:admin'])->prefix('admin')->name('admin.')->group(function(){
    Route::get('/', [\App\Http\Controllers\Admin\AdminDashboardController::class, '__invoke'])->name('dashboard');

    // Companies management
    Route::get('/companies', [\App\Http\Controllers\Admin\CompanyAdminController::class, 'index'])->name('companies.index');
    Route::post('/companies/{company}/approve', [\App\Http\Controllers\Admin\CompanyAdminController::class, 'approve'])->name('companies.approve');
    Route::put('/companies/{company}/subscription', [\App\Http\Controllers\Admin\CompanyAdminController::class, 'updateSubscription'])->name('companies.subscription');

    // Jobs approvals
    Route::get('/jobs/pending', [\App\Http\Controllers\Admin\JobAdminController::class, 'pending'])->name('jobs.pending');
    Route::post('/jobs/{job}/approve', [\App\Http\Controllers\Admin\JobAdminController::class, 'approve'])->name('jobs.approve');
    Route::post('/jobs/{job}/reject', [\App\Http\Controllers\Admin\JobAdminController::class, 'reject'])->name('jobs.reject');

    // Master settings CRUD
    Route::get('/settings', [\App\Http\Controllers\Admin\MasterSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\Admin\MasterSettingController::class, 'store'])->name('settings.store');
    Route::put('/settings/{setting}', [\App\Http\Controllers\Admin\MasterSettingController::class, 'update'])->name('settings.update');
    Route::delete('/settings/{setting}', [\App\Http\Controllers\Admin\MasterSettingController::class, 'destroy'])->name('settings.destroy');
    Route::post('/settings/bulk', [\App\Http\Controllers\Admin\MasterSettingController::class, 'bulkStore'])->name('settings.bulk');
        Route::get('/settings/export', [\App\Http\Controllers\Admin\MasterSettingController::class, 'export'])->name('settings.export');
    // Districts admin
    Route::get('/districts', [\App\Http\Controllers\Admin\DistrictAdminController::class, 'index'])->name('districts.index');
    Route::post('/districts', [\App\Http\Controllers\Admin\DistrictAdminController::class, 'store'])->name('districts.store');
    Route::post('/districts/bulk', [\App\Http\Controllers\Admin\DistrictAdminController::class, 'bulkStore'])->name('districts.bulk');

    Route::delete('/districts/{district}', [\App\Http\Controllers\Admin\DistrictAdminController::class, 'destroy'])->name('districts.destroy');
    Route::get('/districts/export', [\App\Http\Controllers\Admin\DistrictAdminController::class, 'export'])->name('districts.export');
    Route::post('/districts/import', [\App\Http\Controllers\Admin\DistrictAdminController::class, 'import'])->name('districts.import');

        Route::post('/settings/import', [\App\Http\Controllers\Admin\MasterSettingController::class, 'import'])->name('settings.import');


    // Users enable/disable
    Route::put('/users/{user}/toggle', [\App\Http\Controllers\Admin\UserAdminController::class, 'toggleStatus'])->name('users.toggle');
});

// Company dashboard
Route::middleware(['setlocale','auth','role:company','company.approved'])->group(function(){
    Route::get('/company', \App\Http\Controllers\Company\CompanyDashboardController::class)->name('company.dashboard');
});
// Company jobs and applicants
Route::middleware(['setlocale','auth','role:company','company.approved'])->prefix('company')->name('company.')->group(function(){
    Route::get('/jobs', [\App\Http\Controllers\Company\CompanyJobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/create', [\App\Http\Controllers\Company\CompanyJobController::class, 'create'])->name('jobs.create');
    Route::post('/jobs', [\App\Http\Controllers\Company\CompanyJobController::class, 'store'])->name('jobs.store');
    Route::put('/jobs/{job}/toggle', [\App\Http\Controllers\Company\CompanyJobController::class, 'togglePublish'])->name('jobs.toggle');
    Route::get('/jobs/{job}', [\App\Http\Controllers\Company\CompanyJobController::class, 'show'])->middleware('job.owner')->name('jobs.show');

    Route::get('/jobs/{job}/edit', [\App\Http\Controllers\Company\CompanyJobController::class, 'edit'])->middleware('job.owner')->name('jobs.edit');
    Route::put('/jobs/{job}', [\App\Http\Controllers\Company\CompanyJobController::class, 'update'])->middleware('job.owner')->name('jobs.update');
    Route::delete('/jobs/{job}', [\App\Http\Controllers\Company\CompanyJobController::class, 'destroy'])->middleware('job.owner')->name('jobs.destroy');

    // Future edit/update/destroy routes will include 'job.owner' middleware to enforce ownership
    // Route::get('/jobs/{job}/edit', [\App\Http\Controllers\Company\CompanyJobController::class, 'edit'])->middleware('job.owner')->name('jobs.edit');
    // Route::put('/jobs/{job}', [\App\Http\Controllers\Company\CompanyJobController::class, 'update'])->middleware('job.owner')->name('jobs.update');
    // Route::delete('/jobs/{job}', [\App\Http\Controllers\Company\CompanyJobController::class, 'destroy'])->middleware('job.owner')->name('jobs.destroy');


    Route::get('/applicants', [\App\Http\Controllers\Company\ApplicantFilterController::class, 'index'])->name('applicants.index');
    Route::put('/applications/{application}', [\App\Http\Controllers\Company\ApplicantActionController::class, 'update'])->name('applications.update');

});


// Job seeker dashboard
Route::middleware(['setlocale','auth','role:jobseeker'])->group(function(){
    Route::get('/jobseeker', function(){ return view('dashboards.jobseeker'); })->name('jobseeker.dashboard');
});
// Jobseeker dashboard + profile + apply
Route::middleware(['setlocale','auth','role:jobseeker'])->prefix('jobseeker')->name('jobseeker.')->group(function(){
    Route::get('/', [\App\Http\Controllers\JobSeeker\ProfileController::class,'dashboard'])->name('dashboard');
    Route::get('/profile', [\App\Http\Controllers\JobSeeker\ProfileController::class,'edit'])->name('profile.edit');
    Route::post('/profile', [\App\Http\Controllers\JobSeeker\ProfileController::class,'update'])->name('profile.update');

    Route::post('/apply/{job}', [\App\Http\Controllers\JobSeeker\ApplyController::class,'apply'])->name('apply');
});

