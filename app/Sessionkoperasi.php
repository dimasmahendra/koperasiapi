<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sessionkoperasi extends Model
{
    protected $table='sessionkoperasi';

    protected $fillable=['adminkoperasi_id','session_key','status','ip_address','expired_at'];


}
