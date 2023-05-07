<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'invoice_id',
        'created_by',
        'amount',
        'paid_at',
        'payment_method_id',
        'check_no',
        'bank_name'
    ];
}
