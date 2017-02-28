<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';
	
	protected $fillable = [
		'koperasi_id','anggotakoperasi_id','pesankementeriankoperasi_id','bookingseminarkementerian_id','bookingseminarkoperasi_id','bookingtrainingkementerian_id','bookingtrainingkoperasi_id'
	];
}
