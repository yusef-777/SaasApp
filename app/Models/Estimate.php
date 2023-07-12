<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estimate extends Model
{
    use HasFactory;

    const PENDING_STATUS = 'pedning';
    const ACCEPTED_STATUS = 'accepted';
    const CANCELED_STATUS = 'canceled';

    protected $fillable = [
        "account_id",
        "created_by",
        "no",
        "client_id",
        "issued_at",
        "vat"
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(EstimateItem::class);
    }
}
