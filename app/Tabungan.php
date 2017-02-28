<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tabungan extends Model
{
    protected $table = 'tabungan';
	
	protected $fillable = [
		'koperasi_id','anggotakoperasi_id','setingtabungan_id','saldo'
	];
	
	public function anggotakoperasi(){
		return $this->belongsTo('App\Anggotakoperasi');
	}
}
