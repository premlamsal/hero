<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Customer extends BaseModel
{
    protected $fillable = ['name', 'address', 'phone', 'mobile', 'tax_number', 'opening_balance', 'description'];


}
