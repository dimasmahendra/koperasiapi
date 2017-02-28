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
use App\Adminkoperasi;
use App\Anggotakoperasi;
use App\Pengurus;
use App\Tahunoperasi;
Use App\Koperasi;
Use App\Pengesahan;
Use App\Anggarandasar;
Use App\Indikatorusaha;
Use App\Karyawan;
Use App\Sektorusaha;
Use App\Kelompokkoperasi;
use App\Sessionkementerian;
use App\Komponenpenilaianksp;
use App\Indikatorkesehatankoperasi;
use App\Aspekpertanyaan;
use App\Komponenpenilaianmanajemenksp;
use App\Predikatkesehatanksp;
use App\Sekunderkoperasi;

class Apiv8Controller extends Controller
{
    private function createOrUpdateSessionkoperasi($adminkoperasi_id = null)
    {
        $session_key = null;
        if (!is_null($adminkoperasi_id)) {
            // dd($adminkoperasi_id);
            $expired = Carbon::now()->addMinutes(30);
            $session = Sessionkoperasi::where(['adminkoperasi_id' => $adminkoperasi_id])->first();
            if (is_null($session)) {
                Sessionkoperasi::create(['adminkoperasi_id' => $adminkoperasi_id, 'status' => 1, 'session_key' => str_random(16)]);
                $session = Sessionkoperasi::where(['adminkoperasi_id' => $adminkoperasi_id])->first();
            }
            $session->update(['status' => 1, 'expired_at' => $expired]);
            if (!is_null($session)) {
                $session_key = $session->session_key;
            }
        }
        return $session_key;
    }
    private function getAdminkoperasiId($session_key = null)
    {
        $adminkoperasi_id = null;
        if (!is_null($session_key)) {
            $session = Sessionkoperasi::where(['session_key' => $session_key])->first();
            if (!is_null($session)) {
                $adminkoperasi_id = Adminkoperasi::find($session->adminkoperasi_id);
                if (!is_null($adminkoperasi_id)) {
                    $adminkoperasi_id = $adminkoperasi_id->id;
                    $this->createOrUpdateSessionkoperasi($session->adminkoperasi_id);
                }
            }
        }
        return $adminkoperasi_id;
    }
    private function checkIfSessionkoperasiExpired($session_key = null)
    {
        $boolean = false;
        if (!is_null($session_key)) {
            $session = Sessionkoperasi::where(['session_key' => $session_key])->where('expired_at', '>=', Carbon::now())->first();
            if (!is_null($session)) {
                $boolean = true;
                $this->createOrUpdateSessionkoperasi($session->adminkoperasi_id);
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
//==================Kementrian
    private function createOrUpdateSessionkementerian($adminkementerian_id = null){
        $session_key = null;
        if (!is_null($adminkementerian_id)) {
            // dd($adminkementerian_id);
            $expired = Carbon::now()->addMinutes(30);
           // dd($expired);
            $session = Sessionkementerian::where(['adminkementerian_id' => $adminkementerian_id])->first();
            if (is_null($session)) {
                Sessionkementerian::create(['adminkementerian_id' => $adminkementerian_id, 'status' => 1, 'session_key' => str_random(16)]);
                $session = Sessionkementerian::where(['adminkementerian_id' => $adminkementerian_id])->first();
            }
            $session->update(['status' => 1, 'expired_at' => $expired]);
            if (!is_null($session)) {
                $session_key = $session->session_key;
            }
        }
        return $session_key;
    }
    private function getAdminkementerianId($session_key = null){
        $adminkementerian_id = null;
        if (!is_null($session_key)) {
            $session = Sessionkementerian::where(['session_key' => $session_key])->first();
            if (!is_null($session)) {
                $adminkementerian_id = Adminkementerian::find($session->adminkementerian_id);
                if (!is_null($adminkementerian_id)) {
                    $adminkementerian_id = $adminkementerian_id->id;
                    $this->createOrUpdateSessionkementerian($session->adminkementerian_id);
                }
            }
        }
        return $adminkementerian_id;
    }
    private function checkIfSessionKementerianExpired($session_key = null){
        $boolean = false;
        if (!is_null($session_key)) {
            $session = Sessionkementerian::where(['session_key' => $session_key])->where('expired_at', '>=', Carbon::now())->first();
            if (!is_null($session)) {
                $boolean = true;
                $this->createOrUpdateSessionkementerian($session->adminkementerian_id);
            }
        }
        return $boolean;
    }
//===============================================================================================================================//
    private function setNullHandler($array=null){
        if(is_array($array)) {
            foreach ($array as $key => $value) {
            	foreach ($value as $key => $value2) {
            		if(!isset($value2))
            		$value->$key="0";
            	}
            }
        }
        return $array;
    }

    public function insertsusunankepengurusan(Request $request){
    	$input = $request->all();
    	//$session_key= $input->session_key;
    	$session_key= $input['session_key'];
    	if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
	    $koperasi_id=$this->getKoperasiId($session_key);
	    if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }  
	    try{
	        $tahunoperasi_id= Tahunoperasi::where('koperasi_id',$koperasi_id)
	        ->where('status','like','Aktif')->orderBy('id','desc')->limit(1)->get();	
        	if(is_array($input['data']))
    			$data  = $input['data'];
    		else
    			$data  = json_decode($input['data']);
    		foreach ($data as $key => $value) {
    			$int=array();
    			$int['koperasi_id']			= $koperasi_id;
    			$int['jabatan']	            = $value['jabatan'];
    			$int['anggotakoperasi_id']	= $value['anggotakoperasi_id'];
    			$int['tahunoperasi_id']		= $tahunoperasi_id[0]->id;
    			$result[] = Pengurus::Create($int);
    		}
        	//$input['koperasi_id']=$koperasi_id;
	  		// $anggotakoperasi_id	= $request->input('anggotakoperasi_id');//nullable
			// $nama 				= $request->input('nama');
			// $telepon			= $request->input('telepon');
			// $pendidikan			= $request->input('pendidikan');
			// $jabatan			= $request->input('jabatan');
			// $jeniskelamin		= $request->input('jeniskelamin');
			// $status				= $request->input('status');
			// $mulaijabatan		= $request->input('mulaijabatan');
			// $akhirjabatan		= $request->input('akhirjabatan');//nullable
			// $keterangan			= $request->input('keterangan');//nullable
			// $result = Pengurus::Create(['koperasi_id' => $koperasi_id, 'anggotakoperasi_id' => $anggotakoperasi_id,'nama' => $nama,'telepon' => $telepon,'pendidikan' => $pendidikan, 'jabatan' => $jabatan, 'jeniskelamin' => $jeniskelamin,'status' => $status,'mulaijabatan' => $mulaijabatan,'akhirjabatan' => $akhirjabatan,'keterangan' => $keterangan]);
		}catch(\Exception $e){
			return Response::json(['status' => 0, 'message' => $e]);	
		}
		if (is_null($result)) {
			return Response::json(['status' => 0, 'message' => 'Input Gagal']);	
		}
		return Response::json(['status' => 1,'session_key' => $session_key, 'message' => 'Input Berhasil', 'data' => $result]);	
    }

    public function deletesusunankepengurusan(Request $request){
        //$session_key= $input['session_key'];
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $koperasi_id=$this->getKoperasiId($session_key);
            $id         =$request->input('id');
            $data=Pengurus::where('id',$id)->delete();
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => $e]);
        }
        if (count($data)==0) {
            return Response::json(['status' => 0, 'message' => 'Data gagal dihapus']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Berhasil dihapus']);
    }
//=============================================Get All Pengurus=============================================================// 
    public function getketuakoperasi(Request $request){
    	$session_key = $request->input('session_key');
    	if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
       	$data=array();
        try{
	        $koperasi_id=$this->getKoperasiId($session_key);
	        
	       	$data=Pengurus::with('anggotakoperasi')
	       	->with('tahunoperasi')
	       	->where('koperasi_id',$koperasi_id)
	       	->where('jabatan','like','ketua%')
            ->where('jabatan','not like','%pengawas')
	       	->orderBy('jabatan','asc')->get();
	        // foreach ($indikator as $key => $value) {
	        // 	foreach ($value as $key => $value2) {
	        // 		if(!isset($value2))
	        // 		$value->$key="0";
	        // 	}
	        // }
	    }catch(\Exception $e){
	    	return Response::json(['status' => 0, 'message' => $e]);
	    }
	    if (count($data)==0) {
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan/kosong']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Data Ditemukan','data' => $data]);
    }

