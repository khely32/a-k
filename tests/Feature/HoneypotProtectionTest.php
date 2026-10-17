<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HoneypotProtectionTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private Branch $otherBranch;

    private Product $product;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
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

        $this->user = User::create([
            'name' => 'Staff',
            'email' => 'staff@example.com',
            'password' => bcrypt('secret'),
            'role' => 'staff',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
    }

    public function test_login_form_renders_honeypot_fields(): void
    {
        $html = $this->get('/login')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/name="my_name_[^"]+"/', $html);
        $this->assertStringContainsString('name="valid_from"', $html);
    }

    public function test_transfer_form_renders_honeypot_fields(): void
    {
        $html = $this->actingAs($this->user)->get('/transfers/create')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/name="my_name_[^"]+"/', $html);
        $this->assertStringContainsString('name="valid_from"', $html);
    }

    public function test_login_submission_with_filled_honeypot_field_is_rejected(): void
    {
        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'secret',
            'my_name' => 'spam-bot',
        ]);

        $response->assertOk();
        $this->assertSame('', $response->getContent());
        $this->assertGuest();
    }

    public function test_login_submission_with_valid_honeypot_fields_is_not_rejected(): void
    {
        $html = $this->get('/login')->assertOk()->getContent();

        preg_match('/name="(my_name_[^"]+)"/', $html, $nameField);
        preg_match('/name="valid_from"[^>]*value="([^"]+)"/', $html, $validFrom);

        $this->assertNotEmpty($nameField[1]);
        $this->assertNotEmpty($validFrom[1]);

        $this->travel(2)->seconds();

        $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'secret',
            $nameField[1] => '',
            'valid_from' => $validFrom[1],
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($this->user);
    }

    public function test_transfer_submission_with_filled_honeypot_field_is_rejected(): void
    {
        $response = $this->actingAs($this->user)->post('/transfers/store', [
            'from_branch_id' => $this->otherBranch->id,
            'to_branch_id' => $this->branch->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'my_name' => 'spam-bot',
        ]);

        $response->assertOk();
        $this->assertSame('', $response->getContent());
        $this->assertDatabaseCount('stock_transfers', 0);
    }

    public function test_method_spoofed_edit_submission_with_filled_honeypot_field_is_rejected(): void
    {
        $response = $this->actingAs($this->user)->call('POST', '/products/'.$this->product->id, [
            '_method' => 'PUT',
            'name' => 'Changed Name',
            'my_name' => 'spam-bot',
        ]);

        $response->assertOk();
        $this->assertSame('', $response->getContent());
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'name' => 'Bosny Spray Paint',
        ]);
    }
}
