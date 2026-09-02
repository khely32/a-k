<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = ['serial_number', 'name', 'brand', 'type', 'color', 'size', 'quantity', 'price', 'description', 'branch_id'];

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->serial_number)) {
                $branch = $product->branch_id ?: '00';
                $prefix = 'AK-BR' . str_pad($branch, 2, '0', STR_PAD_LEFT) . '-';
                do {
                    $serial = $prefix . strtoupper(Str::random(6));
                } while (static::where('serial_number', $serial)->exists());

                $product->serial_number = $serial;
            }
        });
    }

    /**
     * SAFETY APIS (Accessors)
     * If any legacy front-end JavaScript/Blade views try to call the old variable names 
     * on this model, these functions dynamically hand back the correct new columns.
     */
    public function getPartNameAttribute()
    {
        return $this->name ?? $this->attributes['part_name'] ?? null;
    }

    public function getItemCodeAttribute()
    {
        return $this->serial_number ?? $this->attributes['item_code'] ?? null;
    }

    public function getStockLevelAttribute()
    {
        return $this->quantity ?? $this->attributes['stock_level'] ?? null;
    }
}