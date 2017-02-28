<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tipekoperasi extends Model
{
    protected $table='tipekoperasi';

    protected $fillable=[
		'kodetipekoperasi','tipekoperasi'
	];

    public function adminkementerian()
    {
        return $this->hasMany('App\Adminkementerian');
    }
	
	public function koperasi()
    {
        return $this->hasMany('App\Koperasi');
    }




}
