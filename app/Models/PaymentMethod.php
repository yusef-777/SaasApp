<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    const CHEQUE_ID = 'cheque';

    protected $fillable = [
        'name',
        'named_id'
    ];
}
