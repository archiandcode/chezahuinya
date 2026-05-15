<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'report_date',
    'section',
    'group_name',
    'counterparty',
    'amount',
    'company',
    'note',
    'sort_order',
])]
class DebtCreditEntry extends Model
{
    public const SECTION_CREDITOR = 'creditor';
    public const SECTION_DEBTOR = 'debtor';

    public const SECTIONS = [
        self::SECTION_CREDITOR => 'Кредиторская задолженность',
        self::SECTION_DEBTOR => 'Дебиторская задолженность',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'amount' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }
}
