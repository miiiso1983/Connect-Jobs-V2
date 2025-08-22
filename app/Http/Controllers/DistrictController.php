<?php

namespace App\Http\Controllers;

use App\Models\District;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    public function index(Request $request)
    {
        $province = $request->query('province');
        if (!$province) return response()->json([]);
        $items = District::where('province',$province)->orderBy('name')->pluck('name');
        if ($items->isEmpty()) {
            // Fallback: provide a minimal default to allow the UI to proceed
            $items = collect(['المركز']);
        }
        return response()->json($items);
    }
}

