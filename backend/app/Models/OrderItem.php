<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    //

    protected $appends = ['subtotal'];

    protected $fillable = [
        'order_id',
        'menu_item_id',
        'quantity',
        'price'
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function getSubtotalAttribute(): string
    {
        return number_format((float) $this->price * (int) $this->quantity, 2, '.', '');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
}

