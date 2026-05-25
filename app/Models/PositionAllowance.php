<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id_position', 'id_allowance_type', 'amount', 'effective_from', 'effective_to'])]
class PositionAllowance extends Model
{
    use HasFactory;

    protected $table = 'position_allowances';
    protected $primaryKey = 'id_position_allowance';
    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'id_position', 'id_position');
    }

    public function allowanceType(): BelongsTo
    {
        return $this->belongsTo(AllowanceType::class, 'id_allowance_type', 'id_allowance_type');
    }
}
