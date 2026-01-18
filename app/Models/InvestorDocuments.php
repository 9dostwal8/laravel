<?php

namespace App\Models;

use App\Enums\InvestorDocumentTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorDocuments extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'type' => InvestorDocumentTypeEnum::class,
    ];

    public function investor(): BelongsTo
    {
        return $this->belongsTo(Investor::class);
    }
}
