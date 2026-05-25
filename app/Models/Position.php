<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'is_active'])]
class Position extends Model
{
    use HasFactory;

    protected $table = 'positions';
    protected $primaryKey = 'id_position';
    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function teacherPositions(): HasMany
    {
        return $this->hasMany(TeacherPosition::class, 'id_position', 'id_position');
    }

    public function allowances(): HasMany
    {
        return $this->hasMany(PositionAllowance::class, 'id_position', 'id_position');
    }
}
