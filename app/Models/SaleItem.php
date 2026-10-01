<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = ['sale_id', 'product_id', 'quantity', 'price'];

    /**
     * Line total before VAT. Derived, not stored: there is no subtotal
     * column on sale_items. Note this is pre-VAT, so it will not sum to
     * the parent Sale's total_amount, which includes 12% VAT.
     */
    public function getSubtotalAttribute(): float
    {
        return round((float) $this->quantity * (float) $this->price, 2);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
