<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Komponenpenilaianksp extends Authenticatable
{
    protected $table='komponenpenilaianksp';
    protected $fillable = [
        'koperasi_id', 'status', 'atmr','bank','bebanoperasi' ,'bebanusaha', 'bebanperkoperasian', 'biayakaryawan', 'cadanganrisiko', 'danaditerima','kas', 'kewajibanlancar', 'modalsendiri', 'modaltertimbang', 'partisipasibruto', 'partisipasinetto', 'pendapatan', 'pea', 'pinjamanbermasalah', 'pinjamanberisiko', 'pinjamandiberikan', 'pinjamandiberikanberisiko', 'shubagiananggota', 'shukotor', 'shusebelumpajak', 'simpananpokok', 'simpananwajib', 'totalaset', 'totalmodalsendiri', 'volumepinjaman', 'volumepinjamananggota', 'kepatuhansyariah' 
    ];
    public function koperasi()
    {
        return $this->belongsTo('App\Koperasi');
    }
}
