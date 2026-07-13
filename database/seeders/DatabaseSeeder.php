<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Addon;
use App\Models\Setting;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VariantGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $cashierRole = Role::firstOrCreate(['name' => 'Kasir']);

        User::updateOrCreate(
            ['email' => 'superadmin@pos.test'],
            ['name' => 'Super Admin', 'password' => 'password', 'status' => 'active']
        )->syncRoles([$superAdminRole]);

        $tenant = Tenant::firstOrCreate(
            ['slug' => 'keijora-demo'],
            [
                'name' => 'Keijora Demo Cafe',
                'business_email' => 'owner@pos.test',
                'phone' => '081234567890',
                'address' => 'Jl. Demo POS No. 1',
                'status' => 'active',
                'approved_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@pos.test'],
            ['tenant_id' => $tenant->id, 'name' => 'Admin POS', 'password' => 'password', 'status' => 'active']
        )->syncRoles([$adminRole]);

        User::updateOrCreate(
            ['email' => 'kasir@pos.test'],
            ['tenant_id' => $tenant->id, 'name' => 'Kasir POS', 'password' => 'password', 'status' => 'active']
        )->syncRoles([$cashierRole]);

        Setting::setValue('payment_methods', [
            'cash' => true,
            'qris' => true,
        ], $tenant->id);

        Setting::setValue('receipt', [
            'logo_path' => null,
            'cafe_name' => 'Keijora Demo Cafe',
            'address' => 'Jl. Demo POS No. 1',
            'phone' => '081234567890',
            'footer_note' => 'Terima kasih atas kunjungan Anda.',
        ], $tenant->id);

        $categories = collect(['Minuman', 'Makanan', 'Snack'])->mapWithKeys(function (string $name) use ($tenant) {
            return [
                $name => Category::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['tenant_id' => $tenant->id, 'name' => $name, 'is_active' => true]
                ),
            ];
        });

        $products = [
            ['category' => 'Minuman', 'sku' => 'DRK-001', 'name' => 'Es Kopi Susu', 'price' => 15000, 'stock' => 25],
            ['category' => 'Makanan', 'sku' => 'FOD-001', 'name' => 'Mie Ayam Spesial', 'price' => 18000, 'stock' => 15],
            ['category' => 'Snack', 'sku' => 'SNK-001', 'name' => 'Bakwan Jagung', 'price' => 2500, 'stock' => 50],
            ['category' => 'Minuman', 'sku' => 'DRK-002', 'name' => 'Es Teh Manis', 'price' => 5000, 'stock' => 40],
        ];

        $createdProducts = collect();

        foreach ($products as $product) {
            $createdProducts->put($product['sku'], Product::firstOrCreate(
                ['sku' => $product['sku']],
                [
                    'tenant_id' => $tenant->id,
                    'category_id' => $categories[$product['category']]->id,
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                    'is_active' => true,
                ]
            ));
        }

        $temperature = VariantGroup::firstOrCreate(
            ['slug' => 'suhu'],
            ['tenant_id' => $tenant->id, 'name' => 'Suhu', 'selection_type' => 'single', 'is_required' => true, 'is_active' => true]
        );

        foreach ([
            ['name' => 'Es', 'price_delta' => 0],
            ['name' => 'Panas', 'price_delta' => 0],
        ] as $option) {
            $temperature->options()->firstOrCreate(['name' => $option['name']], $option + ['is_active' => true]);
        }

        $size = VariantGroup::firstOrCreate(
            ['slug' => 'ukuran'],
            ['tenant_id' => $tenant->id, 'name' => 'Ukuran', 'selection_type' => 'single', 'is_required' => false, 'is_active' => true]
        );

        foreach ([
            ['name' => 'Regular', 'price_delta' => 0],
            ['name' => 'Large', 'price_delta' => 5000],
        ] as $option) {
            $size->options()->firstOrCreate(['name' => $option['name']], $option + ['is_active' => true]);
        }

        $createdAddons = collect();

        foreach ([
            ['name' => 'Extra Keju', 'price' => 5000],
            ['name' => 'Bubble', 'price' => 4000],
            ['name' => 'Extra Espresso Shot', 'price' => 6000],
        ] as $addon) {
            $createdAddons->put($addon['name'], Addon::firstOrCreate(
                ['slug' => Str::slug($addon['name'])],
                $addon + ['tenant_id' => $tenant->id, 'is_active' => true]
            ));
        }

        $createdProducts['DRK-001']->variantGroups()->syncWithoutDetaching([$temperature->id, $size->id]);
        $createdProducts['DRK-001']->addons()->syncWithoutDetaching([
            $createdAddons['Bubble']->id,
            $createdAddons['Extra Espresso Shot']->id,
        ]);

        $createdProducts['DRK-002']->variantGroups()->syncWithoutDetaching([$temperature->id, $size->id]);
        $createdProducts['DRK-002']->addons()->syncWithoutDetaching([
            $createdAddons['Bubble']->id,
        ]);

        $createdProducts['FOD-001']->variantGroups()->syncWithoutDetaching([$size->id]);
        $createdProducts['FOD-001']->addons()->syncWithoutDetaching([
            $createdAddons['Extra Keju']->id,
        ]);
    }
}
