<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sekunderkoperasi extends Model
{
    protected $table='sekunderkoperasi';
    protected $fillable = [
    	'sekunder_id', 'koperasi_id', 'status'    
    ];

    public function koperasi()
    {
        return $this->belongsTo('App\Koperasi');
    }

    public function predikatkesehatanksp()
    {
        return $this->belongsTo('App\Predikatkesehatanksp', 'koperasi_id', 'koperasi_id');
    }
}
