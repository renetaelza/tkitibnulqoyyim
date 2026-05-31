<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'id_fund_source',
    'direction',
    'amount',
    'transaction_date',
    'description',
    'attachment_path',
    'reference_type',
    'reference_id',
    'created_by',
])]
class FundTransaction extends Model
{
    use HasFactory;

    protected $table = 'fund_transactions';

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    public function fundSource(): BelongsTo
    {
        return $this->belongsTo(FundSource::class, 'id_fund_source');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeIn($query)
    {
        return $query->where('direction', 'in');
    }

    public function scopeOut($query)
    {
        return $query->where('direction', 'out');
    }
}
