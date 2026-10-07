<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryDeleteTest extends TestCase
{
    use RefreshDatabase;

    private Branch $mainBranch;
    private Branch $otherBranch;
    private User $user;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mainBranch = Branch::create([
            'branch_name' => 'Main Branch',
            'location'    => 'Moroboro',
            'is_active'   => true,
            'is_main'     => true,
        ]);

        $this->otherBranch = Branch::create([
            'branch_name' => 'Satellite Branch',
            'location'    => 'Iloilo',
            'is_active'   => true,
            'is_main'     => false,
        ]);

        $this->user = User::create([
            'name'      => 'Staff',
            'email'     => 'staff@example.com',
            'password'  => bcrypt('secret'),
            'role'      => 'cashier',
            'branch_id' => $this->mainBranch->id,
        ]);

        // Product accounts for BOTH branches (the created hook mirrors this
        // for new products; seeded here explicitly like the legacy rows).
        $this->product = Product::create([
            'serial_number' => 'AK-BR04-LMFP6S',
            'name'          => 'Brembo Brake Pad',
            'brand'         => 'Brembo',
            'type'          => 'Brake System',
            'price'         => 100.00,
            'quantity'      => 5,
        ]);

        foreach ([$this->mainBranch, $this->otherBranch] as $branch) {
            Inventory::updateOrCreate(
                ['product_id' => $this->product->id, 'branch_id' => $branch->id],
                ['quantity' => 5]
            );
        }
    }

    public function test_delete_from_inventory_returns_json_for_ajax_and_stays_in_place(): void
    {
        $response = $this->actingAs($this->user)
            ->withHeaders(['Accept' => 'application/json'])
            ->delete(route('products.destroy', $this->product), [
                'from' => 'inventory',
            ]);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertSessionMissing('success');

        // Product and its rows for every branch are gone.
        $this->assertNull($this->product->fresh());
        $this->assertDatabaseCount('inventories', 0);
    }

    public function test_delete_from_inventory_redirects_back_to_inventory_with_page_and_filters(): void
    {
        $response = $this->actingAs($this->user)->delete(route('products.destroy', $this->product), [
            'from'     => 'inventory',
            'page'     => '9',
            'search'   => 'brake',
            'category' => 'Brake System',
            'brand'    => 'Brembo',
            'color'    => '',
        ]);

        $response->assertRedirect(route('inventory.index', [
            'page'     => '9',
            'search'   => 'brake',
            'category' => 'Brake System',
            'brand'    => 'Brembo',
        ]));
        $this->assertDatabaseMissing('products', ['id' => $this->product->id]);
    }

    public function test_delete_without_from_field_still_redirects_to_products(): void
    {
        $response = $this->actingAs($this->user)->delete(route('products.destroy', $this->product));

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseMissing('products', ['id' => $this->product->id]);
    }

    public function test_inventory_delete_form_forwards_inventory_state(): void
    {
        $html = $this->actingAs($this->user)->get('/inventory')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<form class="act-del-form" action="' . preg_quote(route('products.destroy', $this->product), '/') . '" method="POST">/',
            $html
        );
        $this->assertMatchesRegularExpression(
            '/<input type="hidden" name="from" value="inventory">/',
            $html
        );
    }
}