<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table = 'payment_methods';

    protected $fillable = [
        'type',
        'label',
        'account_number',
        'account_name',
        'image_path',
        'description',
        'is_active',
        'sort_order',
    ];
}
