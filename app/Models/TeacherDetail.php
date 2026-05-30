<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'id_user',
    'name',
    'position',
    'nip',
    'nuptk',
    'birth_place',
    'birth_date',
    'start_work_date',
    'education',
    'phone_num',
    'email',
    'status',
])]
class TeacherDetail extends Model
{
    use HasFactory;

    protected $table = 'teacher_details';
    protected $primaryKey = 'id_teacher';
    public $timestamps = true;

    protected $appends = ['masa_kerja'];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'start_work_date' => 'date',
        ];
    }

    protected function masaKerja(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $start = $this->start_work_date;
                if (!$start) {
                    return '-';
                }

                $diff = $start->diff(now());
                $years = (int) $diff->y;
                $months = (int) $diff->m;

                $parts = [];
                if ($years > 0) $parts[] = $years . ' th';
                if ($months > 0) $parts[] = $months . ' bln';

                return $parts ? implode(' ', $parts) : '0 bln';
            },
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_teacher', 'id_teacher', 'id_class');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class, 'id_teacher', 'id_teacher');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(TeacherPosition::class, 'id_teacher', 'id_teacher');
    }

    public function attendanceRates(): HasMany
    {
        return $this->hasMany(TeacherAttendanceRate::class, 'id_teacher', 'id_teacher');
    }

    public function honors(): HasMany
    {
        return $this->hasMany(TeacherHonor::class, 'id_teacher', 'id_teacher');
    }
}
