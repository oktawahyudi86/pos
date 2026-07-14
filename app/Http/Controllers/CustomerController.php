<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $tenant = $request->user()->tenant;

        $customers = User::role('Customer')
            ->where('tenant_id', $tenant->id)
            ->withCount(['onlineOrders' => function ($query) use ($tenant) {
                $query->where('tenant_id', $tenant->id);
            }])
            ->latest('created_at')
            ->get();

        return view('customers.index', compact('customers', 'tenant'));
    }

    public function deactivate(User $customer, Request $request)
    {
        $tenant = $request->user()->tenant;

        abort_unless((int) $customer->tenant_id === (int) $tenant->id, 403);
        abort_unless($customer->hasRole('Customer'), 403);

        $customer->update(['status' => 'inactive']);

        return back()->with('success', 'Pelanggan berhasil dinonaktifkan');
    }

    public function activate(User $customer, Request $request)
    {
        $tenant = $request->user()->tenant;

        abort_unless((int) $customer->tenant_id === (int) $tenant->id, 403);
        abort_unless($customer->hasRole('Customer'), 403);

        $customer->update(['status' => 'active']);

        return back()->with('success', 'Pelanggan berhasil diaktifkan');
    }

    public function sendPasswordReset(User $customer, Request $request)
    {
        $tenant = $request->user()->tenant;

        abort_unless((int) $customer->tenant_id === (int) $tenant->id, 403);
        abort_unless($customer->hasRole('Customer'), 403);

        // Generate password reset token
        $token = Password::createToken($customer);

        // Store the reset information
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $customer->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Generate the reset link
        $resetLink = route('password.reset', [
            'token' => $token,
            'email' => $customer->email,
        ]);

        return back()->with('success', 'Link reset password berhasil dibuat: ' . $resetLink);
    }
}
