<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:company', 'company.approved'])->group(function(){
    Route::get('/company', function(){
        return view('dashboards.company');
    })->name('company.dashboard');
});

