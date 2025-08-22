<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\MasterSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DistrictAdminController extends Controller
{
    public function index(): View
    {
        $provinces = \App\Models\MasterSetting::where('setting_type','province')->orderBy('value')->pluck('value');
        $byProvince = District::orderBy('province')->orderBy('name')->get()->groupBy('province');
        return view('admin.districts.index', compact('provinces','byProvince'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'province' => 'required|string|max:100',
            'name' => 'required|string|max:150',
        ]);
        $data = $request->only('province','name');
        District::firstOrCreate($data);
        // Ensure province exists in master settings
        MasterSetting::firstOrCreate(['setting_type'=>'province','value'=>$data['province']]);
        return back()->with('status','تمت الإضافة.');

    }


    public function bulkStore(Request $request): RedirectResponse
    {
        $request->validate([
            'province' => 'required|string|max:100',
            'values' => 'required|string',
        ]);
        $prov = trim($request->province);
        $lines = preg_split("/(\r\n|\n|\r)/", $request->values);
        $created = 0; $skipped = 0;
        foreach ($lines as $line) {
            $name = trim($line);
            if ($name==='') continue;
            $exists = District::where('province',$prov)->whereRaw('LOWER(`name`)=?', [mb_strtolower($name)])->exists();
            if ($exists) { $skipped++; continue; }
            District::create(['province'=>$prov,'name'=>$name]);
            $created++;
        }
        MasterSetting::firstOrCreate(['setting_type'=>'province','value'=>$prov]);
        return back()->with('status', "تمت الإضافة: $created | تم التجاوز (مكرر): $skipped");
    }

    public function destroy(District $district): RedirectResponse
    {
        $district->delete();
        return back()->with('status','تم الحذف.');
    }

    public function export(Request $request)
    {
        $province = $request->query('province');
        $file = 'districts'.($province?('_'.$province):'').'_'.date('Ymd_His').'.csv';
        $q = District::query(); if ($province) $q->where('province',$province);
        $rows = $q->orderBy('province')->orderBy('name')->get(['province','name']);
        return response()->streamDownload(function() use ($rows){
            $out = fopen('php://output','w');
            fputcsv($out, ['province','name']);
            foreach ($rows as $r) fputcsv($out, [$r->province,$r->name]);
            fclose($out);
        }, $file, ['Content-Type'=>'text/csv']);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv' => 'required|file|mimes:csv,txt',
            'province' => 'nullable|string',
        ]);
        $defProvince = $request->input('province');
        $fh = fopen($request->file('csv')->getRealPath(), 'r');
        $header = fgetcsv($fh);
        $created=0; $skipped=0;
        while (($row = fgetcsv($fh))!==false) {
            $row = array_map('trim',$row);
            if ($header && count($header)>=2 && strtolower($header[0])==='province') {
                [$prov,$name] = [$row[0]??'', $row[1]??''];
            } else {
                $prov = $defProvince; $name = $row[0]??'';
            }
            if (!$prov || !$name) { $skipped++; continue; }
            $exists = District::where('province',$prov)->whereRaw('LOWER(`name`)=?', [mb_strtolower($name)])->exists();
            if ($exists) { $skipped++; continue; }
            District::create(['province'=>$prov,'name'=>$name]);
            $created++;
        }
        fclose($fh);
        return back()->with('status', "استيراد CSV — تمت الإضافة: $created | تم التجاوز: $skipped");
    }
}