    public function getsekretariskoperasi(Request $request){
    	$session_key = $request->input('session_key');
    	if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
       	$data=array();
        try{
	        $koperasi_id=$this->getKoperasiId($session_key);
	       	$data=Pengurus::with('anggotakoperasi')
	       	->with('tahunoperasi')
	       	->where('koperasi_id',$koperasi_id)
	       	->where('jabatan','like','sekretaris%')->get();
	    }catch(\Exception $e){
	    	return Response::json(['status' => 0, 'message' => $e]);
	    }
	    if (count($data)==0) {
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan/kosong']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Data Ditemukan','data' => $data]);
    }

    public function getbendaharakoperasi(Request $request){
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $data=array();
        try{
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Pengurus::with('anggotakoperasi')
            ->with('tahunoperasi')
            ->where('koperasi_id',$koperasi_id)
            ->where('jabatan','like','bendahara%')->get();
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => $e]);
        }
        if (count($data)==0) {
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan/kosong']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Data Ditemukan','data' => $data]);
    }

    public function getpengawaskoperasi(Request $request){
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $data=array();
        try{
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Pengurus::with('anggotakoperasi')
            ->with('tahunoperasi')
            ->where('koperasi_id',$koperasi_id)
            ->where('jabatan','like','ketua pengawas')
            ->orWhere('jabatan','like','anggota%')->get();
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => $e]);
        }
        if (count($data)==0) {
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan/kosong']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Data Ditemukan','data' => $data]);
    }

    public function getmanajerkoperasi(Request $request){
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $data=array();
        try{
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Pengurus::with('anggotakoperasi')
            ->with('tahunoperasi')
            ->where('koperasi_id',$koperasi_id)
            ->where('jabatan','like','manajer%')->get();
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => $e]);
        }
        if (count($data)==0) {
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan/kosong']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Data Ditemukan','data' => $data]);
    }
