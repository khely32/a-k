<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Optional but clean
use App\Models\Branch;
use App\Models\Product;
use App\Models\User;

class StockTransfer extends Model
{
    protected $fillable = [
        'from_branch_id',
        'to_branch_id',
        'product_id',
        'quantity',
        'status',
        'approved_by'
    ];

    // Relationship: Branch sending the stock
    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    // Relationship: Branch receiving the stock
    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    // Relationship: Product being transferred
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Relationship: Admin who approved/rejected
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}