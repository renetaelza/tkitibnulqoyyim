<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id_teacher', 'id_position', 'effective_from', 'effective_to'])]
class TeacherPosition extends Model
{
    use HasFactory;

    protected $table = 'teacher_positions';
    protected $primaryKey = 'id_teacher_position';
    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(TeacherDetail::class, 'id_teacher', 'id_teacher');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'id_position', 'id_position');
    }
}
