<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'is_active'])]
class AllowanceType extends Model
{
    use HasFactory;

    protected $table = 'allowance_types';
    protected $primaryKey = 'id_allowance_type';
    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function positionAllowances(): HasMany
    {
        return $this->hasMany(PositionAllowance::class, 'id_allowance_type', 'id_allowance_type');
    }
}
