<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Metode extends Model
{
	protected $table='Metode';
	
	protected $fillable=['id','nama'];
}
