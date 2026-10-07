<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryPaginationRetentionTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;
    private User $user;
    private Product $product;

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

        $this->product = Product::create([
            'serial_number' => 'AK-BR04-LMFP6S',
            'name'          => 'Brembo Brake Pad',
            'brand'         => 'Brembo',
            'type'          => 'Brake System',
            'price'         => 100.00,
            'quantity'      => 5,
        ]);

        Inventory::updateOrCreate(
            ['product_id' => $this->product->id, 'branch_id' => $this->branch->id],
            ['quantity' => 5]
        );
    }

    public function test_update_redirects_back_to_inventory_with_page_and_filters(): void
    {
        $response = $this->actingAs($this->user)->put(route('products.update', $this->product), [
            'name'        => 'Brembo Brake Pad',
            'brand'       => 'Brembo',
            'type'        => 'Brake System',
            'quantity'    => 100,
            'price'       => 100.00,
            'description' => '',
            'color'       => '',
            'size'        => '',
            'from'        => 'inventory',
            'page'        => '9',
            'search'      => 'brake',
            'category'    => 'Brake System',
            'brand'       => 'Brembo',
            'color'       => '',
        ]);

        $response->assertSessionHas('success');
        $response->assertRedirect(route('inventory.index', [
            'page'     => '9',
            'search'   => 'brake',
            'category' => 'Brake System',
            'brand'    => 'Brembo',
        ]));
    }

    public function test_update_without_from_field_still_redirects_to_products(): void
    {
        $response = $this->actingAs($this->user)->put(route('products.update', $this->product), [
            'name'        => 'Brembo Brake Pad',
            'brand'       => 'Brembo',
            'type'        => 'Brake System',
            'quantity'    => 10,
            'price'       => 100.00,
            'description' => '',
            'color'       => '',
            'size'        => '',
        ]);

        $response->assertRedirect(route('products.index'));
    }

    public function test_edit_page_forwards_page_and_filter_params_back_via_form_and_links(): void
    {
        $html = $this->actingAs($this->user)->get(
            route('products.edit', ['product' => $this->product, 'from' => 'inventory', 'page' => '9', 'category' => 'Brake System'])
        )->assertOk()->getContent();

        // Hidden inputs on the update form carry the state.
        $this->assertMatchesRegularExpression(
            '/<input type="hidden" name="page" value="9">/',
            $html
        );
        $this->assertMatchesRegularExpression(
            '/<input type="hidden" name="category" value="Brake System">/',
            $html
        );

        // "Back to Inventory" keeps the page (Blade escapes & -> &amp; and
        // http_build_query encodes the space as +).
        $this->assertMatchesRegularExpression(
            '/href="' . preg_quote(route('inventory.index') . '?page=9&amp;category=Brake+System', '/') . '"/',
            $html
        );
    }

    public function test_inventory_page_stamps_edit_links_with_current_view_state(): void
    {
        $html = $this->actingAs($this->user)->get('/inventory')->assertOk()->getContent();

        // The data-edit-base attribute drives the JS syncUrl() rewrite, and
        // the initial href already points at the edit route from inventory.
        $this->assertMatchesRegularExpression(
            '/data-edit-base="' . preg_quote(route('products.edit', $this->product), '/') . '"/',
            $html
        );
    }
}