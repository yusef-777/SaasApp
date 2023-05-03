<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'account_id',
        'invoice_id',
        'description',
        'unity_price',
        'quantity',
        'quantity_unity'

    ];
}
