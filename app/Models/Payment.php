<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'jenis_payment',
    'period_mode',
    'detail_fee_template',
    'default_amount',
    'is_active',
    'start_date',
    'end_date',
])]
class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';
    protected $primaryKey = 'id_payment';
    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'detail_fee_template' => 'array',
            'default_amount' => 'decimal:2',
            'is_active' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function studentPayments(): HasMany
    {
        return $this->hasMany(StudentPayment::class, 'id_payment', 'id_payment');
    }
}
