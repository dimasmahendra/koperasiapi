<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setingtabungan extends Model
{
    protected $table = 'setingtabungan';
	
	protected $fillable = [
	'id','koperasi_id','bunga','minsaldo','maxsaldo'
	];
}
