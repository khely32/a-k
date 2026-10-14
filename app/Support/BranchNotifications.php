<?php

namespace App\Support;

use App\Models\Inventory;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\Schema;

/**
 * Builds the chronological notification feed shown in the top-right bell
 * dropdown. Every entry is scoped to a single branch so a branch user only
 * ever sees alerts that involve their own stock.
 *
 * Each entry is a plain array (not a model) so the Blade layout can render it
 * without reaching back into the database:
 *
 *   ['type','icon','title','message','url','time' => \Illuminate\Support\Carbon|null]
 */
class BranchNotifications
{
    /** Anything at or below this quantity is "low" (matches the inventory UI). */
    public const LOW_STOCK_THRESHOLD = 10;

    /**
     * @return array<int, array{type:string,icon:string,title:string,message:string,url:string,time:\Illuminate\Support\Carbon|null}>
     */
    public static function forBranch(?int $branchId): array
    {
        if (! $branchId) {
            return [];
        }

        try {
            $feed = array_merge(
                self::transferRequests($branchId),
                self::transferConfirmations($branchId),
                self::lowStock($branchId),
                self::outOfStock($branchId),
            );
        } catch (\Throwable $e) {
            // A half-migrated database should never break the whole chrome.
            return [];
        }

        // Newest first; items with no timestamp fall to the bottom.
        usort($feed, function (array $a, array $b) {
            $at = $a['time'] ? $a['time']->getTimestamp() : 0;
            $bt = $b['time'] ? $b['time']->getTimestamp() : 0;

            return $bt <=> $at;
        });

        return array_slice($feed, 0, 25);
    }

    /**
     * Incoming transfer requests this branch has to act on. The branch that
     * owns the stock is the source, so the requesting branch is the
     * destination.
     */
    private static function transferRequests(int $branchId): array
    {
        if (! Schema::hasTable('stock_transfers')) {
            return [];
        }

        return StockTransfer::with(['toBranch'])
            ->where('from_branch_id', $branchId)
            ->where('status', 'pending')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (StockTransfer $t) => [
                'type'    => 'transfer',
                'icon'    => 'bi-arrow-left-right',
                'title'   => 'Stock transfer requested',
                'message' => ($t->toBranch?->label ?? 'A branch').' requested stock transfer '.self::code($t->id).'.',
                'url'     => route('transfers.index'),
                'time'    => $t->created_at,
            ])
            ->all();
    }

    /** Processed transfers (approved / received) this branch was part of. */
    private static function transferConfirmations(int $branchId): array
    {
        if (! Schema::hasTable('stock_transfers')) {
            return [];
        }

        return StockTransfer::with(['fromBranch', 'toBranch'])
            ->whereIn('status', ['approved', 'received'])
            ->where(function ($q) use ($branchId) {
                $q->where('from_branch_id', $branchId)
                  ->orWhere('to_branch_id', $branchId);
            })
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(function (StockTransfer $t) use ($branchId) {
                $code = self::code($t->id);

                if ($t->status === 'received') {
                    $msg = $code.' was received by '.($t->toBranch?->label ?? 'the destination branch').'.';
                } elseif ((int) $t->from_branch_id === $branchId) {
                    $msg = $code.' was approved and sent to '.($t->toBranch?->label ?? 'another branch').'.';
                } else {
                    $msg = $code.' from '.($t->fromBranch?->label ?? 'another branch').' was approved.';
                }

                return [
                    'type'    => 'completed',
                    'icon'    => 'bi-check-circle',
                    'title'   => 'Stock transfer completed',
                    'message' => $msg,
                    'url'     => route('transfers.history'),
                    'time'    => $t->updated_at,
                ];
            })
            ->all();
    }

    /** Items running low at this branch. One notification per product. */
    private static function lowStock(int $branchId): array
    {
        if (! Schema::hasTable('inventories')) {
            return [];
        }

        return Inventory::with('product')
            ->where('branch_id', $branchId)
            ->where('quantity', '>', 0)
            ->where('quantity', '<=', self::LOW_STOCK_THRESHOLD)
            ->orderBy('quantity')
            ->limit(8)
            ->get()
            ->map(fn (Inventory $i) => [
                'type'    => 'low',
                'icon'    => 'bi-exclamation-triangle',
                'title'   => 'Low stock alert',
                'message' => ($i->product?->name ?? 'An item').' is running low ('.$i->quantity.' left).',
                'url'     => route('inventory.index'),
                'time'    => $i->updated_at,
            ])
            ->all();
    }

    /** Items with no stock at all at this branch. One notification per item. */
    private static function outOfStock(int $branchId): array
    {
        if (! Schema::hasTable('inventories')) {
            return [];
        }

        return Inventory::with('product')
            ->where('branch_id', $branchId)
            ->where('quantity', 0)
            ->latest('updated_at')
            ->limit(8)
            ->get()
            ->map(fn (Inventory $i) => [
                'type'    => 'out',
                'icon'    => 'bi-x-octagon',
                'title'   => 'Out of stock alert',
                'message' => ($i->product?->name ?? 'An item').' is out of stock.',
                'url'     => route('inventory.index'),
                'time'    => $i->updated_at,
            ])
            ->all();
    }

    private static function code(int $id): string
    {
        return '#TRF-'.str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }
}
