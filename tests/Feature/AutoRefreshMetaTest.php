<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoRefreshMetaTest extends TestCase
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

        $this->product = Product::create([
            'name' => 'Bosny Spray Paint',
            'brand' => 'Bosny',
            'type' => 'Spray Paint',
            'price' => 160.00,
            'quantity' => 10,
            'branch_id' => $this->branch->id,
        ]);
    }

    public function test_index_pages_carry_the_auto_refresh_meta(): void
    {
        $html = $this->actingAs($this->user)->get('/products')->assertOk()->getContent();

        $this->assertStringContainsString('<meta http-equiv="refresh" content="5">', $html);
    }

    public function test_create_edit_and_pos_pages_are_excluded_from_refresh(): void
    {
        foreach (['/products/create', '/products/'.$this->product->id.'/edit', '/pos'] as $uri) {
            $html = $this->actingAs($this->user)->get($uri)->assertOk()->getContent();

            $this->assertStringNotContainsString(
                '<meta http-equiv="refresh" content="5">',
                $html,
                "expected {$uri} to be excluded from auto-refresh"
            );
        }
    }
}