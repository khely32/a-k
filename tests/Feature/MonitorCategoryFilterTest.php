<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitorCategoryFilterTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'branch_name' => 'Moroboro Branch',
            'location'    => 'Brgy Moroboro Dingle, Ilo-ilo',
            'is_active'   => true,
            'is_main'     => true,
        ]);

        $this->owner = User::create([
            'name'      => 'Owner',
            'email'     => 'owner@example.com',
            'password'  => bcrypt('secret'),
            'role'      => 'owner',
            'branch_id' => $this->branch->id,
        ]);
    }

    private function addProduct(string $name, string $type, string $brand = 'Generic'): Product
    {
        $product = Product::create([
            'serial_number' => 'SKU-' . strtoupper(substr(md5($name . $type), 0, 8)),
            'name'          => $name,
            'brand'         => $brand,
            'type'          => $type,
            'price'         => 100.00,
            'quantity'      => 5,
        ]);

        Inventory::updateOrCreate(
            ['product_id' => $product->id, 'branch_id' => $this->branch->id],
            ['quantity' => 5]
        );

        return $product;
    }

    private function dropdownOptions(): array
    {
        $html = $this->actingAs($this->owner)->get('/monitor')->assertOk()->getContent();

        preg_match('/<select id="category-filter".*?<\/select>/s', $html, $m);
        $this->assertNotEmpty($m, 'category dropdown not found');

        preg_match_all('/<option value="([^"]*)"/', $m[0], $options);

        return array_map('html_entity_decode', $options[1]);
    }

    /**
     * This dropdown was DISTINCT `type`, so every legacy spelling showed up
     * as its own option.
     */
    public function test_dropdown_offers_normalised_categories_only(): void
    {
        $this->addProduct('Motorcycle Tire', 'Tire');
        $this->addProduct('Iridium Spark Plug', 'Engine Part');
        $this->addProduct('Rear Shock Absorber', 'Rear Shock Absorber');
        $this->addProduct('Koby De-Rust Spray', 'Maintenance Sprays');

        $this->assertSame([
            'all',
            'Engine Parts',
            'Lubricants & Maintenance',
            'Suspension & Steering',
            'Tires & Inner Tubes',
        ], $this->dropdownOptions());
    }

    /**
     * The live JSON feeds the same filter; its `category` must be the
     * canonical value the option list offers, or the client-side comparison
     * silently matches nothing.
     */
    public function test_stock_data_returns_the_canonical_category(): void
    {
        $this->addProduct('Motorcycle Tire', 'Tire');
        $this->addProduct('Koby De-Rust Spray', 'Maintenance Sprays');

        $json = $this->actingAs($this->owner)->getJson('/monitor/stock-data')->assertOk()->json();

        $categories = collect($json['branches'])
            ->flatMap(fn ($branch) => $branch['inventories'])
            ->pluck('category')
            ->all();

        sort($categories);

        $this->assertSame(
            ['Lubricants & Maintenance', 'Tires & Inner Tubes'],
            $categories
        );
    }

    public function test_monitor_is_owner_only(): void
    {
        $this->addProduct('Motorcycle Tire', 'Tire');

        $cashier = User::create([
            'name'      => 'Cashier',
            'email'     => 'cashier@example.com',
            'password'  => bcrypt('secret'),
            'role'      => 'cashier',
            'branch_id' => $this->branch->id,
        ]);

        $this->actingAs($cashier)->get('/monitor')->assertForbidden();
    }
}