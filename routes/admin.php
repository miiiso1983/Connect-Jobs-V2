<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleMiddleware;

Route::middleware(['auth', 'role:admin'])->group(function(){
    Route::get('/admin', function(){
        return view('dashboards.admin');
    })->name('admin.dashboard');
});

