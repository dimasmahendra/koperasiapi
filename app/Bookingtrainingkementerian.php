<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Bookingtrainingkementerian extends  Model
{

    protected $table='bookingtrainingkementerian';

    protected $fillable = [
        'trainingkementerian_id','anggotakoperasi_id','koperasi_id','status'
    ];



   public function trainingkementerian()
    {
        return $this->BelongsTo('App\Trainingkementerian','id');
    }

   public function anggotakoperasi()
    {
        return $this->HasMany('App\Anggotakoperasi','id','anggotakoperasi_id')->select(['id','nama','email','telepon','jeniskelamin','alamat','foto']);
    }
	public function koperasi(){
		return $this->belongsTo('App\Koperasi');
	}
}
