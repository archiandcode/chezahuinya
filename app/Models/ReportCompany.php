<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'short_name', 'category'])]
class ReportCompany extends Model
{
    /**
     * @return HasMany<ReportCompanyAccount, $this>
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(ReportCompanyAccount::class);
    }
}
