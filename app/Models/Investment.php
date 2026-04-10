<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    protected $fillable = ['name', 'type', 'invested_amount', 'current_balance', 'start_date', 'estimated_profitability'];

    protected $casts = [
        'start_date' => 'date'
    ];
}
