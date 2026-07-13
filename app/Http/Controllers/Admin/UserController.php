<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = $this->cashierQuery($request)
            ->when($request->search, function ($query, string $search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create', ['user' => new User()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'active',
        ]);

        $user->assignRole(Role::firstOrCreate(['name' => 'Kasir']));

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Kasir berhasil ditambahkan.');
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorizeCashier($request, $user);

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeCashier($request, $user);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'status' => ['required', 'in:active,suspended'],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->status = $data['status'];

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Data kasir berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizeCashier($request, $user);

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Kasir berhasil dihapus.');
    }

    private function cashierQuery(Request $request)
    {
        return User::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->whereHas('roles', fn ($query) => $query->where('name', 'Kasir'));
    }

    private function authorizeCashier(Request $request, User $user): void
    {
        abort_unless((int) $user->tenant_id === (int) $request->user()->tenant_id, 404);
        abort_unless($user->hasRole('Kasir'), 404);
    }
}
