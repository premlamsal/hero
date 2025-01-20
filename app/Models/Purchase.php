<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Purchase extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'purchase_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'grand_total',
        'supplier_id'
    ];

    // Relationship with sales details
    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    // Relationship with the customer
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }


}
