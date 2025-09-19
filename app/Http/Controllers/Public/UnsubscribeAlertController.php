<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\JobAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnsubscribeAlertController extends Controller
{
    public function __invoke(Request $request, string $token): View|RedirectResponse
    {
        $alert = JobAlert::where('unsubscribe_token', $token)->first();
        if (!$alert) {
            return redirect()->route('home')->with('status', 'رابط غير صالح أو منتهي.');
        }
        if ($alert->enabled) {
            $alert->enabled = false;
            $alert->save();
        }
        return view('public.alerts.unsubscribed');
    }
}

