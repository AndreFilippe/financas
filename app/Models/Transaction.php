<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Transaction extends Model
{
    use LogsActivity;
    protected $fillable = ['account_id', 'category_id', 'shopping_list_id', 'description', 'amount', 'type', 'date', 'payment_date', 'is_recurring', 'repeat_until', 'recurrence_type', 'associated_with', 'status'];

    public function shoppingList()
    {
        return $this->belongsTo(ShoppingList::class);
    }

    protected $casts = [
        'date' => 'date',
        'payment_date' => 'date',
        'repeat_until' => 'date',
        'is_recurring' => 'boolean'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
