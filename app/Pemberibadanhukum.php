<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pemberibadanhukum extends Model
{
    protected $table='pemberibadanhukum';

    protected $fillable = [
        'keterangan'
    ];

    public function pengesahan()
    {
        return $this->hasMany('App\Pengesahan');
    }
}
