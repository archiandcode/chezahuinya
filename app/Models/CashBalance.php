<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'balance_date',
    'sort_order',
    'company',
    'balance_amount',
    'custody_assets_amount',
    'own_assets_amount',
])]
class CashBalance extends Model
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
            'balance_amount' => 'decimal:2',
            'custody_assets_amount' => 'decimal:2',
            'own_assets_amount' => 'decimal:2',
        ];
    }
}
