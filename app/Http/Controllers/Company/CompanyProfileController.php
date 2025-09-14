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
        $company = Auth::user()->company;
        return view('company.profile.edit', compact('company'));
    }

    public function update(Request $request): RedirectResponse
    {
        $company = Auth::user()->company;

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

