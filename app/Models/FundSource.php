<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'description', 'is_active', 'is_auto_credit', 'display_order'])]
class FundSource extends Model
{
    use HasFactory;

    protected $table = 'fund_sources';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_auto_credit' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FundTransaction::class, 'id_fund_source');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
