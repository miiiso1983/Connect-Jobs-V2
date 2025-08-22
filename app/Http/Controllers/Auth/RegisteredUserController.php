<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:company,jobseeker'],
            'scientific_office_name' => ['nullable','string','max:150'],
            'company_job_title' => ['nullable','string','max:150'],
            'mobile_number' => ['nullable','string','max:30'],
        ]);

        $role = $request->input('role');
        // Companies need admin approval, jobseekers need code verification
        $status = $role === 'company' ? 'inactive' : 'inactive';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'status' => $status,
        ]);
        // Post-create: create related profile record
        if ($role === 'company') {
            $company = \App\Models\Company::firstOrCreate(['user_id' => $user->id], [
                'company_name' => $request->name,
                'scientific_office_name' => $request->scientific_office_name,
                'company_job_title' => $request->company_job_title,
                'mobile_number' => $request->mobile_number,
                'province' => 'N/A',
                'industry' => 'N/A',
                'subscription_plan' => 'free',
                'status' => 'inactive',
            ]);
            // Notify master admin via email (queued) and log
            try {
                $adminEmail = env('MASTER_ADMIN_EMAIL', 'mustafa@teamiapps.com');
                Mail::to($adminEmail)->queue(new \App\Mail\CompanyRegistrationRequestMail($company, $user));
                \DB::table('email_logs')->insert([
                    'mailable' => \App\Mail\CompanyRegistrationRequestMail::class,
                    'to_email' => $adminEmail,
                    'to_name' => 'Master Admin',
                    'payload' => json_encode(['company_id' => $company->id, 'user_id' => $user->id]),
                    'status' => 'queued',
                    'queued_at' => now(),
                ]);
            } catch (\Throwable $e) { \Log::error('CompanyRegistration mail failed: '.$e->getMessage()); }
        } else {
            $seeker = \App\Models\JobSeeker::firstOrCreate(['user_id' => $user->id], [
                'full_name' => $request->name,
                'province' => 'N/A',
                'profile_completed' => false,
            ]);
            // Notify master admin for new jobseeker
            try {
                $adminEmail = env('MASTER_ADMIN_EMAIL', 'mustafa@teamiapps.com');
                Mail::to($adminEmail)->queue(new \App\Mail\NewJobSeekerRegisteredMail($seeker, $user));
                \DB::table('email_logs')->insert([
                    'mailable' => \App\Mail\NewJobSeekerRegisteredMail::class,
                    'to_email' => $adminEmail,
                    'to_name' => 'Master Admin',
                    'payload' => json_encode(['seeker_id' => $seeker->id, 'user_id' => $user->id]),
                    'status' => 'queued',
                    'queued_at' => now(),
                ]);
            } catch (\Throwable $e) { \Log::error('NewJobSeeker mail failed: '.$e->getMessage()); }
        }

        // Ensure email verification link is sent even if events aren't registered
        $dispatcher = app('events');
        if (is_object($dispatcher) && method_exists($dispatcher, 'hasListeners')
            && ! $dispatcher->hasListeners(Registered::class)) {
            $user->sendEmailVerificationNotification();
        }

        // Fire the Registered event (framework listener will send verification when events are enabled)
        event(new Registered($user));

        Auth::login($user);

        if ($role === 'jobseeker') {
            return redirect()->route('verify.code.show')->with('status','أرسلنا لك صفحة التفعيل، اختر القناة وأرسل الرمز.');
        }
        return redirect(route('dashboard', absolute: false));
    }
}
