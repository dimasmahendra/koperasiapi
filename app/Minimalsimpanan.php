<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Minimalsimpanan extends Model
{
    protected $table = 'minimalsimpanan';
	
	protected $fillable = [
        'id', 'koperasi_id', 'jumlah', 'administrasi'
    ];
	
	public function koperasi(){
		return $this->belongsTo('App\koperasi');
	}

}
