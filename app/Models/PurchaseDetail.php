<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{

    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'price',
        'unit_id'
    ];

    // Relationship with the purchase
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
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
