<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'id_student_payment',
    'installment_number',
    'due_date',
    'installment_amount',
    'status',
    'paid_at',
    'payment_method',
    'proof_file',
])]
class StudentPaymentInstallment extends Model
{
    use HasFactory;

    protected $table = 'student_payment_installments';
    protected $primaryKey = 'id_student_payment_installment';
    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'installment_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function studentPayment(): BelongsTo
    {
        return $this->belongsTo(StudentPayment::class, 'id_student_payment', 'id_student_payment');
    }

    public function proofs(): MorphMany
    {
        return $this->morphMany(PaymentProof::class, 'proofable');
    }
}
