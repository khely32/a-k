<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosCategoryFilterTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'branch_name' => 'Moroboro Branch',
            'location'    => 'Brgy Moroboro Dingle, Ilo-ilo',
            'is_active'   => true,
            'is_main'     => true,
        ]);

        $this->user = User::create([
            'name'     => 'Cashier',
            'email'    => 'cashier@example.com',
            'password' => bcrypt('secret'),
            'role'     => 'cashier',
            'branch_id'=> $this->branch->id,
        ]);
    }

    private function addProduct(string $name, string $type, string $brand = 'Generic', int $qty = 10): Product
    {
        $product = Product::create([
            'serial_number' => 'SKU-' . strtoupper(substr(md5($name . $type), 0, 8)),
            'name'          => $name,
            'brand'         => $brand,
            'type'          => $type,
            'price'         => 100.00,
            'quantity'      => $qty,
        ]);

        Inventory::updateOrCreate(
            ['product_id' => $product->id, 'branch_id' => $this->branch->id],
            ['quantity' => $qty]
        );

        return $product;
    }

    private function search(string $category = 'all', string $search = '')
    {
        // Query string must go in the URI: getJson()'s second argument is
        // headers, not data, and silently drops the filters.
        $response = $this->actingAs($this->user)->getJson(
            '/pos/search?' . http_build_query([
                'category' => $category,
                'search'   => $search,
            ])
        );

        $response->assertOk();

        return $response->json();
    }

    private function categories(): array
    {
        $response = $this->actingAs($this->user)->getJson('/pos/categories');
        $response->assertOk();

        return $response->json();
    }

    public function test_dropdown_returns_the_fixed_categories(): void
    {
        $this->addProduct('4T Engine Oil', 'Motor Oil / Lubricants');

        $this->assertSame([
            'Bearings',
            'Body & Fairings',
            'Brake System',
            'Cooling System',
            'Drive Train & Transmission',
            'Electrical & Lighting',
            'Engine Parts',
            'Exhaust & Emissions',
            'Fasteners & Hardware',
            'Frame & Chassis',
            'Fuel System & Air Intake',
            'Handlebars & Controls',
            'Instrumentation & Gauges',
            'Lubricants & Maintenance',
            'Mirrors & Accessories',
            'Suspension & Steering',
            'Tires & Inner Tubes',
            'Wheels & Rims',
        ], $this->categories());
    }

    /**
     * The new Wheels & Rims entry has to actually return its products.
     */
    public function test_wheel_products_are_filterable_under_wheels_and_rims(): void
    {
        $this->addProduct('Mag Wheel 17', 'Wheels & Rims');
        $this->addProduct('Alloy Rim', 'Rim');
        $this->addProduct('Motorcycle Tire', 'Tire');

        $this->assertCount(2, $this->search('Wheels & Rims'));
        $this->assertCount(1, $this->search('Tires & Inner Tubes'));
        $this->assertCount(3, $this->search('all'));
    }

    /**
     * The core complaint: the same inventory was split across
     * "Lubricant", "Lubricants" and "Lubricants / Gear Oil".
     */
    public function test_redundant_type_spellings_return_the_same_items(): void
    {
        $this->addProduct('Shell Helix HX3', 'Motor Oil / Lubricants');
        $this->addProduct('4T Engine Oil', 'Lubricant');
        $this->addProduct('Gear Oil 80W-90', 'Lubricants / Gear Oil');
        $this->addProduct('Chain Lube', 'Maintenance Sprays');
        $this->addProduct('Brembo Brake Pad', 'Brake System');

        $category = 'Lubricants & Maintenance';

        $this->assertCount(4, $this->search($category));
        $this->assertCount(4, $this->search('lubricants & maintenance'));
        $this->assertCount(4, $this->search('  Lubricants & Maintenance  '));
        $this->assertCount(4, $this->search('LUBRICANTS & MAINTENANCE'));
        $this->assertCount(5, $this->search('all'));
    }

    public function test_category_selection_combines_with_text_search(): void
    {
        $this->addProduct('NTN Ball Bearing 6201', 'Bearing', 'NTN');
        $this->addProduct('KOYO Wheel Bearing 6201', 'Bearing', 'KOYO');
        $this->addProduct('Generic Bearing 6304', 'Bearing', 'Generic');
        $this->addProduct('Brembo Brake Pad', 'Brake System', 'Brembo');

        // Category alone: all bearings regardless of brand.
        $this->assertCount(3, $this->search('Bearings'));

        // Category + text narrows within the category only.
        $this->assertCount(2, $this->search('Bearings', '6201'));
        $this->assertCount(2, $this->search('Bearings', '6201'));
        $this->assertCount(1, $this->search('Bearings', 'koyo'));
        $this->assertCount(1, $this->search('Bearings', 'ntn'));

        // Same text under a different category finds nothing.
        $this->assertCount(0, $this->search('Brake System', '6201'));
    }

    public function test_text_search_is_trim_and_case_insensitive(): void
    {
        $this->addProduct('Brembo Brake Pad', 'Brake System', 'Brembo');

        foreach (['brembo', 'BREMBO', 'Brembo', '  brembo  '] as $needle) {
            $this->assertCount(1, $this->search('Brake System', $needle), "needle [{$needle}]");
            $this->assertCount(1, $this->search('all', $needle), "needle [{$needle}]");
        }

        // Whitespace-only trims to an empty query, i.e. no filter at all.
        $this->assertCount(1, $this->search('all', '   '));
        $this->assertCount(1, $this->search('all', ''));
    }

    public function test_search_covers_sku_name_brand_and_description(): void
    {
        $product = $this->addProduct('Iridium Spark Plug', 'Engine Part', 'NGK');
        $product->description = 'Long life racing spark plug';
        $product->save();

        $this->assertCount(1, $this->search('all', $product->serial_number));
        $this->assertCount(1, $this->search('all', 'iridium'));
        $this->assertCount(1, $this->search('all', 'ngk'));
        $this->assertCount(1, $this->search('all', 'racing'));
        $this->assertCount(0, $this->search('all', 'no-such-thing'));
    }

    public function test_all_categories_option_returns_every_in_stock_item(): void
    {
        $this->addProduct('4T Engine Oil', 'Motor Oil / Lubricants');
        $this->addProduct('Brembo Brake Pad', 'Brake System');
        $this->addProduct('Motorcycle Tire', 'Tire');

        foreach (['all', 'ALL', 'All', 'all ', ''] as $value) {
            $this->assertCount(3, $this->search($value), "value [{$value}]");
        }

        $total = 0;
        foreach ($this->categories() as $category) {
            $total += count($this->search($category));
        }
        $this->assertSame(3, $total, 'individual categories must partition All Categories');
    }

    public function test_products_with_zero_stock_are_not_offered(): void
    {
        $this->addProduct('4T Engine Oil', 'Motor Oil / Lubricants', 'Shell', 0);
        $this->addProduct('Chain Lube', 'Maintenance Sprays', 'Motul', 5);

        $this->assertCount(1, $this->search('all'));
        $this->assertCount(1, $this->search('Lubricants & Maintenance'));
    }

    public function test_storing_a_product_normalises_its_category(): void
    {
        // Name deliberately carries no keyword of its own, so `type` decides.
        $product = $this->addProduct('Shell Helix HX5', 'Lubricants');
        $this->assertSame('Lubricants & Maintenance', $product->fresh()->category);

        // An alias written by the UI gets canonicalised on save.
        $product->category = 'lubricants';
        $product->save();
        $this->assertSame('Lubricants & Maintenance', $product->fresh()->category);

        // Editing `type` re-derives the category instead of leaving it stale.
        $product->type = 'Brake System';
        $product->save();
        $this->assertSame('Brake System', $product->fresh()->category);

        // `type` itself is never rewritten - variant codes stay intact.
        $product->type = 'Std';
        $product->save();
        $this->assertSame('Std', $product->fresh()->type);
        $this->assertNotNull($product->fresh()->category);
    }
}