/*========================================End of Get All Pengurus=============================================================*/
    public function insertidentitaskoperasi(Request $request){
        $input = $request->all();
        //$session_key= $input->session_key;
        $session_key= $input['session_key'];
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $koperasi_id=$this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }  
        try
        {  
            if(is_array($input['data']))
                $data  = $input['data'];
            else
                $data  = json_decode($input['data']);
            foreach ($data as $key => $value) {
                $value['koperasi_id']=$koperasi_id;
                switch ($key) {
                    case 'koperasi':
                        //khusus koperasi update!!!
                        $update = Koperasi::find($koperasi_id);
                        if (!empty($update)) {
                            $update->update($value);
                        }
                        $result['Koperasi']=$update;
                        break;
                    case 'pengesahan':
                     //print_r('pengesahan ki=');print_r($value[0]);
                        $result['Pengesahan']=Pengesahan::Create($value);
                        break;
                    case 'anggarandasar':
                        //print_r('anggaran dasar ki=');print_r($value[0]);
                        $result['Anggarandasar']=Anggarandasar::Create($value);
                        break;
                default:
                    break;
                }
            }
        }
        catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => $e]);    
        }
        if (empty($result)) {
            return Response::json(['status' => 0, 'message' => 'Input Gagal']); 
        }
        return Response::json(['status' => 1,'session_key' => $session_key, 'message' => 'Input Berhasil', 'data' => $result]); 
    }

    public function getidentitaskoperasi(Request $request){
    	$session_key = $request->input('session_key');
    	if (($this->checkIfSessionkoperasiExpired($session_key) == false && 
            $this->checkIfSessionKementerianExpired($session_key) == false )||
            ($this->checkIfSessionkoperasiExpired($session_key) == true && !empty($request->input('koperasi_id')))
            ){
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $data=array();
        try{
            if(!empty($request->input('koperasi_id')))
            $koperasi_id= $request->input('koperasi_id');
            else
	        $koperasi_id= $this->getKoperasiId($session_key);

	       	$data['identitaskoperasi']=Koperasi::with(['anggarandasar','pengesahan.pemberibadanhukum','tipekoperasi','bentukkoperasi','sektorusaha'])
            ->where('id',$koperasi_id)->get();
            $data['identitaskoperasi'][0]->kelompokkoperasi=Kelompokkoperasi::
            where('id','like',$data['identitaskoperasi'][0]->kelompokkoperasi_id)->get();
            // print_r(Sektorusaha::where('id',$data['identitaskoperasi'][0]->sektorusaha_id)->get());die();
            $alamat=DB::select('call sp_getAlamatKoperasi(?)',[$koperasi_id]);
            $alamat=$this->setNullHandler($alamat);
           
            $temp= $alamat[0]->kelurahan;
            $alamat[0]->kelurahan=array();
            $alamat[0]->kelurahan['id']=$alamat[0]->kelurahan_id;
            $alamat[0]->kelurahan['nama']=$temp;

            $temp= $alamat[0]->kecamatan;
            $alamat[0]->kecamatan=array();
            $alamat[0]->kecamatan['id']=$alamat[0]->kecamatan_id;
            $alamat[0]->kecamatan['nama']=$temp;

            $temp= $alamat[0]->kabupatenkota;
            $alamat[0]->kabupatenkota=array();
            $alamat[0]->kabupatenkota['id']=$alamat[0]->kabupatenkota_id;
            $alamat[0]->kabupatenkota['nama']=$temp;

            $temp= $alamat[0]->provinsi;
            $alamat[0]->provinsi=array();
            $alamat[0]->provinsi['id']=$alamat[0]->provinsi_id;
            $alamat[0]->provinsi['nama']=$temp;

            unset($alamat[0]->kelurahan_id);
            unset($alamat[0]->kecamatan_id);
            unset($alamat[0]->kabupatenkota_id);
            unset($alamat[0]->provinsi_id);
            $data['alamatlengkap']=$alamat;

            $indikator=DB::select('call sp_getIndikatorKelembagaan(?)',[$koperasi_id]);
            $indikator=$this->setNullHandler($indikator);
            $data['indikatorkelembagaan']=$indikator;

            $usaha=DB::select('call sp_getIndikatorUsaha(?)',[$koperasi_id]);
            $usaha=$this->setNullHandler($usaha);
            $data['indikatorusaha']=$usaha;

            $ketua=DB::select('call sp_getKetuaKoperasi(?)',[$koperasi_id]);
            $ketua=$this->setNullHandler($ketua);
            $data['ketuakoperasi']=$ketua;

            $bendahara=DB::select('call sp_getBendaharaKoperasi(?)',[$koperasi_id]);
            $bendahara=$this->setNullHandler($bendahara);
            $data['bendaharakoperasi']=$bendahara;

            $sekretaris=DB::select('call sp_getSekretarisKoperasi(?)',[$koperasi_id]);
            $sekretaris=$this->setNullHandler($sekretaris);
            $data['sekretariskoperasi']=$sekretaris;

            $pengawas=DB::select('call sp_getPengawasKoperasi(?)',[$koperasi_id]);
            $pengawas=$this->setNullHandler($pengawas);
            $data['pengawaskoperasi']=$pengawas;

            $manajer=DB::select('call sp_getManajerKoperasi(?)',[$koperasi_id]);
            $manajer=$this->setNullHandler($manajer);
            $data['manajerkoperasi']=$manajer;
	    }catch(\Exception $e){
	     	return Response::json(['status' => 0, 'message' => $e]);
	    }
	    if (count($data)==0) {
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Data Ditemukan','data' => $data]);
    }

    public function getindikatorusaha(Request $request){
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $data=array();
        try{
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Indikatorusaha::where('koperasi_id',$koperasi_id)->get();
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (count($data)==0) {
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan/kosong']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Data Ditemukan','data' => $data]);
    }

    public function getindikatorusahaby(Request $request){
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$request->input('id');
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Indikatorusaha::where('id',$id)->where('koperasi_id',$koperasi_id)->get();
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (count($data)==0) {
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan/kosong']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Data Ditemukan','data' => $data]);
    }

    public function insertindikatorusaha(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $koperasi_id=$this->getKoperasiId($session_key);
            $input['koperasi_id']=$koperasi_id;
            unset($input['session_key']);
            $data=Indikatorusaha::Create($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Insert Gagal']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Input Berhasil']);
    }

    public function updateindikatorusaha(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$input['id'];
            $koperasi_id=$this->getKoperasiId($session_key);
            $input['koperasi_id']=$koperasi_id;
            unset($input['session_key']);
            $data=Indikatorusaha::find($id);
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

    public function deleteindikatorusaha(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$input['id'];
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Indikatorusaha::find($id);
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
//Karyawan
    public function getkaryawan(Request $request){
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $data=array();
        try{
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Karyawan::where('koperasi_id',$koperasi_id)->get();
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (count($data)==0) {
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan/kosong']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Data Ditemukan','data' => $data]);
    }

    public function getkaryawanby(Request $request){
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$request->input('id');
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Karyawan::where('id',$id)->where('koperasi_id',$koperasi_id)->get();
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (count($data)==0) {
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan/kosong']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Data Ditemukan','data' => $data]);
    }

    public function insertkaryawan(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $koperasi_id=$this->getKoperasiId($session_key);
            $input['koperasi_id']=$koperasi_id;
            unset($input['session_key']);
            $data=Karyawan::Create($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Insert Gagal']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Input Berhasil']);
    }

    public function updatekaryawan(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$input['id'];
            $koperasi_id=$this->getKoperasiId($session_key);
            $input['koperasi_id']=$koperasi_id;
            unset($input['session_key']);
            $data=Karyawan::find($id);
            $anggotakoperasi= Anggotakoperasi::find($data->anggotakoperasi_id);
            if(!empty($data))
            $data->Update($input);
            if(!empty($anggotakoperasi))
            $anggotakoperasi->Update($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Ubah Gagal']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Ubah Berhasil']);
    }

    public function deletekaryawan(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$input['id'];
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Karyawan::find($id);
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
//Anggarandasar
    public function getanggarandasar(Request $request){
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $data=array();
        try{
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Anggarandasar::where('koperasi_id',$koperasi_id)->get();
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (count($data)==0) {
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan/kosong']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Data Ditemukan','data' => $data]);
    }

    public function getanggarandasarby(Request $request){
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$request->input('id');
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Anggarandasar::where('id',$id)->where('koperasi_id',$koperasi_id)->get();
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (count($data)==0) {
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan/kosong']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Data Ditemukan','data' => $data]);
    }

    public function insertanggarandasar(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $koperasi_id=$this->getKoperasiId($session_key);
            $input['koperasi_id']=$koperasi_id;
            unset($input['session_key']);
            $data=Anggarandasar::Create($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Insert Gagal']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Input Berhasil']);
    }

    public function updateanggarandasar(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$input['id'];
            $koperasi_id=$this->getKoperasiId($session_key);
            $input['koperasi_id']=$koperasi_id;
            unset($input['session_key']);
            $data=Anggarandasar::find($id);
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

    public function deleteanggarandasar(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$input['id'];
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Anggarandasar::find($id);
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
//Pengesahan
    public function getpengesahan(Request $request){
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $data=array();
        try{
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Pengesahan::where('koperasi_id',$koperasi_id)->get();
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (count($data)==0) {
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan/kosong']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Data Ditemukan','data' => $data]);
    }

    public function getpengesahanby(Request $request){
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$request->input('id');
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Pengesahan::where('id',$id)->where('koperasi_id',$koperasi_id)->get();
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (count($data)==0) {
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan/kosong']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Data Ditemukan','data' => $data]);
    }

    public function insertpengesahan(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $koperasi_id=$this->getKoperasiId($session_key);
            $input['koperasi_id']=$koperasi_id;
            unset($input['session_key']);
            $data=Pengesahan::Create($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'Failed', 'log' => $e]);
        }
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Insert Gagal']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'message' => 'Input Berhasil']);
    }

    public function updatepengesahan(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$input['id'];
            $koperasi_id=$this->getKoperasiId($session_key);
            $input['koperasi_id']=$koperasi_id;
            unset($input['session_key']);
            $data=Pengesahan::find($id);
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

    public function deletepengesahan(Request $request){
        $input=$request->all();
        $session_key = $input['session_key'];
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        try{
            $id=$input['id'];
            $koperasi_id=$this->getKoperasiId($session_key);
            $data=Pengesahan::find($id);
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

    public function getkelompokkoperasi(Request $request){
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $data = Kelompokkoperasi::get();
        if (empty($data)) {
             return Response::json(['status' => 0, 'message' => 'Data Kosong / tidak ditemukan']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'data' => $data]); 
    }

    public function getsektorusaha(Request $request){
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $data = Sektorusaha::get();
        if (empty($data)) {
             return Response::json(['status' => 0, 'message' => 'Data Kosong / tidak ditemukan']);
        }
        return Response::json(['status' => 1, 'session_key' => $session_key ,'data' => $data]); 
    }

    public function insertindikatorkesehatan(Request $request){
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $koperasi_id=$this->getKoperasiId($session_key);
        if (empty($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'koperasi tidak di temukan']);
        }
        $input = $request->all();
        $input['koperasi_id'] = $koperasi_id; 
        $insert = Komponenpenilaianksp::Create($input);        
        if (empty($insert)) {
             return Response::json(['status' => 0, 'message' => 'Insert Gagal']);
        } 
        return Response::json(['status' => 1, 'message' => 'Insert Berhasil']); 
    }

    public function insertaspekpertanyaan(Request $request){
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $koperasi_id=$this->getKoperasiId($session_key);
        if (empty($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'koperasi tidak di temukan']);
        }

        $input = $request->all();
        if(is_array($input['data']))
            $data  = $input['data'];
        else
            $data  = json_decode($input['data']);
        foreach ($data as $key => $value) {
            $value['koperasi_id'] = $koperasi_id;
            switch ($key) {
                case 'manajemenumum':
                    $result['manajemenumum'] = Aspekpertanyaan::Create($value);
                    break;
                case 'kelembagaan':
                 //print_r('pengesahan ki=');print_r($value[0]);
                    $result['kelembagaan'] = Aspekpertanyaan::Create($value);
                    break;
                case 'permodalan':
                    //print_r('anggaran dasar ki=');print_r($value[0]);
                    $result['permodalan'] = Aspekpertanyaan::Create($value);
                    break;
                case 'aktiva':
                    //print_r('anggaran dasar ki=');print_r($value[0]);
                    $result['aktiva'] = Aspekpertanyaan::Create($value);
                    break; 
                case 'likuiditas':
                    //print_r('anggaran dasar ki=');print_r($value[0]);
                    $result['likuiditas'] = Aspekpertanyaan::Create($value);
                    break;   
            default:
                break;
            }
        }

        if (empty($result)) {
             return Response::json(['status' => 0, 'message' => 'Insert Aspek Pertanyaan Gagal']);
        }      
        return Response::json(['status' => 1, 'message' => 'Input Berhasil']); 
    }

    public function getscoreindikatorkesehatan (Request $request){
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $koperasi_id=$this->getKoperasiId($session_key);
        if (empty($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'koperasi tidak di temukan']);
        }        
        $get = Komponenpenilaianksp::where('koperasi_id', '=', $koperasi_id)->orderBy('created_at', 'desc')->first();
        $get1 = Aspekpertanyaan::where('koperasi_id', '=', $koperasi_id)
                ->groupBy('aspekmanajemen_id')
                ->orderBy('aspekmanajemen_id', 'desc')
                ->get();
        
        if(!is_null($get) && empty($get1))
        {
            $modal = new Indikatorkesehatankoperasi();
            $modal1 = new Predikatkesehatanksp();
            /*<======================================= Permodalan ========================================>*/
            $permodalanTotalAset = ($get->modalsendiri / $get->totalaset) * 100;        
            $permodalanBerisiko = ($get->modalsendiri / $get->pinjamandiberikanberisiko) * 100;
            $permodalanModalSendiri = ($get->modaltertimbang / $get->atmr) * 100; 
            $data['permodalan'] = $modal->Permodalan($permodalanTotalAset, $permodalanBerisiko, $permodalanModalSendiri);           

            /*<======================================= Kualitas aktifa produktif ===================>*/
            $aktifaVolume = ($get->volumepinjamananggota / $get->volumepinjaman) * 100;
            $aktifaPinjaman = ($get->pinjamanbermasalah / $get->pinjamandiberikan) * 100;
            $aktifaCadangan = ($get->cadanganrisiko / $get->pinjamanbermasalah) * 100;
            $aktifaBerisiko = ($get->pinjamanberisiko / $get->pinjamandiberikan) * 100;
            $data['aktifa'] = $modal->AktifaProduktif($aktifaVolume, $aktifaPinjaman, $aktifaCadangan, $aktifaBerisiko);

            /*<======================================= Aspek Manejemen Umum ===================>*/
            $hasil = json_decode(json_encode($get1), True);
            $a = 1;
            $akhir = array();
            foreach ($hasil as $key => $value) {
                if ($value['aspekmanajemen_id'] == 1) {
                    foreach ($value as $key => $row) {
                        $counts[$key] = $row;
                        $result['manajemenumum'] = array_count_values($counts);   
                    }
                }
                if ($value['aspekmanajemen_id'] == 2) {
                    foreach ($value as $key => $row) {
                        $counts[$key] = $row;
                        $result['kelembagaan'] = array_count_values($counts);                   
                    }
                }
                if ($value['aspekmanajemen_id'] == 3) {
                    foreach ($value as $key => $row) {
                        $counts[$key] = $row;
                        $result['permodalan'] = array_count_values($counts);                   
                    }
                }
                if ($value['aspekmanajemen_id'] == 4) {
                    foreach ($value as $key => $row) {
                        $counts[$key] = $row;
                        $result['aktiva'] = array_count_values($counts);                   
                    }
                }
                if ($value['aspekmanajemen_id'] == 5) {
                    foreach ($value as $key => $row) {
                        $counts[$key] = $row;
                        $result['likuiditas'] = array_count_values($counts);                   
                    }
                }
            }
            $aspekpertanyaan = json_decode(json_encode($result), True);             
            $data['aspekpertanyaan'] = $modal->AspekManajemen($aspekpertanyaan);
            if (!is_null($data['aspekpertanyaan'])) {
                $input['koperasi_id'] = $koperasi_id;
                $input['status'] = 'Aktif';
                $input['aktiva'] = $data['aspekpertanyaan']['aktiva'];
                $input['likuiditas'] = $data['aspekpertanyaan']['likuiditas'];
                $input['kelembagaan'] = $data['aspekpertanyaan']['kelembagaan'];
                $input['umum'] = $data['aspekpertanyaan']['manajemenumum'];
                $input['permodalan'] = $data['aspekpertanyaan']['permodalan'];
                $insert = Komponenpenilaianmanajemenksp::Create($input);
                if (empty($insert)) {
                    return Response::json(['status' => 0, 'message' => 'Input di tabel Komponenpenilaianmanajemenksp gagal']);
                }
            }            
            /*<======================================= Efisiensi  ================================>*/
            $efisiensiBruto = $get->bebanoperasi / $get->partisipasibruto * 100;
            $efisiensiSHU = ($get->bebanusaha / $get->shukotor) * 100;
            $efisiensiPelayanan = ($get->biayakaryawan / $get->volumepinjaman) * 100;
            $data['efisiensi'] = $modal->Efisiensi($efisiensiBruto, $efisiensiSHU, $efisiensiPelayanan);

            /*<================================ Likuditas ========================================>*/
            $likuiditasKas = (($get->kas + $get->bank) / $get->kewajibanlancar) * 100;
            $likuiditasPinjaman = ($get->pinjamandiberikan / $get->danaditerima) * 100;
            $data['likuiditas'] = $modal->Likuiditas($likuiditasKas, $likuiditasPinjaman);

            /*<================================ Kemandirian dan Pertumbuhan ========================================>*/
            $KemandirianPertumbuhanAset = ($get->shusebelumpajak / $get->totalaset) * 100;
            $KemandirianPertumbuhanModalSendiri = ($get->shubagiananggota / $get->totalmodalsendiri) * 100;
            $KemandirianPertumbuhanOperasional = ($get->partisipasinetto / ($get->bebanusaha + $get->bebanperkoperasian)) * 100;
            $data['KemandirianPertumbuhan'] = $modal->KemandirianPertumbuhan($KemandirianPertumbuhanAset, $KemandirianPertumbuhanModalSendiri, $KemandirianPertumbuhanOperasional);

             /*<================================ Jatidiri Koperasi ========================================>*/
            $JatidiriKoperasiBrutto = ($get->partisipasibruto / ($get->partisipasibruto + $get->pendapatan)) * 100;
            $JatidiriKoperasiPEA = ($get->pea / ($get->simpananpokok + $get->simpananwajib)) * 100;
            $data['JatidiriKoperasi'] = $modal->JatidiriKoperasi($JatidiriKoperasiBrutto, $JatidiriKoperasiPEA);
           
            if (empty($data)) {
                 return Response::json(['status' => 0, 'message' => 'Data Konversi Gagal']);
            }
            elseif (!is_null($data)) {
                $totalscore = array();
                foreach ($data as $index => $value) {
                    foreach ($value as $key => $row) {
                        $totalscore[$key] = $row;
                    }
                }
                $totalscores = array_sum($totalscore);
                $input1['skor'] = $totalscores;
                $input1['predikat'] = $modal1->ScoretoPredikat($totalscores);
                $input1['koperasi_id'] = $koperasi_id;
                $input1['tanggal'] = date('Y-m-d');
                $input1['status'] = 'Aktif';
                $insert1 = Predikatkesehatanksp::Create($input1);
                if (empty($insert1)) {
                    return Response::json(['status' => 0, 'message' => 'Insert di tabel predikat gagal']);
                }
            }
        }    
        else{
            $getGagal['permodalan'] = array('permodalanTotalAset' => 0, 'permodalanBerisiko' => 0, 'permodalanModalSendiri' => 0);
            $getGagal['aktifa'] = array('aktifaVolume' => 0, 'aktifaPinjaman' => 0, 'aktifaCadangan' => 0, 'aktifaBerisiko' => 0);
            $getGagal['aspekpertanyaan'] = array('likuiditas' => 0, 'aktiva' => 0, 'permodalan' => 0, 'kelembagaan' => 0, 'manajemenumum' => 0);
            $getGagal['efisiensi'] = array('efisiensiBruto' => 0, 'efisiensiSHU' => 0, 'efisiensiPelayanan' => 0);
            $getGagal['likuiditas'] = array('likuiditasKas' => 0, 'likuiditasPinjaman' => 0);
            $getGagal['KemandirianPertumbuhan'] = array('KemandirianPertumbuhanAset' => 0, 'KemandirianPertumbuhanModalSendiri' => 0, 'KemandirianPertumbuhanOperasional' => 0);
            $getGagal['JatidiriKoperasi'] = array('JatidiriKoperasiBrutto' => 0, 'JatidiriKoperasiPEA' => 0);
            $getGagal['predikat'] = array('skor' => '', 'predikat' => '', 'tanggal' => '');
            return Response::json(['status' => 0, 'message' => 'Data Gagal', 'data' => $getGagal]);
        }  
        $data['predikat'] = Predikatkesehatanksp::orderBy('created_at', 'desc')->first();      
        return Response::json(['status' => 1, 'session_key' => $session_key ,'data' => $data]);        
    }

     public function getindikatorkesehatan(Request $request){
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $koperasi_id = $this->getKoperasiId($session_key);
        if (empty($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'koperasi tidak di temukan']);
        }

        $get = Sekunderkoperasi::where(['sekunder_id' => $koperasi_id])->with('koperasi', 'predikatkesehatanksp')->get();        
        //print_r($get);die();    

        if (empty($get)) {
             return Response::json(['status' => 0, 'message' => 'Data Gagal']);
        } 
        return Response::json(['status' => 1, 'message' => 'Data Di temukan', 'data' => $get]); 
    }

    public function getdetilindikatorusaha(Request $request){
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }       

        $koperasi_id = $request->input('koperasi_id');
        $get = Komponenpenilaianksp::where('koperasi_id', '=', $koperasi_id)->orderBy('created_at', 'desc')->first();  
        $get1 = Komponenpenilaianmanajemenksp::where('koperasi_id', '=', $koperasi_id)->orderBy('created_at', 'desc')->first();
        //print_r($get);die();    

        if(!is_null($get) && !is_null($get1))
            {
                $modal = new Indikatorkesehatankoperasi();
                $modal1 = new Predikatkesehatanksp();
                /*<======================================= Permodalan ========================================>*/
                $permodalanTotalAset = ($get->modalsendiri / $get->totalaset) * 100;  
                //print_r($permodalanTotalAset);die();      
                $permodalanBerisiko = ($get->modalsendiri / $get->pinjamandiberikanberisiko) * 100;
                $permodalanModalSendiri = ($get->modaltertimbang / $get->atmr) * 100; 
                $data['permodalan'] = $modal->Permodalan($permodalanTotalAset, $permodalanBerisiko, $permodalanModalSendiri);

                /*<======================================= Kualitas aktifa produktif ===================>*/
                $aktifaVolume = ($get->volumepinjamananggota / $get->volumepinjaman) * 100;
                $aktifaPinjaman = ($get->pinjamanbermasalah / $get->pinjamandiberikan) * 100;
                $aktifaCadangan = ($get->cadanganrisiko / $get->pinjamanbermasalah) * 100;
                $aktifaBerisiko = ($get->pinjamanberisiko / $get->pinjamandiberikan) * 100;
                $data['aktifa'] = $modal->AktifaProduktif($aktifaVolume, $aktifaPinjaman, $aktifaCadangan, $aktifaBerisiko);

                /*<======================================= Aspek Manejemen Umum ===================>*/                
                $data['aspekpertanyaan']['aktiva'] = $get1->aktiva;
                $data['aspekpertanyaan']['likuiditas'] = $get1->likuiditas;
                $data['aspekpertanyaan']['kelembagaan'] = $get1->kelembagaan;
                $data['aspekpertanyaan']['manajemenumum'] = $get1->umum;
                $data['aspekpertanyaan']['permodalan'] = $get1->permodalan;     
                /*<======================================= Efisiensi  ================================>*/
                $efisiensiBruto = $get->bebanoperasi / $get->partisipasibruto * 100;
                $efisiensiSHU = ($get->bebanusaha / $get->shukotor) * 100;
                $efisiensiPelayanan = ($get->biayakaryawan / $get->volumepinjaman) * 100;
                $data['efisiensi'] = $modal->Efisiensi($efisiensiBruto, $efisiensiSHU, $efisiensiPelayanan);

                /*<================================ Likuditas ========================================>*/
                $likuiditasKas = (($get->kas + $get->bank) / $get->kewajibanlancar) * 100;
                $likuiditasPinjaman = ($get->pinjamandiberikan / $get->danaditerima) * 100;
                $data['likuiditas'] = $modal->Likuiditas($likuiditasKas, $likuiditasPinjaman);

                /*<================================ Kemandirian dan Pertumbuhan ========================================>*/
                $KemandirianPertumbuhanAset = ($get->shusebelumpajak / $get->totalaset) * 100;
                $KemandirianPertumbuhanModalSendiri = ($get->shubagiananggota / $get->totalmodalsendiri) * 100;
                $KemandirianPertumbuhanOperasional = ($get->partisipasinetto / ($get->bebanusaha + $get->bebanperkoperasian)) * 100;
                $data['KemandirianPertumbuhan'] = $modal->KemandirianPertumbuhan($KemandirianPertumbuhanAset, $KemandirianPertumbuhanModalSendiri, $KemandirianPertumbuhanOperasional);

                 /*<================================ Jatidiri Koperasi ========================================>*/
                $JatidiriKoperasiBrutto = ($get->partisipasibruto / ($get->partisipasibruto + $get->pendapatan)) * 100;
                $JatidiriKoperasiPEA = ($get->pea / ($get->simpananpokok + $get->simpananwajib)) * 100;
                $data['JatidiriKoperasi'] = $modal->JatidiriKoperasi($JatidiriKoperasiBrutto, $JatidiriKoperasiPEA);
                //print_r($data);die();
                if (empty($data)) {
                     return Response::json(['status' => 0, 'message' => 'Data Konversi Gagal']);
                }
            }    
        else{
            $getGagal['permodalan'] = array('permodalanTotalAset' => 0, 'permodalanBerisiko' => 0, 'permodalanModalSendiri' => 0);
            $getGagal['aktifa'] = array('aktifaVolume' => 0, 'aktifaPinjaman' => 0, 'aktifaCadangan' => 0, 'aktifaBerisiko' => 0);
            $getGagal['aspekpertanyaan'] = array('likuiditas' => 0, 'aktiva' => 0, 'permodalan' => 0, 'kelembagaan' => 0, 'manajemenumum' => 0);
            $getGagal['efisiensi'] = array('efisiensiBruto' => 0, 'efisiensiSHU' => 0, 'efisiensiPelayanan' => 0);
            $getGagal['likuiditas'] = array('likuiditasKas' => 0, 'likuiditasPinjaman' => 0);
            $getGagal['KemandirianPertumbuhan'] = array('KemandirianPertumbuhanAset' => 0, 'KemandirianPertumbuhanModalSendiri' => 0, 'KemandirianPertumbuhanOperasional' => 0);
            $getGagal['JatidiriKoperasi'] = array('JatidiriKoperasiBrutto' => 0, 'JatidiriKoperasiPEA' => 0);
            $getGagal['predikat'] = array('skor' => '', 'predikat' => '', 'tanggal' => '');
            return Response::json(['status' => 0, 'message' => 'Data Gagal', 'data' => $getGagal]);
        }  
        
        $data['predikat'] = Predikatkesehatanksp::where('koperasi_id', '=', $koperasi_id)->orderBy('created_at', 'desc')->first();      
        return Response::json(['status' => 1, 'session_key' => $session_key ,'data' => $data]);  

        if (empty($get)) {
             return Response::json(['status' => 0, 'message' => 'Data Gagal']);
        } 
        return Response::json(['status' => 1, 'message' => 'Data Di temukan', 'data' => $get]); 
    }
}