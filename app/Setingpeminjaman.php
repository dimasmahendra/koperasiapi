<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setingpeminjaman extends Model
{
    protected $table = 'setingpeminjaman';
	
	protected $fillable = [
	'koperasi_id', 'minimalpinjam'
	];
}
