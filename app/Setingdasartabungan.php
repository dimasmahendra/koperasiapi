<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setingdasartabungan extends Model
{
    protected $table = 'setingdasartabungan';
	
	protected $fillable = [
	'id','koperasi_id','administrasi','periode'
	];
}
