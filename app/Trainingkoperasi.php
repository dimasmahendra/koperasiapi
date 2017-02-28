<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Trainingkoperasi extends  Model
{

    protected $table='trainingkoperasi';

    protected $fillable = [
        'koperasi_id','judul','isi','tempat','tanggal','durasi','kapasitas','foto'
    ];


    public function bookingtrainingkoperasi()
    {
        return $this->HasMany('App\Bookingtrainingkoperasi','trainingkoperasi_id');
    }

    public function budget_count () {
        return $this->hasOne('App\Bookingtrainingkoperasi')
            ->selectRaw('anggotakoperasi_id, count(*) as budget_count')
            ->groupBy('training_id');
    }


}
