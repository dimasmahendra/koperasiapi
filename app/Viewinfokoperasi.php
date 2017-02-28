<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Viewinfokoperasi extends Model
{
    protected $table = 'viewinfokoperasi';
	
	protected $fillable = [
	'infokoperasi_id', 'anggotakoperasi_id', 'status'
	];
}
