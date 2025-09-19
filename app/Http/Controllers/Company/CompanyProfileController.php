<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Storage;

class CompanyProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $company = $user->company;
        if (!$company) {
            // Create a minimal company profile so the page works seamlessly
            $company = \App\Models\Company::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => $user->name ?? 'شركة',
                    'province' => 'بغداد',
                    'industry' => 'أخرى',
                    'status' => $user->status ?? 'active',
                ]
            );
        }
        return view('company.profile.edit', compact('company'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $company = $user->company;
        if (!$company) {
            $company = \App\Models\Company::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => $user->name ?? 'شركة',
                    'province' => 'بغداد',
                    'industry' => 'أخرى',
                    'status' => $user->status ?? 'active',
                ]
            );
        }

        $request->validate([
            'profile_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048|dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000',
        ]);

        $imagePath = $company->profile_image;
        if ($request->hasFile('profile_image')) {
            try {
                $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                $image = $manager->read($request->file('profile_image')->getPathname())
                    ->scaleDown(width: 800, height: 800);
                Storage::disk('public')->makeDirectory('profile-images');
                $filename = 'profile-images/' . uniqid('img_') . '.webp';
                $fullPath = storage_path('app/public/' . $filename);
                $image->toWebp(quality: 82)->save($fullPath);
                $imagePath = $filename;
            } catch (\Throwable $e) {
                // Fallback to original storage if image processing fails
                $imagePath = $request->file('profile_image')->store('profile-images', 'public');
            }
        }

        $company->update([
            'profile_image' => $imagePath,
        ]);

        return back()->with('status','تم تحديث صورة الشركة بنجاح.');
    }
}

