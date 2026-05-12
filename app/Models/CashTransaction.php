<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'transaction_date',
    'income_amount',
    'expense_amount',
    'company',
    'cash_flow',
    'has_supporting_document',
])]
class CashTransaction extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'income_amount' => 'decimal:2',
            'expense_amount' => 'decimal:2',
            'has_supporting_document' => 'boolean',
        ];
    }
}
