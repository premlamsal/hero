<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'price',
        'unit_id'
    ];

    // Relationship with the sale
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // Relationship with the product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relationship with the unit
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
