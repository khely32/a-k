<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportDateFilterTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 11, 9, 10, 0, 0, 'Asia/Manila'));

        $this->branch = Branch::create([
            'branch_name' => 'Main Branch',
            'location'    => 'Poblacion',
            'is_active'   => true,
            'is_main'     => true,
        ]);

        $this->owner = User::create([
            'name'      => 'Owner',
            'email'     => 'owner@example.com',
            'password'  => bcrypt('secret'),
            'role'      => 'owner',
            'branch_id' => $this->branch->id,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function saleAt(string $date, float $amount): Sale
    {
        $sale = new Sale([
            'branch_id'      => $this->branch->id,
            'user_id'        => $this->owner->id,
            'total_amount'   => $amount,
            'payment_method' => 'cash',
        ]);

        $sale->created_at = Carbon::parse($date.' 12:00:00', 'Asia/Manila');
        $sale->updated_at = $sale->created_at;
        $sale->save();

        return $sale;
    }

    public function test_report_data_uses_the_selected_calendar_date(): void
    {
        $this->saleAt('2026-11-09', 100.00); // today
        $this->saleAt('2026-11-08', 50.00);  // yesterday
        $this->saleAt('2026-11-01', 999.00); // outside both days

        $this->actingAs($this->owner)
            ->getJson('/reports/data?branch_id=all&date=2026-11-08')
            ->assertOk()
            ->assertJsonPath('reportDateIso', '2026-11-08')
            ->assertJsonPath('reportDateLabel', 'Yesterday')
            ->assertJsonPath('isReportDateToday', false)
            ->assertJsonPath('totalRevenue', 50)
            ->assertJsonPath('totalProducts', 0);
    }

    public function test_report_page_renders_the_selected_date_and_revenue(): void
    {
        $this->saleAt('2026-11-09', 100.00);
        $this->saleAt('2026-11-08', 50.00);

        $this->actingAs($this->owner)
            ->get('/reports?date=2026-11-08')
            ->assertOk()
            ->assertSee('value="2026-11-08"', false)
            ->assertSee('Total Revenue (Yesterday)', false)
            ->assertSee('₱50.00', false);
    }

    public function test_daily_income_period_ends_on_the_selected_date(): void
    {
        $this->saleAt('2026-11-09', 100.00); // today, outside the anchored period
        $this->saleAt('2026-11-08', 50.00);  // anchor day
        $this->saleAt('2026-11-07', 25.00);  // within the 7-day window

        $response = $this->actingAs($this->owner)
            ->getJson('/reports/daily-income?days=7&branch=all&date=2026-11-08')
            ->assertOk()
            ->assertJsonPath('anchor', '2026-11-08')
            ->assertJsonPath('is_anchor_today', false);

        $this->assertEquals(75, $response->json('period_total'));
        $this->assertEquals(50, $response->json('today.total'));
    }
}
