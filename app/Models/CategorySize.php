<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategorySize extends Model
{
    protected $fillable = ['category', 'size', 'sort_order'];
}
