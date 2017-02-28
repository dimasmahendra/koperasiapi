<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pembeliantemp extends Model
{
    protected $table='pembeliantemp';

    protected $fillable = [
        'koperasi_id','tahunoperasi_id', 'tanggal','totalhargabeli','metode'
    ];
	
	public function pembeliandetailtemp(){
		return $this->hasMany('App\Pembeliandetailtemp','pembelian_id','id');
	}
}
