<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'tenant_name' => ['required', 'string', 'max:160'],
            'business_email' => ['nullable', 'string', 'lowercase', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $slug = $this->uniqueTenantSlug($request->tenant_name);
        $logoPath = $request->hasFile('logo')
            ? $request->file('logo')->store('tenants', 'public')
            : null;

        $tenant = Tenant::create([
            'name' => $request->tenant_name,
            'slug' => $slug,
            'business_email' => $request->business_email,
            'phone' => $request->phone,
            'address' => $request->address,
            'logo_path' => $logoPath,
            'status' => 'pending',
        ]);

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'pending',
        ]);

        $user->assignRole(Role::firstOrCreate(['name' => 'Admin']));

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('pending-approval', absolute: false));
    }

    private function uniqueTenantSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 2;

        while (Tenant::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
