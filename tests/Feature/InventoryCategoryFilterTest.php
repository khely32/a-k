<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Support\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryCategoryFilterTest extends TestCase
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
            'name'      => 'Staff',
            'email'     => 'staff@example.com',
            'password'  => bcrypt('secret'),
            'role'      => 'cashier',
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

    /**
     * The dropdown used to be DISTINCT type, so variant codes and spelling
     * variants leaked in as selectable "categories".
     */
    public function test_dropdown_offers_only_the_canonical_categories(): void
    {
        $this->addProduct('Shell Helix HX3', 'Motor Oil / Lubricants');
        $this->addProduct('4T Engine Oil', 'Lubricant');
        $this->addProduct('Gear Oil 80W-90', 'Lubricants / Gear Oil');
        $this->addProduct('NTN Ball Bearing', 'Std');

        $html = $this->actingAs($this->user)->get('/inventory')->assertOk()->getContent();

        preg_match(
            '/<select id="category-filter".*?<\/select>/s',
            $html,
            $m
        );

        $this->assertNotEmpty($m, 'category dropdown not found');

        preg_match_all('/<option value="([^"]*)"/', $m[0], $options);

        // Blade HTML-escapes `&`, so compare against the escaped form.
        $this->assertSame(
            array_merge([''], array_map('e', ProductCategory::ALL)),
            $options[1]
        );

        $decoded = array_map('html_entity_decode', $options[1]);

        // The old type values must not be selectable any more.
        foreach (['Motor Oil / Lubricants', 'Lubricant', 'Lubricants / Gear Oil', 'Std'] as $legacy) {
            $this->assertNotContains($legacy, $decoded);
        }
    }

    public function test_rows_carry_the_canonical_category_for_client_side_filtering(): void
    {
        $product = $this->addProduct('Koyo Bearing 6201', 'Bearing');
        $this->addProduct('Shell Helix HX3', 'Motor Oil / Lubricants');

        $html = $this->actingAs($this->user)->get('/inventory')->assertOk()->getContent();

        $this->assertStringContainsString(
            'data-category="bearings"',
            $html,
            "row for {$product->name} is missing its data-category"
        );
        $this->assertStringContainsString('data-category="lubricants &amp; maintenance"', $html);
    }

    public function test_products_without_stock_are_still_listed(): void
    {
        $this->addProduct('Iridium Spark Plug', 'Engine Part');

        $html = $this->actingAs($this->user)->get('/inventory')->assertOk()->getContent();

        $this->assertStringContainsString('Iridium Spark Plug', $html);
    }
}
