<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Branch extends Model
{
    protected $fillable = [
        'branch_name',
        'location',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getNameAttribute()
    {
        return $this->branch_name;
    }

    public function isMainBranch(): bool
    {
        return str_contains(strtolower($this->branch_name ?? ''), 'moroboro')
            || str_contains(strtolower($this->branch_name ?? ''), 'branch 1');
    }

    public function getAddressAttribute()
    {
        return $this->location;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function sales(): HasManyThrough
    {
        return $this->hasManyThrough(
            Sale::class,
            User::class,
            'branch_id',
            'user_id',
            'id',
            'id'
        );
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function stockTransfersFrom(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'from_branch_id');
    }

    public function stockTransfersTo(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'to_branch_id');
    }
}
