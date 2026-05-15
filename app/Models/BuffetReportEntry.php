<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'report_year',
    'period_label',
    'period_date',
    'metric',
    'amount',
    'sort_order',
])]
class BuffetReportEntry extends Model
{
    public const METRICS = [
        'expense' => 'расход',
        'income' => 'приход',
        'employee_debt_income' => 'приход (долг сотрудников)',
        'total' => 'итого',
        'representative_expenses' => 'представительские р-ды',
        'profit_loss' => 'доход/убыток',
        'Деньги в обороте' => 'Деньги в обороте',
        'в кассе на 29.04.2026' => 'в кассе на 29.04.2026',
        'Долг сотрудников' => 'Долг сотрудников',
        'Остаток товара' => 'Остаток товара',
        'Долг поставщикам' => 'Долг поставщикам',
    ];

    protected function casts(): array
    {
        return [
            'report_year' => 'integer',
            'period_date' => 'date',
            'amount' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }
}
