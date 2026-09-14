<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Product;

$user = User::whereNotNull('branch_id')->where('is_active', true)->first();
auth()->login($user); // sets the resolver; blade uses auth in layout
Product::creating(); // no-op

$p = Product::first();

// Share a skipped ViewErrorBag so $errors in blade has a real value
$errs = new Illuminate\Support\MessageBag;
view()->share('errors', \Illuminate\Support\ViewErrorBag::class
    && false ? null : new \Illuminate\Support\ViewErrorBag);
view()->getShared()['errors'] = new Illuminate\Support\ViewErrorBag;

// Simulate the blade @php + ternary
request()->merge(['from' => 'inventory']);

try {
    $view = view('products.edit', ['product' => $p]);
    $html = $view->render();
    echo "RENDER OK bytes=".strlen($html)."\n";
    echo (strpos($html, 'Back to Inventory') !== false) ? "has 'Back to Inventory'\n" : "(no inventory label)\n";
} catch (\Throwable $e) {
    echo "RENDER FAIL: ".get_class($e)."\n";
    echo $e->getMessage()."\n";
    echo $e->getFile().':'.$e->getLine()."\n";
}
