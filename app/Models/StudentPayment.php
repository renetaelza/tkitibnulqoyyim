<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'id_student',
    'id_payment',
    'payment_period',
    'detail_fee_snapshot',
    'total_amount',
    'discount_amount',
    'final_amount',
    'payment_method',
    'status',
    'proof_file',
    'paid_at',
    'installment_requested',
    'installment_count',
])]
class StudentPayment extends Model
{
    use HasFactory;

    protected $table = 'student_payments';
    protected $primaryKey = 'id_student_payment';
    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'detail_fee_snapshot' => 'array',
            'total_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'installment_requested' => 'boolean',
            'paid_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'id_student', 'id_student');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'id_payment', 'id_payment');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(StudentPaymentInstallment::class, 'id_student_payment', 'id_student_payment');
    }

    public function proofs(): MorphMany
    {
        return $this->morphMany(PaymentProof::class, 'proofable');
    }
}
