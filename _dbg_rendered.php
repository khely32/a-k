<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\ViewErrorBag;
use App\Models\User;
use App\Models\Product;

view()->share('errors', new ViewErrorBag);

$user = User::whereNotNull('branch_id')->first();
auth()->login($user_vec ?? $user);

// mimic request('from') === 'inventory'
app('request')->merge(['from' => 'inventory']);

$p = Product::first();
echo "Rendering products.edit for product {$p->id} (from=inventory)\n";
try {
    $html = view('products.edit', ['product' => $p])->render();
    echo "RENDER OK ".strlen($html)." bytes\n";
    echo (str_contains($html, 'Back to Inventory') ? "HAS 'Back to Inventory'\n" : "NO inventory label\n");
    echo (str_contains($html, 'Update Product') ? "HAS submit button\n" : "NO submit\n");
} catch (\Throwable $e) {
    echo "RENDER FAIL: ".get_class($e)."\n".$e->getMessage()."\n"
        .$e->getFile().':'.$e->getLine()."\n";
}