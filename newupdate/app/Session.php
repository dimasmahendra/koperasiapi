<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $table='session';

    protected $fillable=['id', 'anggotakoperasi_id', 'session_key','status','unik_id','expired_at'];


}
