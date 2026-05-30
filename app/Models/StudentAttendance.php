<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id_student', 'date', 'status', 'information'])]
class StudentAttendance extends Model
{
    use HasFactory;

    protected $table = 'student_attendance';
    protected $primaryKey = 'id_attendance';
    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'id_student', 'id_student');
    }
}
