<?php

namespace App;

//use Illuminate\Foundation\Auth\Anggotakoperasi as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Produk extends  Model
{

    protected $table='produk';

    protected $fillable = [
        'suplier_id','nama', 'satuan','stok','hargajual','koperasi_id','kategori_id','foto'
    ];

    public function kategori()
    {
        return $this->belongsTo('App\Kategori');
    }


    public function transaksidetail()
    {
        return $this->hasMany('App\Transaksidetail','id');
    }


    public function transaksidetailtemp()
    {
        return $this->hasMany('App\Transaksidetail','id');
    }

    public function suplier()
    {
        return $this->belongsTo('App\Suplier');
    }

    public function pembeliandetail()
    {
        return $this->hasMany('App\Pembeliandetail','id');
    }



}
