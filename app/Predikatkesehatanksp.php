<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Predikatkesehatanksp extends Model
{
    protected $table='predikatkesehatanksp';
    protected $fillable = [
    	'koperasi_id', 'skor', 'predikat', 'tanggal', 'status'   
    ];
        
    public function ScoretoPredikat($totalscores)
    {
       	if($totalscores < 51.00){
        	$data = 'Dalam Pengawasan Khusus';
        }
        elseif(($totalscores >= 51.00) && ($totalscores < 66.00)){
        	$data = 'Dalam Pengawasan';
        }
        elseif(($totalscores >= 66.00) && ($totalscores < 80.00)){
        	$data = 'Cukup Sehat';
        }
        elseif(($totalscores >= 80.00) && ($totalscores <= 100.00)){
        	$data = 'Sehat';
        }
        elseif($totalscores > 100.00){
        	$data = 'Sehat';
        }
        return $data;
    }
}
