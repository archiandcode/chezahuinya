<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'cash_register_id',
    'transaction_date',
    'income_amount',
    'expense_amount',
    'company',
    'cash_company_id',
    'cash_flow',
    'cash_flow_category_id',
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
            'cash_register_id' => 'integer',
            'income_amount' => 'decimal:2',
            'expense_amount' => 'decimal:2',
            'cash_company_id' => 'integer',
            'cash_flow_category_id' => 'integer',
            'has_supporting_document' => 'boolean',
        ];
    }

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function cashCompany(): BelongsTo
    {
        return $this->belongsTo(CashCompany::class);
    }

    public function cashFlowCategory(): BelongsTo
    {
        return $this->belongsTo(CashFlowCategory::class);
    }
}
