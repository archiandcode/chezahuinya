<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'construction_section_id',
    'payment_date',
    'supplier',
    'amount',
    'contract',
    'purpose',
    'payment_source',
])]
class ConstructionPayment extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'construction_section_id' => 'integer',
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function constructionSection(): BelongsTo
    {
        return $this->belongsTo(ConstructionSection::class);
    }
}
