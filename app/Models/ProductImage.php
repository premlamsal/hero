<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'location',
        'type',
        'name',
        'product_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected $appends = ['full_location']; // Automatically add the computed attribute

    // Accessor for the full URL
    public function getFullLocationAttribute()
    {
        return url('storage/' . $this->location);
    }
}
