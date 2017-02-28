<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pembeliandetailtemp extends Model
{
    protected $table='pembeliandetailtemp';

    protected $fillable = [
        'pembelian_id','tanggal', 'produk_id','hargabeli','kuantitas','subtotalhargabeli'
    ];
	
	public function produk(){
		return $this->belongsTo('App\Produk');
	}
}
