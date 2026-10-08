<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryFilterTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'branch_name' => 'Moroboro Branch',
            'location' => 'Brgy Moroboro Dingle, Ilo-ilo',
            'is_active' => true,
            'is_main' => true,
        ]);

        $this->user = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('secret'),
            'role' => 'owner',
            'branch_id' => $this->branch->id,
        ]);
    }

    private function addProduct(string $name, string $type, string $brand = 'Generic'): Product
    {
        $product = Product::create([
            'serial_number' => 'SKU-'.strtoupper(substr(md5($name.$type), 0, 8)),
            'name' => $name,
            'brand' => $brand,
            'type' => $type,
            'price' => 100.00,
            'quantity' => 5,
        ]);

        Inventory::updateOrCreate(
            ['product_id' => $product->id, 'branch_id' => $this->branch->id],
            ['quantity' => 5]
        );

        return $product;
    }

    private function page(): string
    {
        return $this->actingAs($this->user)->get('/products')->assertOk()->getContent();
    }

    private function dropdownOptions(string $html): array
    {
        preg_match('/<select id="categoryFilter".*?<\/select>/s', $html, $m);
        $this->assertNotEmpty($m, 'category dropdown not found');

        preg_match_all('/<option value="([^"]*)"/', $m[0], $options);

        return array_map('html_entity_decode', $options[1]);
    }

    /**
     * The old dropdown was DISTINCT `type`, so the redundant spellings the
     * brief lists each showed up as its own option.
     */
    public function test_dropdown_offers_normalised_categories_only(): void
    {
        $this->addProduct('Motorcycle Tire', 'Tire');
        $this->addProduct('Iridium Spark Plug', 'Engine Part');
        $this->addProduct('Motorcycle Battery', 'Electrical');
        $this->addProduct('Rear Shock Absorber', 'Rear Shock Absorber');
        $this->addProduct('Koby De-Rust Spray', 'Maintenance Sprays');
        $this->addProduct('All Purpose Cleaner', 'Cleaning Supplies');
        $this->addProduct('Brembo Brake Pad', 'Brake System');

        $options = $this->dropdownOptions($this->page());

        $this->assertSame([
            '',
            'Brake System',
            'Electrical & Lighting',
            'Engine Parts',
            'Lubricants & Maintenance',
            'Suspension & Steering',
            'Tires & Inner Tubes',
        ], $options);

        foreach (['Tire', 'Engine Part', 'Electrical', 'Rear Shock Absorber', 'Maintenance Sprays', 'Cleaning Supplies'] as $legacy) {
            $this->assertNotContains($legacy, $options);
        }
    }

    /**
     * Same category written with different casing/whitespace must collapse
     * onto a single option instead of appearing twice.
     */
    public function test_case_and_whitespace_variants_are_deduplicated(): void
    {
        $this->addProduct('Chain Lube', 'Maintenance Sprays');
        $this->addProduct('Slick 4T Oil', '  maintenance sprays  ');

        $options = $this->dropdownOptions($this->page());

        $this->assertSame(['', 'Lubricants & Maintenance'], $options);
    }

    public function test_rows_expose_the_canonical_category_for_filtering(): void
    {
        $this->addProduct('Motorcycle Tire', 'Tire');

        $html = $this->page();

        $this->assertStringContainsString('data-category="tires &amp; inner tubes"', $html);
        $this->assertStringContainsString('Tires &amp; Inner Tubes', $html);
    }
}
