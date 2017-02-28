<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\Request;
use App\Http\Requests;

use Carbon\Carbon;
use Response;
use DB;
use Hash;
use Auth;
use Input;
use Image;
use File;
use Storage;
use UrlGenerator;
use Mail;
use Attendance;

use App\Sessionkoperasi;
use App\Metode;
use App\session;
use App\Anggotakoperasi;
use App\Pengurus;
use App\Adminkoperasi;
use App\Pembiayaan;
use App\Koperasi;
use App\Dewanpengawassyariah;
use App\Pembiayaansyariah;
use App\Pembiayaansyariahdetail;
use App\Tabunganproduk;
use App\Tabungansyariah;

class Apiv7Controller extends Controller
{
	private function createOrUpdateSession($adminkoperasi_id = null)
    {
        $session_key = null;
        if (!is_null($adminkoperasi_id)) {
            $expired = Carbon::now()->addDays(7);
            $session = Session::where(['adminkoperasi_id' => $adminkoperasi_id])->first();
            if (is_null($session)) {
                Session::create(['adminkoperasi_id' => $adminkoperasi_id, 'status' => 1, 'session_key' => str_random(16)]);
                $session = Session::where(['adminkoperasi_id' => $adminkoperasi_id])->first();
            }
            $session->update(['status' => 1, 'expired_at' => $expired]);
            if (!is_null($session)) {
                $session_key = $session->session_key;
            }
        }
        return $session_key;
    }


    private function firstLogin($anggotakoperasi_id = null)
    {
        $session_key = null;
        if (!is_null($anggotakoperasi_id)) {
            $expired = Carbon::now()->addDays(7);
            $session = Session::where(['anggotakoperasi_id' => $anggotakoperasi_id])->first();
            if (is_null($session)) {
                Session::create(['anggotakoperasi_id' => $anggotakoperasi_id, 'status' => 0, 'session_key' => str_random(16)]);
                $session = Session::where(['anggotakoperasi_id' => $anggotakoperasi_id])->first();
            }
            $session->update(['status' => 0, 'expired_at' => $expired]);
            if (!is_null($session)) {
                $session_key = $session->session_key;
            }
        }
        return $session_key;
    }


    private function getAngggotakoperasiId($session_key = null)
    {
        $anggotakoperasi_id = null;
        if (!is_null($session_key)) {
            $session = Session::where(['session_key' => $session_key])->first();
            if (!is_null($session)) {
                $anggotakoperasi_id = Anggotakoperasi::find($session->anggotakoperasi_id);
                if (!is_null($anggotakoperasi_id)) {
                    $anggotakoperasi_id = $anggotakoperasi_id->id;
                    $this->createOrUpdateSession($session->anggotakoperasi_id);
                }
            }
        }
        return $anggotakoperasi_id;
    }


    private function checkIfSessionExpired($session_key = null)
    {
        $boolean = false;
        if (!is_null($session_key)) {
            $session = Sessionkoperasi::where(['session_key' => $session_key])->where('expired_at', '>=', Carbon::now())->first();
           /* print_r($session);die();*/

            if (!is_null($session)) {
                $boolean = true;
                $this->createOrUpdateSession($session->anggotakoperasi_id);
            }
        }
        return $boolean;
    }


    private function getKoperasiId($session_key = null)
    {
        $now = Carbon::now();
        $koperasi_id = null;
        if (!is_null($session_key)) {
            $session = Sessionkoperasi::where(['session_key' => $session_key])->where('expired_at', '>=', $now)->first();
            if (!is_null($session)) {
                $adminkoperasi_id = Adminkoperasi::find($session->adminkoperasi_id);
                if (!is_null($adminkoperasi_id)) {
                    $koperasi_id = $adminkoperasi_id->koperasi_id;
                }
            }
        }
        return $koperasi_id;
    }
    /*=====================================================================================================================*/
    public function insertdewanpengawas(Request $request){
    	$session_key = $request->input('session_key');
    	if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }


