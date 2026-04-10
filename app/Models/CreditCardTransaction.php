<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditCardTransaction extends Model
{
    protected $fillable = ['credit_card_invoice_id', 'category_id', 'description', 'amount', 'date', 'installments', 'current_installment'];

    protected $casts = [
        'date' => 'date'
    ];

    public function invoice()
    {
        return $this->belongsTo(CreditCardInvoice::class, 'credit_card_invoice_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
