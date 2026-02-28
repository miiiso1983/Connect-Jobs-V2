<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminPermission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUsersController extends Controller
{
    public function index(): View
    {
        $admins = User::query()
            ->where('role', 'admin')
            ->with('adminPermission')
            ->orderBy('id')
            ->paginate(20);

        $labels = AdminPermission::labels();

        return view('admin.admin-users.index', compact('admins', 'labels'));
    }

    public function create(): View
    {
        $labels = AdminPermission::labels();
        return view('admin.admin-users.create', compact('labels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:190|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'required|in:active,inactive,suspended',
            'permissions' => 'array',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // hashed by cast
            'role' => 'admin',
            'status' => $data['status'],
        ]);

        $this->upsertPermissions($user, (array) ($data['permissions'] ?? []));

        return redirect()->route('admin.admin_users.index')->with('status', 'تم إضافة مستخدم إداري بنجاح.');
    }

    public function edit(User $user): View|RedirectResponse
    {
        abort_unless(($user->role ?? null) === 'admin', 404);

        if ($user->isMasterAdmin()) {
            return redirect()->route('admin.admin_users.index')->with('status', 'لا يمكن تعديل حساب الماستر أدمن.');
        }

        $user->load('adminPermission');
        $labels = AdminPermission::labels();
        return view('admin.admin-users.edit', compact('user', 'labels'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless(($user->role ?? null) === 'admin', 404);

        if ($user->isMasterAdmin()) {
            return back()->with('status', 'لا يمكن تعديل حساب الماستر أدمن.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:190|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'status' => 'required|in:active,inactive,suspended',
            'permissions' => 'array',
        ]);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => $data['status'],
        ];
        if (!empty($data['password'] ?? '')) {
            $payload['password'] = $data['password'];
        }

        $user->update($payload);

        $this->upsertPermissions($user, (array) ($data['permissions'] ?? []));

        return redirect()->route('admin.admin_users.index')->with('status', 'تم تحديث بيانات المستخدم الإداري.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless(($user->role ?? null) === 'admin', 404);

        if ($user->isMasterAdmin()) {
            return back()->with('status', 'لا يمكن حذف حساب الماستر أدمن.');
        }

        $user->delete();

        return back()->with('status', 'تم حذف المستخدم الإداري.');
    }

    private function upsertPermissions(User $user, array $selected): void
    {
        // Master admin always full permissions regardless of submitted form.
        if ($user->isMasterAdmin()) {
            AdminPermission::updateOrCreate(['user_id' => $user->id], AdminPermission::fullAccessPayload());
            return;
        }

        $payload = [];
        foreach (AdminPermission::MAP as $key => $column) {
            $payload[$column] = array_key_exists($key, $selected);
        }

        AdminPermission::updateOrCreate(['user_id' => $user->id], $payload);
    }
}
