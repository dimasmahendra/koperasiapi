<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Biayausaha extends Model
{
    protected $table='biayausaha';

    protected $fillable=['koperasi_id','tahunoperasi_id','tanggal','jumlah','keterangan'];


    public function tahunoperasi()
    {
        return $this->belongsTo('App\Tahunoperasi');
    }



}
