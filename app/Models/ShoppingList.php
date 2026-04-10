<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ShoppingList extends Model
{
    use LogsActivity;
    protected $fillable = ['name', 'location', 'date', 'total_amount', 'status', 'closing_reason'];
    
    protected $casts = [
        'date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(ShoppingListItem::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
