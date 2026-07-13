<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VariantGroup;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VariantGroupController extends Controller
{
    public function index(): View
    {
        $variantGroups = VariantGroup::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->withCount('options')
            ->latest()
            ->paginate(10);

        return view('admin.variant-groups.index', compact('variantGroups'));
    }

    public function create(): View
    {
        return view('admin.variant-groups.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $options = $data['options'];
        unset($data['options']);

        $variantGroup = VariantGroup::create($data + ['tenant_id' => auth()->user()->tenant_id]);
        $this->syncOptions($variantGroup, $options);

        return redirect()->route('admin.variant-groups.index')->with('status', 'Grup varian berhasil ditambahkan.');
    }

    public function show(VariantGroup $variantGroup): RedirectResponse
    {
        return redirect()->route('admin.variant-groups.edit', $variantGroup);
    }

    public function edit(VariantGroup $variantGroup): View
    {
        $this->ensureTenant($variantGroup);
        $variantGroup->load('options');

        return view('admin.variant-groups.edit', compact('variantGroup'));
    }

    public function update(Request $request, VariantGroup $variantGroup): RedirectResponse
    {
        $this->ensureTenant($variantGroup);
        $data = $this->validatedData($request, $variantGroup);
        $options = $data['options'];
        unset($data['options']);

        $variantGroup->update($data);
        $this->syncOptions($variantGroup, $options);

        return redirect()->route('admin.variant-groups.index')->with('status', 'Grup varian berhasil diperbarui.');
    }

    public function destroy(VariantGroup $variantGroup): RedirectResponse
    {
        $this->ensureTenant($variantGroup);
        $variantGroup->delete();

        return redirect()->route('admin.variant-groups.index')->with('status', 'Grup varian berhasil dihapus.');
    }

    private function validatedData(Request $request, ?VariantGroup $variantGroup = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'selection_type' => ['required', Rule::in(['single', 'multiple'])],
            'is_required' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'options' => ['required', 'array', 'min:1'],
            'options.*.name' => ['required', 'string', 'max:100'],
            'options.*.price_delta' => ['nullable', 'integer', 'min:0'],
            'options.*.is_active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = Str::slug($data['name']);

        validator($data, [
            'slug' => [
                'required',
                Rule::unique('variant_groups', 'slug')
                    ->where(fn ($query) => $query->where('tenant_id', auth()->user()->tenant_id))
                    ->ignore($variantGroup),
            ],
        ])->validate();

        return $data + [
            'is_required' => false,
            'is_active' => false,
        ];
    }

    private function ensureTenant(VariantGroup $variantGroup): void
    {
        abort_unless((int) $variantGroup->tenant_id === (int) auth()->user()->tenant_id, 404);
    }

    private function syncOptions(VariantGroup $variantGroup, array $options): void
    {
        $variantGroup->options()->delete();

        foreach ($options as $option) {
            $variantGroup->options()->create([
                'name' => $option['name'],
                'price_delta' => $option['price_delta'] ?? 0,
                'is_active' => (bool) ($option['is_active'] ?? false),
            ]);
        }
    }
}
