<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    //


    protected $fillable = [
        'order_id',
        'amount',
        'payment_method',
        'status',
        'mpesa_receipt',
        'merchant_request_id',
        'checkout_request_id',
        'phone_number',
        'result_code',
        'result_description',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

