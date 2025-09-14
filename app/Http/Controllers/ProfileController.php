<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // Persist notification preferences if provided
        if ($request->has('application_notifications_opt_in')) {
            $request->user()->application_notifications_opt_in = (bool)$request->boolean('application_notifications_opt_in');
        } else if ($request->isMethod('patch')) {
            // If checkbox absent, treat as unchecked for explicit PATCH from preferences form
            if ($request->header('referer') && str_contains($request->header('referer'), '/profile')) {
                $request->user()->application_notifications_opt_in = false;
            }
        }
        if ($request->has('profile_view_notifications_opt_in')) {
            $request->user()->profile_view_notifications_opt_in = (bool)$request->boolean('profile_view_notifications_opt_in');
        } else if ($request->isMethod('patch')) {
            if ($request->header('referer') && str_contains($request->header('referer'), '/profile')) {
                $request->user()->profile_view_notifications_opt_in = false;
            }
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
