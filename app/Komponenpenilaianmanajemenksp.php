<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Komponenpenilaianmanajemenksp extends Model
{
    protected $table='komponenpenilaianmanajemenksp';
    protected $fillable = [
    	'koperasi_id', 'status', 'aktiva', 'likuiditas', 'kelembagaan', 'umum', 'permodalan'   
    ];
}
