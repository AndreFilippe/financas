<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditCardInvoice extends Model
{
    protected $fillable = ['credit_card_id', 'reference_month', 'total_amount', 'status'];

    public function creditCard()
    {
        return $this->belongsTo(CreditCard::class);
    }

    public function transactions()
    {
        return $this->hasMany(CreditCardTransaction::class);
    }
}
