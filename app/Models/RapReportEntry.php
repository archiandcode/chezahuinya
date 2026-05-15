<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'report_date',
    'section',
    'counterparty',
    'quantity',
    'unit_price',
    'sale_amount',
    'sale_month',
    'invoice_date',
    'actual_payment_date',
    'paid_amount',
    'planned_payment_date',
    'unpaid_amount',
    'is_paid',
    'comment',
])]
class RapReportEntry extends Model
{
    public const SECTION_UNINVOICED = 'uninvoiced';
    public const SECTION_DEBT = 'debt';
    public const SECTION_SALES = 'sales';

    public const SECTIONS = [
        self::SECTION_UNINVOICED => 'Не выписаны ЭСФ',
        self::SECTION_DEBT => 'Общая задолженность',
        self::SECTION_SALES => 'Реализация товаров',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'sale_amount' => 'decimal:2',
            'invoice_date' => 'date',
            'actual_payment_date' => 'date',
            'paid_amount' => 'decimal:2',
            'planned_payment_date' => 'date',
            'unpaid_amount' => 'decimal:2',
            'is_paid' => 'boolean',
        ];
    }
}
