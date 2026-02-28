<?php

namespace App\Http\Controllers\JobSeeker;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\CvVerificationRequest;
use App\Models\JobSeeker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ProfileController extends Controller
{
    public function exportPdf() {
        $js = \App\Models\JobSeeker::firstWhere('user_id', \Illuminate\Support\Facades\Auth::id());
        abort_if(!$js, 404);

        $data = [
            'js' => $js,
            'user' => \Illuminate\Support\Facades\Auth::user(),
        ];

        // If Dompdf is available, return a PDF download; otherwise return HTML preview
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('jobseeker.profile.cv_pdf', $data)->setPaper('a4');
            $safeName = preg_replace('/[\\\/:*?"<>|]/', '_', $js->full_name ?: 'cv');
            return $pdf->download("CV_{$safeName}.pdf");
        }

        return view('jobseeker.profile.cv_pdf', $data);
    }
    public function dashboard(): View
    {
		$js = JobSeeker::firstWhere('user_id', Auth::id());
		$latestCvVerificationRequest = null;
		$isPharmacist = false;
			$cvVerificationAvailable = Schema::hasTable('cv_verification_requests');
		if ($js) {
				$isPharmacist = $js->isPharmacist();
				if ($isPharmacist && $cvVerificationAvailable) {
					$latestCvVerificationRequest = CvVerificationRequest::where('job_seeker_id', $js->id)
						->orderByDesc('id')
						->first();
				}
		}

			return view('jobseeker.dashboard', compact('js', 'latestCvVerificationRequest', 'isPharmacist', 'cvVerificationAvailable'));
    }

		public function cvVerification(): View
		{
			$js = JobSeeker::firstWhere('user_id', Auth::id());
			abort_if(!$js, 404);

				$isPharmacist = $js->isPharmacist();
			$cvVerificationAvailable = Schema::hasTable('cv_verification_requests');
			$latestCvVerificationRequest = null;
			if ($isPharmacist && $cvVerificationAvailable) {
				$latestCvVerificationRequest = CvVerificationRequest::where('job_seeker_id', $js->id)
					->orderByDesc('id')
					->first();
			}

			return view('jobseeker.cv-verification', compact('js', 'latestCvVerificationRequest', 'isPharmacist', 'cvVerificationAvailable'));
		}

	public function requestCvVerification(Request $request): RedirectResponse
	{
		$js = JobSeeker::firstWhere('user_id', Auth::id());
		abort_if(!$js, 404);
			if (!Schema::hasTable('cv_verification_requests')) {
				return back()->with('status', 'ميزة التوثيق غير متاحة حالياً. يرجى المحاولة لاحقاً.');
			}
			$isPharmacist = $js->isPharmacist();
			if (!$isPharmacist) {
				return redirect()->route('jobseeker.cv_verification.show')
						->with('status', 'هذه الخدمة متاحة للصيادلة فقط. إذا كنت صيدلانياً، حدّث بيانات ملفك (مثل: المسمى الوظيفي/التخصص/الكلية/القسم) ثم أعد المحاولة.');
			}

		if ($js->cv_verified) {
			return back()->with('status', 'السيرة الذاتية موثقة مسبقاً.');
		}
		if (empty($js->cv_file)) {
			return back()->with('status', 'يرجى رفع السيرة الذاتية أولاً ثم إعادة الطلب.');
		}

			$missing = [];
			if (empty($js->university_name)) {
				$missing[] = 'اسم الجامعة';
			}
			if (empty($js->college_name)) {
				$missing[] = 'اسم الكلية';
			}
			$gradYear = $js->graduation_year ?? null;
			if (empty($gradYear) || !is_numeric($gradYear) || (int) $gradYear < 1950 || (int) $gradYear > 2100) {
				$missing[] = 'سنة التخرج';
			}
			if (!empty($missing)) {
				return redirect()->route('jobseeker.cv_verification.show')
					->with('status', 'يرجى إكمال معلومات الدراسة قبل إرسال طلب التوثيق: ' . implode('، ', $missing) . '.');
			}

		$hasPending = CvVerificationRequest::where('job_seeker_id', $js->id)
			->where('status', CvVerificationRequest::STATUS_PENDING)
			->exists();
		if ($hasPending) {
			return back()->with('status', 'لديك طلب توثيق قيد المراجعة بالفعل.');
		}

		CvVerificationRequest::create([
			'job_seeker_id' => $js->id,
			'cv_file' => $js->cv_file,
			'status' => CvVerificationRequest::STATUS_PENDING,
		]);

		return back()->with('status', 'تم إرسال طلب توثيق السيرة الذاتية، سيتم مراجعته من قبل الإدارة.');
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
	            // Education
	            'university_name' => 'nullable|string|max:190',
	            'college_name' => 'nullable|string|max:190',
	            'department_name' => 'nullable|string|max:190',
	            'graduation_year' => 'nullable|integer|min:1950|max:2100',
	            'is_fresh_graduate' => 'nullable|boolean',
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
                    $manager->read($request->file('profile_image')->getPathname())->scaleDown(width: 160, height: 160)->toWebp(quality: 82)->save(storage_path('app/public/' . $sm));
                    $manager->read($request->file('profile_image')->getPathname())->scaleDown(width: 320, height: 320)->toWebp(quality: 82)->save(storage_path('app/public/' . $md));
                    $manager->read($request->file('profile_image')->getPathname())->scaleDown(width: 640, height: 640)->toWebp(quality: 82)->save(storage_path('app/public/' . $lg));
                } catch (\Throwable $e) {}

                $imagePath = $main;
            } catch (\Throwable $e) {
                $imagePath = $request->file('profile_image')->store('profile-images', 'public');
            }
        }

	        $gradYear = $request->input('graduation_year');
	        $gradYear = ($gradYear === null || $gradYear === '') ? null : (int) $gradYear;

	        $js->update([
            'full_name' => $request->full_name,
            'province' => $request->province,
            'districts' => $request->input('districts', []),
            'job_title' => $request->job_title,
            'speciality' => $request->speciality,
            'specialities' => $request->input('specialities', []),
            'gender' => $request->gender,
            'own_car' => (bool)$request->own_car,
	            // Education
	            'university_name' => $request->input('university_name'),
	            'college_name' => $request->input('college_name'),
	            'department_name' => $request->input('department_name'),
	            'graduation_year' => $gradYear,
	            'is_fresh_graduate' => $request->boolean('is_fresh_graduate'),
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

