<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductMergeLog extends Model
{
    protected $table = 'product_merge_logs';

    protected $fillable = [
        'canonical_product_id',
        'canonical_serial_number',
        'merged_product_id',
        'merged_serial_number',
        'inventories_merged',
        'sale_items_repointed',
        'stock_transfers_repointed',
    ];
}