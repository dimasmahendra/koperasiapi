<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pengesahan extends Model
{
    protected $table='pengesahan';

    protected $fillable = [
        'koperasi_id','nomorbh','tanggalbh', 'nolembaran','tgllembaran','pemberibadanhukum_id','dokumen','status'
    ];

    public function koperasi()
    {
        return $this->hasMany('App\Koperasi');
    }

    public function pemberibadanhukum()
    {
        return $this->belongsTo('App\Pemberibadanhukum')->orderBy('id','desc')->limit(1);
    }
}
