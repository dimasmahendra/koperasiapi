<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    protected $table = 'peminjaman';
	
	protected $fillabel = [
	'koperasi_id', 'anggotakoperasi_id', 'bungapinjam_id', 'tanggal', 'token','	jumlah','tenor','sisatenor','persenbunga','jatuhtempo','status','keperluan'
	];
	
	public function peminjamandetail(){
		return $this->hasMany('App\Peminjamandetail');
	}
	public function koperasi(){
		return $this->belongsTo('App\Koperasi');
	}
	public function anggotakoperasi(){
		return $this->belongsTo('App\Anggotakoperasi');
	}
}
