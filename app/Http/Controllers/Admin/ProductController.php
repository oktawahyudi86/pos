<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Category;
use App\Models\Product;
use App\Models\VariantGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $tenantId = auth()->user()->tenant_id;
        $categories = Category::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->with(['category', 'variantGroups', 'addons'])
            ->where('tenant_id', $tenantId)
            ->when(request('category'), fn ($query, string $category) => $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $category)))
            ->when(request('status') === 'active', fn ($query) => $query->where('is_active', true)->where('stock', '>', 0))
            ->when(request('status') === 'empty', fn ($query) => $query->where('stock', 0))
            ->when(request('status') === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest()
            ->paginate(10);

        return view('admin.products.index', compact('categories', 'products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $tenantId = auth()->user()->tenant_id;
        $categories = Category::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $variantGroups = $this->activeVariantGroups();
        $addons = $this->activeAddons();

        return view('admin.products.create', compact('addons', 'categories', 'variantGroups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedProductData($request);
        $variantGroupIds = $data['variant_group_ids'] ?? [];
        $addonIds = $data['addon_ids'] ?? [];
        unset($data['variant_group_ids'], $data['addon_ids']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data + ['tenant_id' => auth()->user()->tenant_id]);
        $product->variantGroups()->sync($variantGroupIds);
        $product->addons()->sync($addonIds);

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Produk berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): RedirectResponse
    {
        return redirect()->route('admin.products.edit', $product);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        $this->ensureTenant($product);
        $product->load(['variantGroups', 'addons']);

        $tenantId = auth()->user()->tenant_id;
        $categories = Category::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $variantGroups = $this->activeVariantGroups();
        $addons = $this->activeAddons();

        return view('admin.products.edit', compact('addons', 'product', 'categories', 'variantGroups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->ensureTenant($product);
        $data = $this->validatedProductData($request, $product);
        $variantGroupIds = $data['variant_group_ids'] ?? [];
        $addonIds = $data['addon_ids'] ?? [];
        unset($data['variant_group_ids'], $data['addon_ids']);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);
        $product->variantGroups()->sync($variantGroupIds);
        $product->addons()->sync($addonIds);

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->ensureTenant($product);
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Produk berhasil dihapus.');
    }

    private function validatedProductData(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('tenant_id', auth()->user()->tenant_id)),
            ],
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'sku')
                    ->where(fn ($query) => $query->where('tenant_id', auth()->user()->tenant_id))
                    ->ignore($product),
            ],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'variant_group_ids' => ['nullable', 'array'],
            'variant_group_ids.*' => [
                'integer',
                Rule::exists('variant_groups', 'id')->where(fn ($query) => $query->where('tenant_id', auth()->user()->tenant_id)),
            ],
            'addon_ids' => ['nullable', 'array'],
            'addon_ids.*' => [
                'integer',
                Rule::exists('addons', 'id')->where(fn ($query) => $query->where('tenant_id', auth()->user()->tenant_id)),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => false];
    }

    private function activeVariantGroups()
    {
        return VariantGroup::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->with(['options' => fn ($query) => $query->where('is_active', true)->orderBy('name')])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function activeAddons()
    {
        return Addon::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function ensureTenant(Product $product): void
    {
        abort_unless((int) $product->tenant_id === (int) auth()->user()->tenant_id, 404);
    }
}
