<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_code',
        'paypal_order_id',
        'total_amount',
        'currency',
        'status',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];


    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
