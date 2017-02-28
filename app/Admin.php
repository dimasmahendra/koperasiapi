<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $table='admin';
    protected $fillable = [
        'fullname', 'email','username','password' ,'img'
    ];
    protected $hidden = [
        'password', 'remember_token',
    ];
}
