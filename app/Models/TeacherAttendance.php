<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'id_teacher',
    'date',
    'check_in_time',
    'is_late',
    'late_minutes',
    'status',
    'information',
    'attachment_path',
    'source',
])]
class TeacherAttendance extends Model
{
    use HasFactory;

    protected $table = 'teacher_attendance';
    protected $primaryKey = 'id_attendance';
    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in_time' => 'datetime',
            'is_late' => 'boolean',
            'late_minutes' => 'integer',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(TeacherDetail::class, 'id_teacher', 'id_teacher');
    }
}
