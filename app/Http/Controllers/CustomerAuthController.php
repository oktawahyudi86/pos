<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class CustomerAuthController extends Controller
{
    public function profile(Tenant $tenant, Request $request): View|RedirectResponse
    {
        abort_unless($tenant->isActive(), 404);

        if (! $request->user()) {
            return redirect()->route('online-orders.auth', [
                'tenant' => $tenant,
                'redirect' => route('online-orders.profile', $tenant),
            ]);
        }

        return view('auth.customer-profile', [
            'tenant' => $tenant,
            'user' => $request->user(),
            'cartCount' => collect($request->session()->get('online_order_cart_'.$tenant->id, []))->sum('quantity'),
        ]);
    }

    public function show(Tenant $tenant, Request $request): View|RedirectResponse
    {
        abort_unless($tenant->isActive(), 404);

        if ($request->user()) {
            return redirect()->to($this->safeRedirect($request, route('online-orders.catalog', $tenant)));
        }

        return view('auth.customer', [
            'tenant' => $tenant,
            'redirectTo' => $this->safeRedirect($request, route('online-orders.catalog', $tenant)),
            'activeTab' => $request->query('tab') === 'register' ? 'register' : 'login',
        ]);
    }

    public function login(Tenant $tenant, Request $request): RedirectResponse
    {
        abort_unless($tenant->isActive(), 404);

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $phone = $this->normalizePhone($validated['phone']);

        if (! Auth::attempt(['phone' => $phone, 'password' => $validated['password']], true)) {
            throw ValidationException::withMessages([
                'phone' => 'Nomor HP atau password tidak sesuai.',
            ]);
        }

        $user = $request->user();

        if (! $user?->hasRole('Customer') || ! $user->isActive()) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'phone' => 'Akun customer tidak aktif untuk pemesanan online.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->to($this->safeRedirect($request, route('online-orders.catalog', $tenant)));
    }

    public function register(Tenant $tenant, Request $request): RedirectResponse
    {
        abort_unless($tenant->isActive(), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $phone = $this->normalizePhone($validated['phone']);

        if (User::query()->where('phone', $phone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => 'Nomor HP sudah terdaftar. Silakan masuk.',
            ]);
        }

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'email' => $this->makeCustomerEmail($phone),
            'phone' => $phone,
            'password' => Hash::make($validated['password']),
            'status' => 'active',
        ]);

        $user->assignRole(Role::firstOrCreate(['name' => 'Customer']));

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->to($this->safeRedirect($request, route('online-orders.catalog', $tenant)));
    }

    public function logout(Tenant $tenant, Request $request): RedirectResponse
    {
        abort_unless($tenant->isActive(), 404);

        Auth::guard('web')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('online-orders.catalog', $tenant);
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?: $phone;
    }

    private function makeCustomerEmail(string $phone): string
    {
        return 'customer-'.$phone.'-'.Str::lower(Str::random(6)).'@customer.local';
    }

    private function safeRedirect(Request $request, string $fallback): string
    {
        $target = $request->input('redirect_to', $request->query('redirect', $fallback));
        $appUrl = rtrim(config('app.url'), '/');

        if (str_starts_with($target, '/')) {
            return $target;
        }

        if ($appUrl !== '' && str_starts_with($target, $appUrl)) {
            return $target;
        }

        if (str_starts_with($target, $request->getSchemeAndHttpHost())) {
            return $target;
        }

        return $fallback;
    }
}
