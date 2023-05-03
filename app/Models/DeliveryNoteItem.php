<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNoteItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'delivery_note_id',
        'description',
        'quantity',
        'quantity_unit'
    ];
}
