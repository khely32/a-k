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

    public function test_branch_dashboard_only_counts_transfers_awaiting_its_approval(): void
    {
        $staff = $this->userOn($this->poblacion);

        // Transfer from another branch TO Poblacion, still PENDING and AWAITING
        // that OTHER branch's approval (because status pending means the source
        // hasn't approved yet). It should NOT count on Poblacion's dashboard.
        $this->transfer($this->other->id, $this->poblacion->id); // pending; source is Other

        // Transfer from Poblacion TO another branch, PENDING — now Poblacion
        // is the source and it's awaiting Poblacion's approval → counts.
        $this->transfer($this->poblacion->id, $this->other->id); // pending; source is Poblacion

        // Another incoming pending to Poblacion (also awaiting Other's approval)
        $this->transfer($this->third->id, $this->poblacion->id);

        $this->assertSame(1, $this->awaitingApprovalCount($staff));
    }

    public function test_poblacion_with_no_incoming_requests_to_approve_shows_zero(): void
    {
        $staff = $this->userOn($this->poblacion);

        // Only incoming, pending — awaiting other branches' approvals, not Poblacion's.
        $this->transfer($this->other->id, $this->poblacion->id);
        $this->transfer($this->third->id, $this->poblacion->id);

        $this->assertSame(0, $this->awaitingApprovalCount($staff));
    }
}