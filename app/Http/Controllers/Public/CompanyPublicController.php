<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\View\View;

class CompanyPublicController extends Controller
{
    public function show(Company $company): View
    {
        // Load open + approved jobs for this company
        $company->load(['jobs' => function($q){
            $q->where('approved_by_admin', true)->where('status','open')->orderByDesc('id');
        }]);

        return view('public.companies.show', compact('company'));
    }
}

