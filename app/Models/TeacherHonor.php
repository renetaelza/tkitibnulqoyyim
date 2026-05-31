<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'id_teacher',
    'month',
    'year',
    'period_start',
    'period_end',
    'attendance_count',
    'permission_count',
    'sickness_count',
    'absence_count',
    'workday_count',
    'holiday_credit_count',
    'effective_attendance_count',
    'late_count',
    'late_penalty',
    'permission_penalty',
    'rate_snapshot',
    'allowance_total',
    'manual_adjustment',
    'amount',
    'payment_date',
])]
class TeacherHonor extends Model
{
    use HasFactory;

    protected $table = 'teacher_honors';
    protected $primaryKey = 'id_honors';
    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'rate_snapshot' => 'decimal:2',
            'allowance_total' => 'decimal:2',
            'manual_adjustment' => 'decimal:2',
            'late_penalty' => 'decimal:2',
            'permission_penalty' => 'decimal:2',
            'workday_count' => 'integer',
            'holiday_credit_count' => 'integer',
            'effective_attendance_count' => 'integer',
            'late_count' => 'integer',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(TeacherDetail::class, 'id_teacher', 'id_teacher');
    }

    public function allowances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TeacherHonorAllowance::class, 'id_honors', 'id_honors');
    }
}
