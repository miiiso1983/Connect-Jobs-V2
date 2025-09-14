<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class CompanyProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $company = $user->company;
        if (!$company) {
            // Create a minimal company profile so the page works seamlessly
            $company = \App\Models\Company::create([
                'user_id' => $user->id,
                'company_name' => $user->name ?? 'شركة',
                'status' => $user->status ?? 'active',
            ]);
        }
        return view('company.profile.edit', compact('company'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $company = $user->company;
        if (!$company) {
            $company = \App\Models\Company::create([
                'user_id' => $user->id,
                'company_name' => $user->name ?? 'شركة',
                'status' => $user->status ?? 'active',
            ]);
        }

        $request->validate([
            'profile_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048|dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000',
        ]);

        $imagePath = $company->profile_image;
        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('profile-images','public');
        }

        $company->update([
            'profile_image' => $imagePath,
        ]);

        return back()->with('status','تم تحديث صورة الشركة بنجاح.');
    }
}

