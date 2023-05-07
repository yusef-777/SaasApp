<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstimateItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        "id",
        "account_id",
        "estimate_id",
        "description",
        "unit_price",
        "quantity",
        "quantity_unit"
    ];
}
