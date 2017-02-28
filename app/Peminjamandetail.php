<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Peminjamandetail extends Model
{
    protected $table = 'peminjamandetail';
	
	protected $fillable = [
	'idunik','peminjaman_id','metode_id','jatuhtempo','tanggalbayar','angsuranpokok','besarbunga','denda','status'
	];
	
	public function peminjaman(){
		return $this->belongsTo('App\Peminjaman');
	}
	public function metode(){
		return $this->belongsTo('App\Metode');
	}
}
