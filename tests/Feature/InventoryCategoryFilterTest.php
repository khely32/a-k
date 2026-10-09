<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
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
     * The dropdown follows the stored categories (via ProductCategory::options),
     * so legacy type spellings and variant codes never become entries while
     * the resolved groups always are.
     */
    public function test_dropdown_offers_resolved_categories_only(): void
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
            ['', 'Bearings', 'Lubricants &amp; Maintenance'],
            $options[1]
        );

        $decoded = array_map('html_entity_decode', $options[1]);

        // The old type values must not be selectable any more.
        foreach (['Motor Oil / Lubricants', 'Lubricant', 'Lubricants / Gear Oil', 'Std'] as $legacy) {
            $this->assertNotContains($legacy, $decoded);
        }
    }

    /**
     * A brand-new type grows the dropdown the moment its product is saved:
     * the category is stored verbatim and offered as a filter.
     */
    public function test_a_brand_new_type_grows_the_dropdown(): void
    {
        $product = $this->addProduct('Custom Seat Foam', 'Seats & Upholstery');

        $this->assertSame('Seats & Upholstery', $product->category);

        $html = $this->actingAs($this->user)->get('/inventory')->assertOk()->getContent();

        $this->assertStringContainsString(
            '<option value="Seats &amp; Upholstery">Seats &amp; Upholstery</option>',
            $html
        );
        $this->assertStringContainsString('data-category="seats &amp; upholstery"', $html);
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

    /**
     * Wheels and rims are their own dropdown entry, and the products filed
     * under it carry that category on their row for the client-side filter.
     */
    public function test_wheel_and_rim_products_get_their_own_entry(): void
    {
        $this->addProduct('Mag Wheel 17', 'Wheels & Rims');
        $this->addProduct('Alloy Rim', 'Rim');

        $html = $this->actingAs($this->user)->get('/inventory')->assertOk()->getContent();

        $this->assertStringContainsString(
            '<option value="Wheels &amp; Rims">Wheels &amp; Rims</option>',
            $html
        );
        $this->assertSame(2, substr_count($html, 'data-category="wheels &amp; rims"'));
    }
}
