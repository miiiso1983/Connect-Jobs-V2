<?php

namespace App\Http\Controllers\JobSeeker;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobSeeker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;


class ProfileController extends Controller
{
    public function dashboard(): View
    {
        return view('jobseeker.dashboard');
    }

    public function edit(): View
    {
        $js = JobSeeker::firstWhere('user_id', Auth::id());
        $titles = \App\Models\MasterSetting::where('setting_type','job_title')->pluck('value');
        if ($titles->isEmpty()) {
            $titles = collect(['صيدلاني','صيدلاني مساعد','مندوب مبيعات طبية','فني مختبر','محاسب','سكرتير/ة']);
        }
        $provinces = \App\Models\MasterSetting::where('setting_type','province')->pluck('value');
        if ($provinces->isEmpty()) {
            $provinces = collect(['بغداد','أربيل','البصرة','نينوى','النجف','كربلاء','الأنبار','ديالى','دهوك','السليمانية','صلاح الدين','كركوك','بابل','واسط','الديوانية','ميسان','المثنى','ذي قار']);
        }
        $specialities = \App\Models\MasterSetting::where('setting_type','speciality')->pluck('value');
        if ($specialities->isEmpty()) { $specialities = collect(['صيدلة','طب','تمريض','مبيعات','محاسبة','إدارة']); }
        return view('jobseeker.profile.edit', compact('js','titles','provinces','specialities'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'full_name' => 'required|string|max:150',
            'province' => 'required|string|max:100',
            'districts' => 'nullable|array',
            'districts.*' => 'string|max:150',
            'job_title' => 'nullable|string|max:150',
            'specialities' => 'nullable|array',
            'specialities.*' => 'string|max:150',
            'gender' => 'nullable|in:male,female',
            'own_car' => 'nullable|boolean',
            'summary' => 'nullable|string',
            'qualifications' => 'nullable|string',
            'experiences' => 'nullable|string',
            'languages' => 'nullable|string',
            'skills' => 'nullable|string',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'profile_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048|dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000',
        ]);

        $js = JobSeeker::firstOrCreate(['user_id'=>Auth::id()], [
            'full_name' => $request->full_name,
            'province' => $request->province,
        ]);

        $cvPath = $js->cv_file;
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('cv','public');
        }
        $imagePath = $js->profile_image;
        if ($request->hasFile('profile_image')) {
            try {
                $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                $image = $manager->read($request->file('profile_image')->getPathname())
                    ->scaleDown(width: 800, height: 800);
                Storage::disk('public')->makeDirectory('profile-images');

                $base = 'profile-images/' . uniqid('img_');
                $main = $base . '.webp';
                $sm = $base . '_sm.webp';
                $md = $base . '_md.webp';
                $lg = $base . '_lg.webp';

                $image->toWebp(quality: 82)->save(storage_path('app/public/' . $main));
                try {
                    $image->clone()->scaleDown(width: 160, height: 160)->toWebp(quality: 82)->save(storage_path('app/public/' . $sm));
                    $image->clone()->scaleDown(width: 320, height: 320)->toWebp(quality: 82)->save(storage_path('app/public/' . $md));
                    $image->clone()->scaleDown(width: 640, height: 640)->toWebp(quality: 82)->save(storage_path('app/public/' . $lg));
                } catch (\Throwable $e) {}

                $imagePath = $main;
            } catch (\Throwable $e) {
                $imagePath = $request->file('profile_image')->store('profile-images', 'public');
            }
        }

        $js->update([
            'full_name' => $request->full_name,
            'province' => $request->province,
            'districts' => $request->input('districts', []),
            'job_title' => $request->job_title,
            'speciality' => $request->speciality,
            'specialities' => $request->input('specialities', []),
            'gender' => $request->gender,
            'own_car' => (bool)$request->own_car,
            'summary' => $request->summary,
            'qualifications' => $request->qualifications,
            'experiences' => $request->experiences,
            'languages' => $request->input('languages', []),
            'skills' => $request->skills,
            'cv_file' => $cvPath,
            'profile_image' => $imagePath,
            'profile_completed' => true,
        ]);

        return back()->with('status','تم تحديث البروفايل بنجاح.');
    }
}

