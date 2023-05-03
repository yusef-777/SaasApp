<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    const ROLE_ADMIN = 'admin';
    const ROLE_COMMERCIAL = 'commercial';
    const ROLE_CLIENT = 'client';

    const ROLES = [self::ROLE_ADMIN, self::ROLE_COMMERCIAL, self::ROLE_CLIENT];


    protected $fillable = [
        'account_id',
        'email' ,
        'password' ,
        'role' ,
        'status' ,
        'email_verified' ,
        'first_name',
        'last_name'  ,
        'phone_number',
        'client_id'  
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}