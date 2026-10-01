<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Sale extends Model
{
    protected $fillable = ['branch_id', 'user_id', 'total_amount', 'payment_method', 'customer_id'];

    /**
     * Limit the query to sales recorded within the current business day
     * (Asia/Manila midnight to midnight), so revenue totals reset daily.
     */
    public function scopeToday(Builder $query): Builder
    {
        $now = Carbon::now('Asia/Manila');

        return $query
            ->where('created_at', '>=', $now->copy()->startOfDay())
            ->where('created_at', '<', $now->copy()->addDay()->startOfDay());
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
