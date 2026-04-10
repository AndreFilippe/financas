<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditCard extends Model
{
    protected $fillable = ['name', 'limit', 'closing_day', 'due_day'];

    public function invoices()
    {
        return $this->hasMany(CreditCardInvoice::class);
    }
}
