<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\MonitorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');

Route::get('/register', [LoginController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [LoginController::class, 'register'])->middleware('throttle:5,1');

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Forgot Password (email verification code)
|--------------------------------------------------------------------------
*/
Route::prefix('forgot-password')->name('forgot.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showEmailForm'])->name('email');
    Route::post('/send-code', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendCode'])->middleware('throttle:3,10')->name('send');
    Route::get('/enter-code', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showCodeForm'])->name('code');
    Route::post('/verify-code', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'verifyCode'])->middleware('throttle:5,10')->name('code.verify');
    Route::get('/reset', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showResetForm'])->name('reset');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'resetPassword'])->middleware('throttle:5,10')->name('reset.update');
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Requires Authentication)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Branch Status Check (Lightweight polling endpoint)
    |--------------------------------------------------------------------------
    */
    Route::get('/api/branch-status', function () {
        $user = auth()->user();
        if ($user->role === 'owner') {
            return response()->json(['active' => true, 'is_owner' => true]);
        }
        if (empty($user->branch_id)) {
            return response()->json(['active' => true, 'is_owner' => false]);
        }
        $branch = \App\Models\Branch::find($user->branch_id);
        return response()->json([
            'active'   => $branch ? $branch->is_active : false,
            'is_owner' => false,
        ]);
    })->name('api.branch-status');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    */
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory/store', [InventoryController::class, 'store'])->name('inventory.store');

    /*
    |--------------------------------------------------------------------------
    | POS
    |--------------------------------------------------------------------------
    */
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/search', [PosController::class, 'search'])->name('pos.search');
    Route::get('/pos/categories', [PosController::class, 'categories'])->name('pos.categories');
    Route::post('/pos/add', [PosController::class, 'addToCart'])->name('pos.addToCart');
    Route::get('/pos/cart', [PosController::class, 'getCart'])->name('pos.getCart');
    Route::post('/pos/remove', [PosController::class, 'removeFromCart'])->name('pos.removeFromCart');
    Route::post('/pos/qty', [PosController::class, 'updateQty'])->name('pos.updateQty');
    Route::post('/pos/clear', [PosController::class, 'clearCart'])->name('pos.clearCart');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');

    /*
    |--------------------------------------------------------------------------
    | Stock Transfers
    |--------------------------------------------------------------------------
    */
    Route::get('/transfers', [StockTransferController::class, 'index'])->name('transfers.index');
    Route::get('/transfers/create', [StockTransferController::class, 'create'])->name('transfers.create');
    Route::post('/transfers/store', [StockTransferController::class, 'store'])->name('transfers.store');
    Route::get('/transfers/check-stock', [StockTransferController::class, 'checkStock'])->name('transfers.checkStock');
    Route::get('/transfer-history', [StockTransferController::class, 'history'])->name('transfers.history');
    Route::patch('/transfers/{id}/confirm', [StockTransferController::class, 'confirmReceipt'])->name('transfers.confirm');
    Route::patch('/transfers/{id}/approve', [StockTransferController::class, 'approve'])->name('transfers.approve');
    Route::patch('/transfers/{id}/reject', [StockTransferController::class, 'reject'])->name('transfers.reject');

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/data', [ReportController::class, 'reportData'])->name('reports.data');
    Route::get('/reports/branch-analytics', [ReportController::class, 'branchAnalytics'])->name('reports.branch-analytics');
    Route::get('/reports/transaction-history', [ReportController::class, 'transactionHistory'])->name('reports.transaction-history');
    Route::get('/reports/transaction-data', [ReportController::class, 'transactionData'])->name('reports.transaction-data');

    /*
    |--------------------------------------------------------------------------
    | Branches
    |--------------------------------------------------------------------------
    */
    Route::resource('branches', BranchController::class);

    /*
    |--------------------------------------------------------------------------
    | Real-Time Monitor (Owner Only)
    |--------------------------------------------------------------------------
    */
    Route::get('/monitor', [MonitorController::class, 'index'])->name('monitor.index');
    Route::get('/monitor/stock-data', [MonitorController::class, 'stockData'])->name('monitor.stock-data');

    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */
    Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
    Route::resource('products', ProductController::class);
    Route::get('/category-sizes/{category}', [ProductController::class, 'getSizes'])->name('category.sizes');

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */
    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::post('/users/{user}/reveal-password', [\App\Http\Controllers\UserController::class, 'revealPassword'])->name('users.revealPassword');
    Route::post('/users/{user}/reset-password', [\App\Http\Controllers\UserController::class, 'resetPassword'])->name('users.resetPassword');
    Route::post('/users/{user}/update-username', [\App\Http\Controllers\UserController::class, 'updateUsername'])->name('users.updateUsername');
    Route::post('/branches/{branch}/toggle', [\App\Http\Controllers\UserController::class, 'toggleBranch'])->name('branches.toggle');

}); // This is now the ONLY closing bracket for the group block