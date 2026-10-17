<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockTransferCreateTest extends TestCase
{
    use RefreshDatabase;

    private Branch $homeBranch;

    private Branch $otherBranch;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->homeBranch = Branch::create([
            'branch_name' => 'Moroboro Branch',
            'location' => 'Brgy Moroboro Dingle, Ilo-ilo',
            'is_active' => true,
        ]);

        $this->otherBranch = Branch::create([
            'branch_name' => 'Iloilo City Branch',
            'location' => 'Iloilo City Proper',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'name' => 'Bosny Spray Paint',
            'brand' => 'Bosny',
            'type' => 'Spray Paint',
            'price' => 160.00,
            'quantity' => 200,
        ]);
    }

    private function createUser(string $role, int $branchId): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => strtolower($role).'@example.com',
            'password' => bcrypt('secret'),
            'role' => $role,
            'branch_id' => $branchId,
        ]);
    }

    private function sourceBranchOptions(string $html): array
    {
        preg_match('/<select name="from_branch_id".*?<\/select>/s', $html, $m);
        $this->assertNotEmpty($m, 'from_branch_id dropdown not found');

        preg_match_all('/<option value="([^"]*)"/', $m[0], $options);

        return array_map('html_entity_decode', $options[1]);
    }

    private function postTransfer(User $user, array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($user)->post('/transfers/store', array_merge([
            'from_branch_id' => $this->otherBranch->id,
            'to_branch_id' => $this->homeBranch->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
        ], $overrides));
    }

    public function test_non_owner_source_dropdown_excludes_their_own_branch(): void
    {
        $user = $this->createUser('staff', $this->homeBranch->id);

        $html = $this->actingAs($user)->get('/transfers/create')->assertOk()->getContent();

        $options = $this->sourceBranchOptions($html);

        $this->assertNotContains((string) $this->homeBranch->id, $options);
        $this->assertContains((string) $this->otherBranch->id, $options);
    }

    public function test_owner_source_dropdown_still_lists_every_branch(): void
    {
        $owner = $this->createUser('owner', $this->homeBranch->id);

        $html = $this->actingAs($owner)->get('/transfers/create')->assertOk()->getContent();

        $options = $this->sourceBranchOptions($html);

        $this->assertContains((string) $this->homeBranch->id, $options);
        $this->assertContains((string) $this->otherBranch->id, $options);
    }

    public function test_store_rejects_requesting_stock_from_own_branch(): void
    {
        $user = $this->createUser('staff', $this->homeBranch->id);

        $this->postTransfer($user, [
            'from_branch_id' => $this->homeBranch->id,
            'to_branch_id' => $this->homeBranch->id,
        ])
            ->assertSessionHasErrors('from_branch_id');

        $this->assertDatabaseCount('stock_transfers', 0);
    }

    public function test_non_owner_can_request_stock_from_another_branch(): void
    {
        $user = $this->createUser('staff', $this->homeBranch->id);

        $this->postTransfer($user)
            ->assertRedirect(route('transfers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('stock_transfers', [
            'from_branch_id' => $this->otherBranch->id,
            'to_branch_id' => $this->homeBranch->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'status' => 'pending',
        ]);
    }

    public function test_owner_store_rejects_same_source_and_destination(): void
    {
        $owner = $this->createUser('owner', $this->homeBranch->id);

        $this->postTransfer($owner, [
            'from_branch_id' => $this->homeBranch->id,
            'to_branch_id' => $this->homeBranch->id,
        ])
            ->assertSessionHasErrors('from_branch_id');

        $this->assertDatabaseCount('stock_transfers', 0);
    }

    public function test_store_allows_owner_to_move_between_two_different_branches(): void
    {
        $owner = $this->createUser('owner', $this->homeBranch->id);

        $this->postTransfer($owner)
            ->assertRedirect(route('transfers.index'));

        $this->assertDatabaseHas('stock_transfers', [
            'from_branch_id' => $this->otherBranch->id,
            'to_branch_id' => $this->homeBranch->id,
        ]);
    }

    public function test_history_page_renders_the_dark_records_card(): void
    {
        $owner = $this->createUser('owner', $this->homeBranch->id);

        StockTransfer::create([
            'from_branch_id' => $this->otherBranch->id,
            'to_branch_id' => $this->homeBranch->id,
            'product_id' => $this->product->id,
            'quantity' => 7,
            'status' => 'pending',
        ]);

        $this->actingAs($owner)
            ->get('/transfer-history')
            ->assertOk()
            ->assertSee('All Transfer Records')
            ->assertSee('hist-card', false)
            ->assertSee('hist-table', false)
            ->assertDontSee('class="card shadow"', false)
            ->assertDontSee('class="card-body"', false);
    }
}