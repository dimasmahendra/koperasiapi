<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pengurus extends Model
{
    protected $table='pengurus';

    protected $fillable = [
        'koperasi_id','anggotakoperasi_id', 'tahunoperasi_id','jabatan','keterangan'
    ];

    public function anggotakoperasi()
    {
        return $this->belongsTo('App\Anggotakoperasi')->select('id','idunik','nama','telepon','jeniskelamin','alamat','pendidikan');
    }
     public function koperasi()
    {
        return $this->belongsTo('App\Koperasi');
    }
    public function tahunoperasi()
    {
        return $this->belongsTo('App\Tahunoperasi','koperasi_id','koperasi_id')->select('id','koperasi_id','tanggalmulai','tanggalselesai','status')->where('status','like','Aktif')->orderBy('id','desc')->limit(1);
    }
    /*---Note---*/
	/*
	*	jeniskelamin:
	*		Laki-laki
	*		Perempuan
	*	jabatan :
	*		ketua 1,ketua 2, ketua 3
	* 		sekretaris 1, sekretaris 2, sekretaris 3
	*		bendahara 1, bendahara 2 ,bendahara 3
	*   	anggota 1, anggota 2
	*		pengawas
	*		manajer 1, manajer 2, manajer 3
	*	pendidikan :
	*		Tidak Lulus SD
	*		SD
	*		SMP
	*		SMA/SMK
	*		D1-D4
	*		SI
	*		S2
	*		S3
	*	status :
	*		Aktif
	*		Tidak Aktif
	*/

	// public function simpananberjangka(){
	// 	return $this->hasOne('App\Simpananberjangka');
	// }
	// public function setingsimka(){
	// 	return $this->belongsTo('App\Setingsimka');
	// }
}
