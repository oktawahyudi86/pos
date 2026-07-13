<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AddonController extends Controller
{
    public function index(): View
    {
        $addons = Addon::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->latest()
            ->paginate(10);

        return view('admin.addons.index', compact('addons'));
    }

    public function create(): View
    {
        return view('admin.addons.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Addon::create($this->validatedData($request) + ['tenant_id' => auth()->user()->tenant_id]);

        return redirect()->route('admin.addons.index')->with('status', 'Add-on berhasil ditambahkan.');
    }

    public function show(Addon $addon): RedirectResponse
    {
        return redirect()->route('admin.addons.edit', $addon);
    }

    public function edit(Addon $addon): View
    {
        $this->ensureTenant($addon);
        return view('admin.addons.edit', compact('addon'));
    }

    public function update(Request $request, Addon $addon): RedirectResponse
    {
        $this->ensureTenant($addon);
        $addon->update($this->validatedData($request, $addon));

        return redirect()->route('admin.addons.index')->with('status', 'Add-on berhasil diperbarui.');
    }

    public function destroy(Addon $addon): RedirectResponse
    {
        $this->ensureTenant($addon);
        $addon->delete();

        return redirect()->route('admin.addons.index')->with('status', 'Add-on berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Addon $addon = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = Str::slug($data['name']);

        validator($data, [
            'slug' => [
                'required',
                Rule::unique('addons', 'slug')
                    ->where(fn ($query) => $query->where('tenant_id', auth()->user()->tenant_id))
                    ->ignore($addon),
            ],
        ])->validate();

        return $data + ['is_active' => false];
    }

    private function ensureTenant(Addon $addon): void
    {
        abort_unless((int) $addon->tenant_id === (int) auth()->user()->tenant_id, 404);
    }
}
