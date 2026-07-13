<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('super-admin.dashboard', [
            'pendingCount' => Tenant::where('status', 'pending')->count(),
            'activeCount' => Tenant::where('status', 'active')->count(),
            'suspendedCount' => Tenant::where('status', 'suspended')->count(),
            'latestTenants' => Tenant::with('users.roles')->latest()->limit(8)->get(),
        ]);
    }
}
