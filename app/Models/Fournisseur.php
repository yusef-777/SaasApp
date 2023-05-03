<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fournisseur extends Model
{
    use HasFactory;
    protected $fillable = [
        'account_id',
        'name',
        'address',
        'phone_number',
        'email',
        'ice_no',
        'rc_no',
        'cnss_no'
    ];
}
