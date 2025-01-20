<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Sale extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'grand_total',
        'customer_id'
    ];

    // Relationship with sales details
    public function details()
    {
        return $this->hasMany(SaleDetail::class);
    }

    // Relationship with the customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    protected static function booted()
    {
        static::addGlobalScope('user', function (Builder $builder) {
            $builder->where('user_id', auth()->id());
        });
    }
}
