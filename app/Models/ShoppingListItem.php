<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingListItem extends Model
{
    protected $fillable = [
        'shopping_list_id', 
        'category_id',
        'name', 
        'quantity', 
        'estimated_unit_price', 
        'actual_unit_price', 
        'is_checked'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    protected $casts = [
        'is_checked' => 'boolean',
    ];

    public function shoppingList()
    {
        return $this->belongsTo(ShoppingList::class);
    }
}
