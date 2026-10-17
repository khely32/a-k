<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosReceiptTest extends TestCase
{
    use RefreshDatabase;

    private function makeBranch(string $name = 'Test Branch'): Branch
    {
        return Branch::create([
            'branch_name' => $name,
            'location' => '123 Test Street',
            'is_active' => true,
        ]);
    }

    private function makeUser(Branch $branch, string $role = 'staff'): User
    {
        return User::create([
            'name' => 'Cashier One',
            'email' => $role.'-'.uniqid().'@example.com',
            'password' => bcrypt('secret'),
            'role' => $role,
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);
    }

    private function makeSale(Branch $branch, User $user, int $qty = 2, float $price = 100): Sale
    {
        $product = Product::create([
            'serial_number' => 'AK-TEST-'.uniqid(),
            'name' => 'Brake Pad',
            'price' => $price,
        ]);

        $sale = Sale::create([
            'total_amount' => round($price * $qty * 1.12, 2),
            'payment_method' => 'cash',
            'user_id' => $user->id,
            'branch_id' => $branch->id,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => $qty,
            'price' => $price,
        ]);

        return $sale;
    }

    public function test_guest_cannot_view_a_receipt(): void
    {
        $branch = $this->makeBranch();
        $cashier = $this->makeUser($branch);
        $sale = $this->makeSale($branch, $cashier);

        $this->get(route('pos.receipt', $sale))->assertRedirect('/login');
    }

    public function test_cashier_can_view_a_receipt_from_their_own_branch(): void
    {
        $branch = $this->makeBranch();
        $cashier = $this->makeUser($branch);
        $sale = $this->makeSale($branch, $cashier);

        $this->actingAs($cashier)
            ->get(route('pos.receipt', $sale))
            ->assertOk()
            ->assertSee('A&K PARTS', false)
            ->assertSee('Test Branch')
            ->assertSee('Brake Pad')
            ->assertSee('224.00');
    }

    public function test_cashier_cannot_view_a_receipt_from_another_branch(): void
    {
        $ownBranch = $this->makeBranch('Own Branch');
        $otherBranch = $this->makeBranch('Other Branch');

        $cashier = $this->makeUser($ownBranch);
        $otherCashier = $this->makeUser($otherBranch);
        $sale = $this->makeSale($otherBranch, $otherCashier);

        $this->actingAs($cashier)
            ->get(route('pos.receipt', $sale))
            ->assertForbidden();
    }

    public function test_owner_can_view_a_receipt_from_any_branch(): void
    {
        $branch = $this->makeBranch('Far Branch');
        $staff = $this->makeUser($branch);
        $sale = $this->makeSale($branch, $staff);

        $owner = $this->makeUser($this->makeBranch('Main'), 'owner');

        $this->actingAs($owner)
            ->get(route('pos.receipt', $sale))
            ->assertOk()
            ->assertSee('Far Branch');
    }

    public function test_checkout_returns_the_receipt_url_and_persists_the_sale(): void
    {
        $branch = $this->makeBranch();
        $cashier = $this->makeUser($branch);

        $product = Product::create([
            'serial_number' => 'AK-CHECKOUT-1',
            'name' => 'Engine Oil',
            'price' => 100,
            'quantity' => 5,
        ]);

        Inventory::updateOrCreate(
            ['product_id' => $product->id, 'branch_id' => $branch->id],
            ['quantity' => 5]
        );

        $response = $this->actingAs($cashier)
            ->withSession([
                'cart' => [
                    $product->id => [
                        'name' => 'Engine Oil',
                        'sku' => 'AK-CHECKOUT-1',
                        'price' => 100,
                        'qty' => 2,
                    ],
                ],
            ])
            ->postJson('/pos/checkout');

        $response->assertOk()->assertJsonStructure(['success', 'sale_id', 'receipt_url']);

        $this->assertDatabaseHas('sales', ['total_amount' => 224.00]);
        $this->assertSame(3, (int) Inventory::first()->fresh()->quantity);
        $this->assertSame(3, (int) $product->fresh()->quantity);
    }
}
