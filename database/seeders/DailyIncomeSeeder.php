<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputOption;

class DailyIncomeSeeder extends Seeder
{
    protected const DAYS = 30;

    protected const VAT_RATE = 0.12;

    protected const PAYMENT_METHODS = ['cash', 'cash', 'cash', 'gcash'];

    protected const SALES_PER_DAY = [
        'moroboro' => [5, 10],
        'default'  => [2, 5],
    ];

    protected const LINES_PER_SALE = [1, 2];

    protected const QTY_PER_LINE = [1, 2];

    public function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Seed even if sales already exist in the period'],
        ];
    }

    public function run(): void
    {
        $todayStart = Carbon::now('Asia/Manila')->startOfDay();
        $from = $todayStart->copy()->subDays(self::DAYS - 1);

        $existing = Sale::where('created_at', '>=', $from)->count();
        if ($existing > 0 && ! $this->command?->option('force')) {
            $this->command?->warn(
                "Skipped: {$existing} sale(s) already exist in the last " . self::DAYS
                . ' days. Re-run with --force to add another set.'
            );
            return;
        }

        $branches = Branch::orderBy('id')->get();
        if ($branches->isEmpty()) {
            $this->command?->error('No branches found. Run BranchSeeder first.');
            return;
        }

        $catalog = $this->catalog();
        if ($catalog === []) {
            $this->command?->error('No products found. Run ProductSeeder first.');
            return;
        }

        $localStock = [];
        $borrowing = [];
        foreach ($branches as $branch) {
            $localStock[$branch->id] = $this->stockedProducts($branch->id);
            if ($localStock[$branch->id] === []) {
                $borrowing[] = $branch->branch_name;
            }
        }

        $cashiersByBranch = User::where('role', 'staff')->get()->groupBy('branch_id');

        mt_srand(20261001);

        $salesCount = 0;
        $itemRows = [];

        for ($dayOffset = self::DAYS - 1; $dayOffset >= 0; $dayOffset--) {
            $day = $todayStart->copy()->subDays($dayOffset);

            foreach ($branches as $branch) {
                $assortment = $localStock[$branch->id] ?: $catalog;
                $cashiers = $cashiersByBranch->get($branch->id, collect())->values();
                $isMain = $branch->isMainBranch();

                [$min, $max] = $isMain
                    ? self::SALES_PER_DAY['moroboro']
                    : self::SALES_PER_DAY['default'];

                // A branch whose own shelf is nearly empty cannot realistically
                // move much stock, regardless of how good the day looks.
                if (count($assortment) < 5) {
                    $min = 1;
                    $max = 2;
                }

                $weekday = $day->dayOfWeek;
                if ($weekday === Carbon::SUNDAY) {
                    $min = (int) ceil($min / 2);
                    $max = max($min, (int) floor($max / 2));
                } elseif ($weekday === Carbon::MONDAY) {
                    $min = (int) ceil($min * 0.75);
                    $max = max($min, (int) floor($max * 0.8));
                }

                for ($i = 0, $count = mt_rand($min, $max); $i < $count; $i++) {
                    $at = $day->copy()->setTime(mt_rand(8, 18), mt_rand(0, 59), mt_rand(0, 59));
                    if ($at->isFuture()) {
                        continue;
                    }

                    $lines = $this->buildLines($assortment);
                    if ($lines === []) {
                        continue;
                    }

                    $subtotal = round(array_sum(array_column($lines, 'subtotal')), 2);
                    $timestamp = $at->toDateTimeString();

                    // Raw insert: Sale's $fillable excludes created_at/updated_at, so the
                    // model would silently discard our backdated timestamps.
                    $saleId = DB::table('sales')->insertGetId([
                        'total_amount'   => round($subtotal * (1 + self::VAT_RATE), 2),
                        'payment_method' => self::PAYMENT_METHODS[array_rand(self::PAYMENT_METHODS)],
                        'branch_id'      => $branch->id,
                        'user_id'        => $cashiers->isNotEmpty() ? $cashiers->random()->id : null,
                        'created_at'     => $timestamp,
                        'updated_at'     => $timestamp,
                    ]);

                    foreach ($lines as $line) {
                        $itemRows[] = [
                            'sale_id'    => $saleId,
                            'product_id' => $line['product_id'],
                            'quantity'   => $line['quantity'],
                            'price'      => $line['price'],
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];
                    }

                    $salesCount++;
                }
            }
        }

        foreach (array_chunk($itemRows, 200) as $chunk) {
            DB::table('sale_items')->insert($chunk);
        }

        $this->command?->info(sprintf(
            'Created %d sales and %d line items across %d days.',
            $salesCount,
            count($itemRows),
            self::DAYS
        ));

        if ($borrowing !== []) {
            $this->command?->warn(
                'No stocked inventory for: ' . implode(', ', $borrowing)
                . '. Their sales borrow products from the shared catalogue so the'
                . ' all-branches list is complete.'
            );
        }
    }

    protected function buildLines(array $assortment): array
    {
        [$min, $max] = self::LINES_PER_SALE;
        $wanted = mt_rand($min, $max);

        $chosen = [];
        for ($l = 0; $l < $wanted; $l++) {
            $product = $this->pickProduct($assortment);
            if ($product === null || isset($chosen[$product['id']])) {
                continue;
            }
            $chosen[$product['id']] = $product;
        }

        [$qtyMin, $qtyMax] = self::QTY_PER_LINE;

        $lines = [];
        foreach ($chosen as $productId => $product) {
            $quantity = mt_rand($qtyMin, $qtyMax);
            $lines[] = [
                'product_id' => $productId,
                'quantity'   => $quantity,
                'price'      => $product['price'],
                'subtotal'   => round($product['price'] * $quantity, 2),
            ];
        }

        return $lines;
    }

    /**
     * Assortment must be sorted cheapest-first, then the squaring below biases
     * picks toward the low end so the basket mix resembles a real parts counter
     * (many cheap consumables, occasional expensive part).
     */
    protected function pickProduct(array $assortment): ?array
    {
        $count = count($assortment);
        if ($count === 0) {
            return null;
        }

        $roll = mt_rand(0, 1000) / 1000;
        $index = (int) floor($count * ($roll ** 2));

        return $assortment[min($index, $count - 1)];
    }

    protected function stockedProducts(int $branchId): array
    {
        return $this->priceOrdered(
            Inventory::where('branch_id', $branchId)
                ->where('quantity', '>', 5)
                ->pluck('product_id')
                ->all()
        );
    }

    protected function catalog(): array
    {
        return $this->priceOrdered(Product::pluck('id')->all());
    }

    protected function priceOrdered(array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        $products = Product::whereIn('id', $productIds)
            ->get(['id', 'price'])
            ->map(fn ($p) => ['id' => (int) $p->id, 'price' => (float) $p->price])
            ->sortBy('price')
            ->values()
            ->all();

        return $products;
    }
}
