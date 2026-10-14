<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Support\BranchNotifications;
use App\Models\StockTransfer;
use App\Models\Inventory;
use App\Models\Product;

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
                    $badges['low_stock'] = Product::where('quantity', '>', 0)->where('quantity', '<=', 5)->count();
                    $badges['out_of_stock'] = Product::where('quantity', 0)->count();
                } else {
                    $badges['pending_transfers'] = StockTransfer::where('from_branch_id', $user->branch_id)
                        ->where('status', 'pending')->count();
                    $badges['low_stock'] = Inventory::where('branch_id', $user->branch_id)
                        ->where('quantity', '>', 0)->where('quantity', '<=', 5)->count();
                    $badges['out_of_stock'] = Inventory::where('branch_id', $user->branch_id)
                        ->where('quantity', 0)->count();
                }
            }

            // Chronological, per-branch notification feed for the bell menu.
            // Scoped to the authenticated user's own branch.
            $notifications = $user ? BranchNotifications::forBranch($user->branch_id) : [];

            // "Mark all as read" stores the moment in the session; anything
            // newer than that is still unread (drives the bell badge).
            $readAt = session('notifications_read_at');
            $unread = 0;
            foreach ($notifications as $n) {
                if (! $readAt || ($n['time'] && $n['time']->gt($readAt))) {
                    $unread++;
                }
            }

            $view->with('badges', $badges)
                ->with('notifications', $notifications)
                ->with('notifUnread', $unread);
        });
    }
}
