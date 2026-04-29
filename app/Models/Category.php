<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'type', 'color'];
    
    protected static function booted()
    {
        static::addGlobalScope('alphabetical', function ($builder) {
            $builder->orderBy('name', 'asc');
        });
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
