<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'name',
        'ice',
        'if_no',
        'rc_no',
        'cnss_no',
        'address',
        'phone_number',
        'created_at',
    ];
}