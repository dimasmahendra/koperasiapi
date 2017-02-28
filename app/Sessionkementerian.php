<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sessionkementerian extends Model
{
    protected $table='sessionkementerian';

    protected $fillable=['adminkementerian_id','session_key','status','ip_address','expired_at'];


}
