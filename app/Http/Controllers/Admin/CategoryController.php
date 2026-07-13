<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->withCount('products')
            ->latest()
            ->paginate(10);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Category::create($this->validatedData($request) + ['tenant_id' => auth()->user()->tenant_id]);

        return redirect()->route('admin.categories.index')->with('status', 'Kategori berhasil ditambahkan.');
    }

    public function show(Category $category): RedirectResponse
    {
        return redirect()->route('admin.categories.edit', $category);
    }

    public function edit(Category $category): View
    {
        $this->ensureTenant($category);

        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->ensureTenant($category);

        $category->update($this->validatedData($request, $category));

        return redirect()->route('admin.categories.index')->with('status', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->ensureTenant($category);

        if ($category->products()->exists()) {
            return back()->withErrors(['category' => 'Kategori masih dipakai produk dan belum bisa dihapus.']);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Kategori berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Category $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = Str::slug($data['name']);

        validator($data, [
            'slug' => [
                'required',
                Rule::unique('categories', 'slug')
                    ->where(fn ($query) => $query->where('tenant_id', auth()->user()->tenant_id))
                    ->ignore($category),
            ],
        ])->validate();

        return $data + ['is_active' => false];
    }

    private function ensureTenant(Category $category): void
    {
        abort_unless((int) $category->tenant_id === (int) auth()->user()->tenant_id, 404);
    }
}
