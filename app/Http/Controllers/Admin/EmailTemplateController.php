<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailTemplateController extends Controller
{
    public function index(): View
    {
        $templates = EmailTemplate::orderBy('scope')->orderBy('name')->get();
        return view('admin.settings.templates', compact('templates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'scope' => 'required|string|in:company,jobseeker,admin',
            'key' => 'nullable|string|max:100',
            'name' => 'required|string|max:150',
            'subject' => 'required|string|max:200',
            'body' => 'required|string',
            'active' => 'nullable|boolean',
        ]);
        $data['active'] = (bool)($request->boolean('active'));
        EmailTemplate::create($data);
        return back()->with('status', 'تم إنشاء القالب.');
    }

    public function update(Request $request, EmailTemplate $template): RedirectResponse
    {
        $data = $request->validate([
            'scope' => 'required|string|in:company,jobseeker,admin',
            'key' => 'nullable|string|max:100',
            'name' => 'required|string|max:150',
            'subject' => 'required|string|max:200',
            'body' => 'required|string',
            'active' => 'nullable|boolean',
        ]);
        $data['active'] = (bool)($request->boolean('active'));
        $template->update($data);
        return back()->with('status', 'تم تحديث القالب.');
    }

    public function destroy(EmailTemplate $template): RedirectResponse
    {
        $template->delete();
        return back()->with('status', 'تم حذف القالب.');
    }
}