    	$input = $request->all();
		// $input['koperasi_id'] = $koperasi_id;
		// $input['anggotakoperasi_id'] = $anggotakoperasi_id;
  //       $input['kehadiran'] = $request->input('kehadiran');
        
        try {
            $insert = Dewanpengawassyariah::firstOrCreate($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'duplicate entry']);
        }
        if (!($insert)) {
            return Response::json(['status' => 0, 'message' => 'insert gagal']);
        }
    	return Response::json(['status' => 1, 'message' => 'Input Berhasil' , 'data' => $insert]);
    }
/*================================================================================================================================*/
    public function getdewanpengawas(Request $request)
    {
         $session_key = $request->input('session_key');
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $data=array();
        try{
            $koperasi_id=$this->getKoperasiId($session_key);

            $data = Dewanpengawassyariah::where(['koperasi_id'=>$koperasi_id])->with('koperasi','anggotakoperasi')->get();
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (count($data)==0) {
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan/kosong']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Data Ditemukan','data' => $data]);
    }

    public function insertpengawas(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $koperasi_id=$this->getKoperasiId($session_key);
            $input['koperasi_id']=$koperasi_id;
            unset($input['session_key']);
            $data=Dewanpengawassyariah::Create($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Insert Gagal']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Input Berhasil']);
    }

    public function deletepengawas(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$input['id'];
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Dewanpengawassyariah::find($id);
            if(!empty($data))
            $data->Delete();
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Hapus Gagal']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Hapus Berhasil']);
    }

    public function updatepengawas(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$input['id'];
            $koperasi_id=$this->getKoperasiId($session_key);
            $input['koperasi_id']=$koperasi_id;
            unset($input['session_key']);
            $data=Dewanpengawassyariah::find($id);
            if(!empty($data))
            $data->Update($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Ubah Gagal']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Ubah Berhasil']);
    }

    public function getpengurus(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
         $pengurus=array();
        try{
            $koperasi_id=$this->getKoperasiId($session_key);
             $pengurus = Pengurus::where(['koperasi_id'=>$koperasi_id])->with('koperasi','anggotakoperasi')->get();
             }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
         if (is_null($pengurus)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $pengurus]);
    }

    public function insertpengurus(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $koperasi_id=$this->getKoperasiId($session_key);
            $input['koperasi_id']=$koperasi_id;
            unset($input['session_key']);
            $data=Pengurus::Create($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Insert Gagal']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Input Berhasil']);
    }

    public function deletepengurus(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$input['id'];
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Pengurus::find($id);
            if(!empty($data))
            $data->Delete();
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Hapus Gagal']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Hapus Berhasil']);
    }

    public function updatepengurus(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$input['id'];
            $koperasi_id=$this->getKoperasiId($session_key);
            $input['koperasi_id']=$koperasi_id;
            unset($input['session_key']);
            $data=Pengurus::find($id);
            if(!empty($data))
            $data->Update($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Ubah Gagal']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Ubah Berhasil']);
    }

   public function getpembiayaan(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
         $pembiayaan=array();
        try{
            $koperasi_id=$this->getKoperasiId($session_key);
             $pembiayaan = Pembiayaan::where(['koperasi_id'=>$koperasi_id])->get();
             }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
         if (is_null($pembiayaan)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $pembiayaan]);
    }

    public function getpembiayaanby(Request $request){
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$request->input('id');
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Pembiayaan::where('id',$id)->where('koperasi_id',$koperasi_id)->get();
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (count($data)==0) {
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan/kosong']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Data Ditemukan','data' => $data]);
    }

    public function insertpembiayaan(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $koperasi_id=$this->getKoperasiId($session_key);
            $input['koperasi_id']=$koperasi_id;
            unset($input['session_key']);
            $data=Pembiayaan::Create($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Insert Gagal']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Input Berhasil']);
    }

     public function updatepembiayaan(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$input['id'];
            $koperasi_id=$this->getKoperasiId($session_key);
            $input['koperasi_id']=$koperasi_id;
            unset($input['session_key']);
            $data=Pembiayaan::find($id);
            if(!empty($data))
            $data->Update($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Ubah Gagal']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Ubah Berhasil']);
    }

    public function deletepembiayaan(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$input['id'];
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Pembiayaan::find($id);
            if(!empty($data))
            $data->Delete();
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Hapus Gagal']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Hapus Berhasil']);
    }

    public function getpembiayaansyariah(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
         $Pembiayaansyariah=array();
        try{
            $koperasi_id=$this->getKoperasiId($session_key);
             $Pembiayaansyariah = Pembiayaansyariah::where(['koperasi_id'=>$koperasi_id])->where('status','aktif')->with('anggotakoperasi')->get();
             }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
         if (is_null($Pembiayaansyariah)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $Pembiayaansyariah]);
    }

    public function getpembiayaansyariahby(Request $request){
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$request->input('id');
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Pembiayaansyariah::where('id',$id)->where('koperasi_id',$koperasi_id)->with('anggotakoperasi','pembiayaan')->first();
            $data1=Pembiayaansyariah::with(['anggotakoperasi','pembiayaan'])
            ->where('koperasi_id',$koperasi_id)->where('id',$id)->get();
           //print_r($data['identitas']);die();
            $sisa=$data->sisa;
            $angsuran=$data->angsuran;
            $sisatenor=$data->sisatenor;
            $sisaharus=$angsuran*$sisatenor;
            $kekurangan=$sisa-$sisaharus;

            if($sisatenor == 0)
            {
                $bulandepan = 0;
            }
            else
            {
                $bulandepan = $kekurangan + $angsuran;
            }            
            //print_r($data['identitas']);die();
            $data1[0]['kekurangan']=$kekurangan;
            $data1[0]['bulandepan']=$bulandepan;

        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (count($data1)==0) {
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan/kosong']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Data Ditemukan','data' => $data1]);
    }

    public function insertpembiayaansyariah(Request $request){
       
        $session_key = $request->input('session_key');
        
            if ($this->checkIfSessionExpired($session_key) == false) {
                return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
            }

            $koperasi_id = $this->getKoperasiId($session_key);
            
            if (is_null($koperasi_id)) {
                return Response::json(['status' => 0, 'message' => 'session key not found']);
            }
        
        $input = $request->all();

        $input['koperasi_id']=$koperasi_id;

        $key = $request->input('key');
        if ($key == "ya") 
        {
         $bonus = $request->input('bonus');
         $input['bonuspersen']=$bonus;
        $jumlahpinjam = $request->input('jumlahpinjam');
        $tenor = $request->input('tenor');
        $bonusfix=0;
        $input['bonusfix']=$bonusfix;
        $angsuran = ($jumlahpinjam + ($bonus/100*$jumlahpinjam))/$tenor;
        $input['angsuran'] = $angsuran;
        $sisa = $jumlahpinjam + ($bonus/100*$jumlahpinjam);
        $input['sisa']=$sisa;
        }       

        else if ($key == "tidak")
        {
        $bonus = $request->input('bonus');
        $input['bonusfix']=$bonus;
        $jumlahpinjam = $request->input('jumlahpinjam');
        $tenor = $request->input('tenor');
        $bonuspersen=0;
        $input['bonuspersen']=$bonuspersen;
        $angsuran = ($jumlahpinjam + $bonus)/$tenor;
        $input['angsuran'] = $angsuran;
        $sisa = $jumlahpinjam + $bonus;
        $input['sisa']=$sisa;
        }
        $rekening=$request->input('rekening');
        $input['rekening']=$rekening;
        
        $ps = Pembiayaansyariah::where(['rekening'=>$rekening])->get();
            foreach ($ps as $key => $value) {
                //echo $value['rekening'];
            }
           
           if (!empty($value['rekening'])) {
                return Response::json(['status' => 0, 'message' => 'Rekening Sama !']);
            }
            if (empty($value['rekening'])){
            
            unset($input['session_key']);
            unset($input['key']);

            $insert = DB::connection('mysql2')->select('call sp_insertPembiayaanSyariah(?,?,?,?,?,?,?,?,?,?,?,?)',[$input['koperasi_id'], $input['pembiayaan_id'], $input['anggotakoperasi_id'], $input['rekening'], $input['jumlahpinjam'], $input['nobukti'], $input['bonuspersen'], $input['bonusfix'], $input['tenor'], $input['angsuran'], $input['sisa'], $input['keteranganlain'] ]);

            if ($insert[0]->status == 0) {
                return Response::json(['status' => 0, 'message' => $insert[0]->message]);
            }

             if (!empty($value['rekening'])) {
                return Response::json(['status' => 0, 'message' => 'Rekening Sama !']);
            }
            
            return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
                'session_key' => $session_key, 'data' => $input]);
        }
    }       


    public function updatepembiayaansyariah(Request $request){
       $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$input['id'];
            $koperasi_id=$this->getKoperasiId($session_key);
            $input['koperasi_id']=$koperasi_id;
            $key = $request->input('key');
            if ($key == "ya") 
            {
            $bonus = $request->input('bonus');
            $input['bonuspersen']=$bonus;
            $jumlahpinjam = $request->input('jumlahpinjam');
            $tenor = $request->input('tenor');
            $bonusfix=0;
            $input['bonusfix']=$bonusfix;
            $angsuran = ($jumlahpinjam + ($bonus/100*$jumlahpinjam))/$tenor;
            $input['angsuran'] = $angsuran;
            }       

            else if ($key == "tidak")
            {
            $bonus = $request->input('bonus');
            $input['bonusfix']=$bonus;
            $jumlahpinjam = $request->input('jumlahpinjam');
            $tenor = $request->input('tenor');
            $bonuspersen=0;
            $input['bonuspersen']=$bonuspersen;
            $angsuran = ($jumlahpinjam + $bonus)/$tenor;
            $input['angsuran'] = $angsuran;
            }
            unset($input['session_key']);
            unset($input['key']);
            $data=Pembiayaansyariah::find($id);
            //print_r($data);die();
            //$pembiayaansyariahdetail = Pembiayaansyariahdetail::find(1)->id;
            //$pembiayaansyariahdetail=Pembiayaansyariahdetail::find($data->id);
            $pembiayaansyariahdetail=Pembiayaansyariahdetail::with('pembiayaansyariah')
            ->where('pembiayaansyariah_id',$id)->first();
            //print_r($pembiayaansyariahdetail);die();            
            if(empty($pembiayaansyariahdetail)){
                $data->Update($input);
            }   
            
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if(!is_null($pembiayaansyariahdetail)){
               return Response::json(['status' => 0, 'message' => 'Ubah Gagal']);
            }  
       
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Ubah Berhasil','data' => $input]);
    }


     public function deletepembiayaansyariah(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$input['id'];
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Pembiayaansyariah::where('id',$id)->first();
            $sisatenor=$data->sisatenor;
            $status=$data->status;
            $status="Tidak Aktif";
            $input['status']=$status;
            unset($input['session_key']);
            if($sisatenor==0)
                $data->Update($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if ($sisatenor!=0) {
            return Response::json(['status' => 0, 'message' => 'Hapus Gagal']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Hapus Berhasil']);
    }

    public function gettabunganproduk(Request $request)
    {
         $session_key = $request->input('session_key');
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $data=array();
        try{
            $koperasi_id=$this->getKoperasiId($session_key);

            $data = Tabunganproduk::where(['koperasi_id'=>$koperasi_id])->get();
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (count($data)==0) {
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan/kosong']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Data Ditemukan','data' => $data]);
    }

    public function inserttabunganproduk(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $koperasi_id=$this->getKoperasiId($session_key);
            $input['koperasi_id']=$koperasi_id;
            unset($input['session_key']);
            $data=Tabunganproduk::Create($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Insert Gagal']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Input Berhasil']);
    }

    public function updatetabunganproduk(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$input['id'];
            $koperasi_id=$this->getKoperasiId($session_key);
            $input['koperasi_id']=$koperasi_id;
            unset($input['session_key']);
            $data=Tabunganproduk::find($id);
            if(!empty($data))
            $data->Update($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Ubah Gagal']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Ubah Berhasil']);
    }

    public function deletetabunganproduk(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$input['id'];
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Tabunganproduk::find($id);
            if(!empty($data))
            $data->Delete();
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Hapus Gagal']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Hapus Berhasil']);
    }

    public function gettabungansyariah(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
         $ts=array();
        try{
            $koperasi_id=$this->getKoperasiId($session_key);
             $ts = Tabungansyariah::where(['koperasi_id'=>$koperasi_id])->where('status','Aktif')->with('anggotakoperasi','tabunganproduk')->get();
             }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
         if (is_null($ts)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $ts]);
    }

    public function inserttabungansyariah(Request $request){
       
        $session_key = $request->input('session_key');
        
            if ($this->checkIfSessionExpired($session_key) == false) {
                return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
            }

            $koperasi_id = $this->getKoperasiId($session_key);
            
            if (is_null($koperasi_id)) {
                return Response::json(['status' => 0, 'message' => 'session key not found']);
            }

            $input = $request->all();
            /*if(!(array_key_exists('refnumberssp', $input))){
            $input['refnumberssp'] = null;}*/
            $input['koperasi_id']=$koperasi_id;
            $key = $request->input('key');

            if ($key == "ya") 
            {
            $bonus = $request->input('bonus');
            $input['bonuspersen']=$bonus;
            $input['bonuscurrency']=0;
            }       

            else if ($key == "tidak")
            {
            $bonus = $request->input('bonus');
            $input['bonuscurrency']=$bonus;
            $input['bonuspersen']=0;
            }

            $rekening=$request->input('rekening');
            $input['rekening']=$rekening;
        
            $ts = Tabungansyariah::where(['rekening'=>$rekening])->get();            
           
           if (!empty($value['rekening'])) {
                return Response::json(['status' => 0, 'message' => 'Rekening Sama !']);
            }
            if (empty($value['rekening'])){
            
            unset($input['session_key']);
            unset($input['key']);

             $insert = DB::connection('mysql2')->select('call sp_insertTabunganSyariah(?,?,?,?,?,?,?,?,?,?,?,?,?)',[$input['koperasi_id'], $input['tabunganproduk_id'], $input['anggotakoperasi_id'], $input['rekening'], $input['setoran'], $input['bonuscurrency'], $input['bonuspersen'], $input['diambilpada'], $input['metode'], $input['nobukti'], $input['sumberdana'], $input['refnumberssp'], $input['penyetor']]);

            if ($insert[0]->status == 0) {
                return Response::json(['status' => 0, 'message' => $insert[0]->message]);
            }

             if (!empty($value['rekening'])) {
                return Response::json(['status' => 0, 'message' => 'Rekening Sama !']);
            }
            
            return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
                'session_key' => $session_key, 'data' => $input]);
        }
    }

    public function updatetabungansyariah(Request $request){
       $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$input['id'];
            $koperasi_id=$this->getKoperasiId($session_key);
            $input['koperasi_id']=$koperasi_id;
            $key = $request->input('key');
            if ($key == "ya") 
            {
            $bonus = $request->input('bonus');
            $input['bonuspersen']=$bonus;
            $input['bonuscurrency']=0;
            }       

            else if ($key == "tidak")
            {
            $bonus = $request->input('bonus');
            $input['bonuscurrency']=$bonus;
            $input['bonuspersen']=0;
            }

            unset($input['session_key']);
            unset($input['key']);
            $data=Tabungansyariah::find($id);
            if(!empty($data)){
                $data->Update($input);   
        }
            
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if(is_null($data)){
               return Response::json(['status' => 0, 'message' => 'Ubah Gagal']);
            }  
       
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Ubah Berhasil','data' => $input]);
    }    

 
    public function deletetabungansyariah(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$input['id'];
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Tabungansyariah::where('id',$id)->first();
            $status=$data->status;
            $status="Tidak Aktif";
            $input['status']=$status;
            unset($input['session_key']);
            if(!empty($data))
             $data->Update($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Hapus Gagal']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Hapus Berhasil']);
    }

    public function ambiltabungansyariah(Request $request){
        
        $session_key = $request->input('session_key');
        /*---------- checking authority ----------*/
            if ($this->checkIfSessionExpired($session_key) == false) {
                return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
            }

            $koperasi_id = $this->getKoperasiId($session_key);
            
            if (is_null($koperasi_id)) {
                return Response::json(['status' => 0, 'message' => 'session key not found']);
            }
        /*---------- checking authority ----------*/
        
        $input = $request->all();
        if(!(array_key_exists('refnumberssp', $input))){
            $input['refnumberssp'] = null;}
        //print_r($input); die();
        $insert = DB::connection('mysql2')->select('call sp_insertTabunganSyariahAmbil(?,?,?,?,?,?,?)',[$input['tabungan_id'],$input['jumlah'],$input['metode'],$input['token'],$input['refnumberssp'],$input['penerima'],$input['nobukti']]);
        
        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $input]);
    }

    public function insertpembiayaansyariahdetail(Request $request){
       
        $session_key = $request->input('session_key');
        
            if ($this->checkIfSessionExpired($session_key) == false) {
                return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
            }

            $koperasi_id = $this->getKoperasiId($session_key);
            
            if (is_null($koperasi_id)) {
                return Response::json(['status' => 0, 'message' => 'session key not found']);
            }
        
        $input = $request->all();
        //print_r($input);die();
        $id=$input['pembiayaansyariah_id'];
        $pembiayaansyariah=Pembiayaansyariah::where('id',$id)->first();
        $sisa=$pembiayaansyariah->sisa;
        $sisatenor=$pembiayaansyariah->sisatenor;
        $angsuran=$input['angsuran'];        

        //print_r($angsuran);die();
        if($sisatenor == 1){
            if($angsuran==$sisa){
                 unset($input['session_key']);
        
        $insert = DB::connection('mysql2')->select('call sp_insertPembiayaanAngsuran (?,?,?,?,?)',[$input['pembiayaansyariah_id'], $input['angsuran'], $input['metode'], $input['nobukti'], $input['tanggalbayar']]);
         }
        
        if ($angsuran<$sisa) {
            return Response::json(['status' => 0, 'message' => 'Sisa Tenor Habis']);
        }

        if ($angsuran>$sisa) {
            return Response::json(['status' => 0, 'message' => 'Sisa Tenor Habis']);
        }

        
        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $input]);
        
        }

        unset($input['session_key']);
        
        $insert = DB::connection('mysql2')->select('call sp_insertPembiayaanAngsuran (?,?,?,?,?)',[$input['pembiayaansyariah_id'], $input['angsuran'], $input['metode'], $input['nobukti'], $input['tanggalbayar']]);
        
        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $input]);
    }

    public function getmutasi(Request $request)
    {
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }       
        $input = $request->all();
        $tab_id = $request->input('tab_id');
        $insert = DB::connection('mysql2')->select('call sp_getMutasiTabunganSyariah (?)',[$tab_id]);   
                if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }       
        $getby = Tabungansyariah::where(['id'=>$tab_id])->first();
        $input['tabunganproduk_id'] = $getby->tabunganproduk_id;
        $input['anggotakoperasi_id'] =$getby->anggotakoperasi_id;
        $input['rekening']  = $getby->rekening;
        $input['bonuscurrency'] = $getby->bonuscurrency;
        $input['bonuspersen'] = $getby->bonuspersen;
        $input['diambilpada'] = $getby->diambilpada;

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $insert, 'data2' => $input]);
    }    
}