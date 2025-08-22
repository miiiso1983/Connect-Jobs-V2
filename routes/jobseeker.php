<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:jobseeker'])->group(function(){
    Route::get('/jobseeker', function(){
        return view('dashboards.jobseeker');
    })->name('jobseeker.dashboard');
});

