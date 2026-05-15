<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'balance_date',
    'sort_order',
    'product_group',
    'quantity',
    'cost_amount',
])]
class TradingStockBalance extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance_date' => 'date',
            'sort_order' => 'integer',
            'quantity' => 'decimal:2',
            'cost_amount' => 'decimal:2',
        ];
    }
}
