<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $fillable = [
        'account_id',
        'name',
        'ice_no',
        'if_no',
        'rc_no',
        'csnss_no',
        'address',
        'phone_number',
        'email',
        'city'
    ];
}