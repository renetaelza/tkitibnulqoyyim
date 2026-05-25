<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $table = 'payment_settings';

    protected $fillable = [
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'qris_image_path',
    ];
}
