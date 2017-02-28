<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dewanpengawassyariah extends Model
{
	protected $connection = 'mysql2';
    protected $table='dewanpengawassyariah';

    protected $fillable = [
        'koperasi_id','anggotakoperasi_id','nama','telepon','pendidikan','jeniskelamin','sertifikatdsn','status','alamat','kelurahan_id','mulaijabatan','akhirjabatan','keterangan'

    ];

    // protected $hidden = [
    //     'password',
    // ];

    public function koperasi()
    {
        return $this->belongsTo('App\Koperasi');
    }
     //nullable: anggotakoperasi_id, telepon , pendidikan (dejarat), alamat, akhirjabatan, keterangan
     public function anggotakoperasi()
    {
        return $this->belongsTo('App\Anggotakoperasi');
    }
   	
}
