<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UserAdminController extends Controller
{
    public function toggleStatus(User $user): RedirectResponse
    {
        $next = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $next]);
        return back()->with('status','تم تحديث حالة المستخدم.');
    }
}

