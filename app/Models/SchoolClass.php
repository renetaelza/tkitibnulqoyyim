<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['class_name', 'school_year', 'max_students'])]
class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';
    protected $primaryKey = 'id_class';
    public $timestamps = true;

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'class_student', 'id_class', 'id_student');
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(TeacherDetail::class, 'class_teacher', 'id_class', 'id_teacher');
    }
}
