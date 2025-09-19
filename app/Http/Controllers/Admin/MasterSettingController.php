<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use App\Models\EmailTemplate;

class MasterSettingController extends Controller
{
    public function index(): View
    {
        $types = MasterSetting::select('setting_type')->distinct()->orderBy('setting_type')->pluck('setting_type');
        $settings = MasterSetting::orderBy('setting_type')->orderBy('value')->get();
        $templates = collect();
        if (class_exists(EmailTemplate::class) && Schema::hasTable('email_templates')) {
            $templates = EmailTemplate::orderBy('scope')->orderBy('name')->get();
        }
        return view('admin.settings.index', compact('types','settings','templates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'setting_type' => 'required|string|max:100',
            'value' => 'required|string|max:191',
        ]);
        $type = trim($request->setting_type);
        $val = trim($request->value);
        $lc = mb_strtolower($val);
        // Basic English plural normalization candidates
        $candidates = [$lc];
        if (str_ends_with($lc, 'es')) $candidates[] = substr($lc, 0, -2);
        if (str_ends_with($lc, 's')) $candidates[] = substr($lc, 0, -1);

        $exists = MasterSetting::where('setting_type', $type)
            ->where(function($q) use ($candidates){
                foreach (array_unique($candidates) as $c) {
                    $q->orWhereRaw('LOWER(`value`) = ?', [$c]);
                }
            })->exists();
        if ($exists) {
            return back()->withInput()->with('status', 'القيمة موجودة مسبقاً (بدون حساسية حالة الأحرف أو اختلاف الجمع/المفرد البسيط).');
        }
        MasterSetting::create(['setting_type'=>$type, 'value'=>$val]);
        return back()->with('status','تمت الإضافة.');

    }


    public function bulkStore(Request $request): RedirectResponse
    {
        $request->validate([
            'setting_type' => 'required|string|max:100',
            'values' => 'required|string',
        ]);
        $type = trim($request->setting_type);
        $lines = preg_split("/(\r\n|\n|\r)/", $request->values);
        $created = 0; $skipped = 0;
        foreach ($lines as $line) {
            $val = trim($line);
            if ($val==='') { continue; }
            $lc = mb_strtolower($val);
            $candidates = [$lc];
            if (str_ends_with($lc, 'es')) $candidates[] = substr($lc, 0, -2);
            if (str_ends_with($lc, 's')) $candidates[] = substr($lc, 0, -1);
            $exists = MasterSetting::where('setting_type', $type)
                ->where(function($q) use ($candidates){
                    foreach (array_unique($candidates) as $c) {
                        $q->orWhereRaw('LOWER(`value`) = ?', [$c]);
                    }
                })->exists();
            if ($exists) { $skipped++; continue; }
            MasterSetting::create(['setting_type'=>$type, 'value'=>$val]);
            $created++;
        }
        return back()->with('status', "تمت الإضافة: $created | تم التجاوز (مكرر/مماثل): $skipped");
    }

    public function update(Request $request, MasterSetting $setting): RedirectResponse
    {
        $request->validate([
            'value' => 'required|string|max:191',
        ]);
        $setting->update(['value' => $request->value]);
        return back()->with('status','تم التحديث.');
    }

    public function destroy(MasterSetting $setting): RedirectResponse
    {
        $setting->delete();
        return back()->with('status','تم الحذف.');
    }

    public function export(Request $request)
    {
        $type = $request->query('type');
        $file = 'master_settings'.($type?('_'.$type):'').'_'.date('Ymd_His').'.csv';
        $query = MasterSetting::query();
        if ($type) { $query->where('setting_type',$type); }
        $rows = $query->orderBy('setting_type')->orderBy('value')->get(['setting_type','value']);
        return response()->streamDownload(function() use ($rows){
            $out = fopen('php://output','w');
            fputcsv($out, ['setting_type','value']);
            foreach ($rows as $r) { fputcsv($out, [$r->setting_type, $r->value]); }
            fclose($out);
        }, $file, ['Content-Type' => 'text/csv']);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv' => 'required|file|mimes:csv,txt',
            'type' => 'nullable|string',
        ]);
        $typeOverride = $request->input('type');
        $fh = fopen($request->file('csv')->getRealPath(), 'r');
        $header = fgetcsv($fh);
        $created = 0; $skipped = 0; $line = 1;
        while (($row = fgetcsv($fh)) !== false) {
            $line++;
            if (!$row) continue;
            $row = array_map('trim', $row);
            if ($header && count($header) >= 2 && strtolower($header[0])==='setting_type') {
                [$stype,$val] = [$row[0] ?? '', $row[1] ?? ''];
            } else {
                $stype = $typeOverride ?? 'job_title';
                $val = $row[0] ?? '';
            }
            if ($stype==='' || $val==='') { $skipped++; continue; }
            $lc = mb_strtolower($val);
            $candidates = [$lc];
            if (str_ends_with($lc, 'es')) $candidates[] = substr($lc, 0, -2);
            if (str_ends_with($lc, 's')) $candidates[] = substr($lc, 0, -1);
            $exists = MasterSetting::where('setting_type', $stype)
                ->where(function($q) use ($candidates){
                    foreach (array_unique($candidates) as $c) {
                        $q->orWhereRaw('LOWER(`value`) = ?', [$c]);
                    }
                })->exists();
            if ($exists) { $skipped++; continue; }
            MasterSetting::create(['setting_type'=>$stype,'value'=>$val]);
            $created++;
        }
        fclose($fh);
        return back()->with('status', "استيراد CSV — تمت الإضافة: $created | تم التجاوز: $skipped");
    }



}
