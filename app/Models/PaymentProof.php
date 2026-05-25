<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'proofable_type',
    'proofable_id',
    'uploaded_by_user_id',
    'payment_method',
    'payment_method_label',
    'payment_method_account_number',
    'payment_method_account_name',
    'file_path',
    'status',
    'admin_note',
])]
class PaymentProof extends Model
{
    use HasFactory;

    protected $table = 'payment_proofs';
    protected $primaryKey = 'id_payment_proof';
    public $timestamps = true;

    public function proofable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id', 'id');
    }
}
