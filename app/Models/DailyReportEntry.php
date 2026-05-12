<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'report_date',
    'report_company_id',
    'report_company_account_id',
    'daily_report_type_id',
    'amount',
    'counterparty',
    'comment',
])]
class DailyReportEntry extends Model
{
    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<ReportCompany, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(ReportCompany::class, 'report_company_id');
    }

    /**
     * @return BelongsTo<ReportCompanyAccount, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(ReportCompanyAccount::class, 'report_company_account_id');
    }

    /**
     * @return BelongsTo<DailyReportType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(DailyReportType::class, 'daily_report_type_id');
    }
}
