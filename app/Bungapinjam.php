<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bungapinjam extends Model
{
    protected $table = 'bungapinjam';
	
	protected $fillable = [
		'koperasi_id','tipebunga_id','persenbunga','denda'
	];
}
