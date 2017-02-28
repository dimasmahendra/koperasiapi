<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Jurnalkoperasi extends Model
{
    protected $table='jurnalkoperasi';

    protected $fillable=['koperasi_id','tahunoperasi_id','penjualan','hpp','labakotor','biayausaha','lababersih','totalsimpanan'];


    public function tahunoperasi()
    {
        return $this->belongsTo('App\Tahunoperasi');
    }



}
