<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\StockTransfer;
use App\Models\Inventory;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $user = auth()->user();
            $badges = [];

            if ($user) {
                if ($user->role === 'owner') {
                    $badges['pending_transfers'] = StockTransfer::where('status', 'pending')->count();
                    $badges['low_stock'] = Inventory::where('quantity', '>', 0)->where('quantity', '<=', 5)->count();
                    $badges['out_of_stock'] = Inventory::where('quantity', 0)->count();
                } else {
                    $badges['pending_transfers'] = StockTransfer::where('from_branch_id', $user->branch_id)
                        ->where('status', 'pending')->count();
                    $badges['low_stock'] = Inventory::where('branch_id', $user->branch_id)
                        ->where('quantity', '>', 0)->where('quantity', '<=', 5)->count();
                    $badges['out_of_stock'] = Inventory::where('branch_id', $user->branch_id)
                        ->where('quantity', 0)->count();
                }
            }

            $view->with('badges', $badges);
        });
    }
}
