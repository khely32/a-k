<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTransfersScopeTest extends TestCase
{
    use RefreshDatabase;

    private Branch $poblacion;

    private Branch $other;

    private Branch $third;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->poblacion = Branch::create(['branch_name' => 'Poblacion Branch', 'location' => 'X', 'is_active' => true]);
        $this->other     = Branch::create(['branch_name' => 'Moroboro Branch', 'location' => 'Y', 'is_active' => true]);
        $this->third     = Branch::create(['branch_name' => 'Iloilo City Branch', 'location' => 'Z', 'is_active' => true]);

        $this->product = Product::create([
            'name' => 'Bosny Spray Paint',
            'brand' => 'Bosny',
            'type' => 'Spray Paint',
            'price' => 160.00,
            'quantity' => 200,
        ]);
    }

    private function userOn(Branch $branch, string $role = 'staff'): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => strtolower($role).'@'.uniqid().'.example.com',
            'password' => bcrypt('secret'),
            'role' => $role,
            'branch_id' => $branch->id,
        ]);
    }

    private function transfer(int $fromId, int $toId, string $status = 'pending'): StockTransfer
    {
        return StockTransfer::create([
            'from_branch_id' => $fromId,
            'to_branch_id' => $toId,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'status' => $status,
        ]);
    }

    private function awaitingApprovalCount(User $user): int
    {
        $html = $this->actingAs($user)->get('/dashboard')->assertOk()->getContent();

        preg_match('/id="kpi-pending"[^>]*>(\d+)/', $html, $m);
        $this->assertNotEmpty($m, 'kpi-pending count not found');

        return (int) $m[1];
    }

    public function test_branch_only_sees_transfers_it_is_involved_in(): void
    {
        $staff = $this->userOn($this->poblacion);

        // Pending transfers between two other branches - invisible to Poblacion.
        $this->transfer($this->other->id, $this->third->id);
        $this->transfer($this->other->id, $this->third->id);
        $this->transfer($this->third->id, $this->other->id);

        // Pending transfers where Poblacion is the requester or the sender.
        $this->transfer($this->poblacion->id, $this->other->id);
        $this->transfer($this->other->id, $this->poblacion->id);

        $this->assertSame(2, $this->awaitingApprovalCount($staff));
    }

    public function test_branch_with_no_pending_involvement_gets_zero(): void
    {
        $staff = $this->userOn($this->poblacion);

        $this->transfer($this->other->id, $this->third->id, 'approved');
        $this->transfer($this->other->id, $this->third->id);

        $this->assertSame(0, $this->awaitingApprovalCount($staff));
    }

    public function test_scoping_applies_to_owners_dashboard_too(): void
    {
        $owner = $this->userOn($this->poblacion, 'owner');

        $this->transfer($this->other->id, $this->third->id);
        $this->transfer($this->poblacion->id, $this->other->id);

        $this->assertSame(1, $this->awaitingApprovalCount($owner));
    }
}