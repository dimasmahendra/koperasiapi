<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table='karyawan';

    protected $fillable = [
        'koperasi_id','anggotakoperasi_id','nama', 'telepon','pendidikan','jeniskelamin','jabatan','status',
        'alamat','kelurahan_id','mulaijabatan','akhirjabatan','keterangan'
    ];

    public function koperasi()
    {
        return $this->belongsTo('App\Koperasi','koperasi_id');
    }

    // public function pemberibadanhukum()
    // {
    //     return $this->belongsTo('App\Pemberibadanhukum');
    // }
}