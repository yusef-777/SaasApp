<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    use HasFactory;
    protected $fillable = [
        'account_id',
        'invoice_id',
        'estimate_id',
        'no',
        'issued_at',
        'status'
    ];

    public function items()
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }
}
