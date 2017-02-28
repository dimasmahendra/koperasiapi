<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Simpananberjangka extends Model
{
    protected $table = 'simpananberjangka';
	
	protected $fillable = [
        'koperasi_id','anggotakoperasi_id','metode_id', 'setingsimka_id','simpanan',
        'bunga','status','jangkawaktu_id','sumberdana','refnumberssp','penyetor'
    ];
	
	public function koperasi()
    {
        return $this->belongsTo('App\Koperasi');
    }
	public function anggotakoperasi()
    {
        return $this->belongsTo('App\Anggotakoperasi');
    }
	public function metode()
    {
        return $this->belongsTo('App\Metode');
    }
	public function setingsimka()
    {
        return $this->belongsTo('App\Setingsimka');
    }
	public function pengambilansimka(){
		return $this->hasOne('App\Pengambilansimka');
	}
}
