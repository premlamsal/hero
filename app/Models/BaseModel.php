<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class BaseModel extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('user', function (Builder $builder) {
            $builder->where('user_id', auth()->id());
        });


        static::creating(function ($model) {
            if (auth()->check() && !isset($model->user_id)) {
                $model->user_id = auth()->id(); // Automatically set user_id
            }
        });
        // Ensure user_id is validated before updating or deleting
        static::updating(function ($model) {
            if ($model->user_id !== auth()->id()) {
                abort(403, 'Unauthorized to update this resource.');
            }
        });

        static::deleting(function ($model) {
            if ($model->user_id !== auth()->id()) {
                abort(403, 'Unauthorized to delete this resource.');
            }
        });
    }

    // Override delete method to enforce user-specific deletion
    public function delete()
    {
        if ($this->user_id !== auth()->id()) {
            abort(403, 'Unauthorized to delete this resource.');
        }
        return parent::delete();
    }

    // Optional: If you want to restrict mass deletes via query
    public static function bootSoftDeletes()
    {
        static::addGlobalScope('user', function (Builder $builder) {
            $builder->where('user_id', auth()->id());
        });
    }
}
