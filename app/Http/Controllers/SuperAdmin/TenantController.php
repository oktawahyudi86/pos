<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::query()
            ->with(['users' => fn ($query) => $query->with('roles')])
            ->when(request('status'), fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(12);

        return view('super-admin.tenants.index', compact('tenants'));
    }

    public function show(Tenant $tenant): View
    {
        $tenant->load(['users.roles', 'approver']);

        return view('super-admin.tenants.show', compact('tenant'));
    }

    public function activate(Tenant $tenant): RedirectResponse
    {
        $tenant->update([
            'status' => 'active',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        $tenant->users()->where('status', 'pending')->update(['status' => 'active']);

        return back()->with('status', 'Tenant berhasil diaktifkan.');
    }

    public function suspend(Tenant $tenant): RedirectResponse
    {
        $tenant->update(['status' => 'suspended']);
        $tenant->users()->update(['status' => 'suspended']);

        return back()->with('status', 'Tenant berhasil dinonaktifkan.');
    }

    public function updateUser(Request $request, Tenant $tenant, User $user): RedirectResponse
    {
        abort_unless((int) $user->tenant_id === (int) $tenant->id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'status' => ['required', 'in:pending,active,suspended'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->status = $data['status'];

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return back()->with('status', 'Data user berhasil diperbarui.');
    }
}
