<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id_honors', 'allowance_label', 'source_position', 'amount'])]
class TeacherHonorAllowance extends Model
{
    use HasFactory;

    protected $table = 'teacher_honor_allowances';
    protected $primaryKey = 'id_honor_allowance';
    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function honor(): BelongsTo
    {
        return $this->belongsTo(TeacherHonor::class, 'id_honors', 'id_honors');
    }
}
