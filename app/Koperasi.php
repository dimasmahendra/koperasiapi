<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class Koperasi extends  Model
{
    protected $table='koperasi';
    protected $fillable = [
    'nama', 'tipekoperasi_id', 'skalakoperasi_id', 'telepon', 'email', 'alamat', 'kodepos', 'fax', 'website', 'kelurahan_id', 'camat', 'notaris', 'jangkawaktu', 'nomorinduk', 'binaan', 'npwp', 'bentukkoperasi_id', 'kelompokkoperasi_id', 'sektorusaha_id', 'simpananpokok', 'simpananwajib', 'sukubunga', 'tanggaldaring', 'foto', 'latitude', 'longitude', 'usernamessp', 'created_at', 'updated_at'     
    ];
    protected $hidden = [
        'password',
    ];

    public function kelurahan()
    {
        return $this->belongsTo('App\Kelurahan','id');
    }

    public function anggotakoperasi()
    {
        return $this->hasMany('App\Anggotakoperasi');
    }
	
	public function tipekoperasi()
    {
        return $this->belongsTo('App\Tipekoperasi');
    }

    public function adminkoperasi()
    {
        return $this->hasMany('App\Adminkoperasi');
    }
	
	public function simpananberjangka()
    {
        return $this->hasMany('App\Simpananberjangka');
    }

    public function anggarandasar()
    {
        return $this->hasMany('App\Anggarandasar')->orderBy('id','desc')->limit(1);
    }

    public function pengesahan()
    {
        return $this->hasOne('App\Pengesahan','koperasi_id')->orderBy('id','desc')->limit(1);
    }

    public function kelompokkoperasi()
    {
        return $this->belongsTo('App\Kelompokkoperasi','kelompokkoperasi_id','id');
    }

    public function sektorusaha()
    {
        return $this->belongsTo('App\Sektorusaha','sektorusaha_id');
    }

    public function bentukkoperasi()
    {
        return $this->belongsTo('App\Bentukkoperasi','bentukkoperasi_id');
    }

    public function sekunderkoperasi()
    {
        return $this->hasMany('App\Sekunderkoperasi');
    }
}
