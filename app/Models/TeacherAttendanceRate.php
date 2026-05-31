<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id_teacher', 'amount_per_attendance', 'effective_from', 'effective_to'])]
class TeacherAttendanceRate extends Model
{
    use HasFactory;

    protected $table = 'teacher_attendance_rates';
    protected $primaryKey = 'id_teacher_attendance_rate';
    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'amount_per_attendance' => 'decimal:2',
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(TeacherDetail::class, 'id_teacher', 'id_teacher');
    }
}
