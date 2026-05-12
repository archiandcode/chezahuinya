<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['report_company_id', 'account_number', 'bank'])]
class ReportCompanyAccount extends Model
{
    /**
     * @return BelongsTo<ReportCompany, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(ReportCompany::class, 'report_company_id');
    }
}
