<?php
/* API Mobile */
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Session;
use App\Sessionkoperasi;
use App\Anggotakoperasi;
use App\Simpananberjangka;
use App\Tabungan;
use App\Peminjaman;
use App\Peminjamandetail;
use App\Koperasi;
use App\Tipekoperasi;

use Carbon\Carbon;
use DB;
use Response;

class apiv5Controller extends Controller
{
    private function createOrUpdateSession($anggotakoperasi_id = null){
        $session_key = null;
        if (!is_null($anggotakoperasi_id)) {
            $expired = Carbon::now()->addDays(7);
            $session = Session::where(['anggotakoperasi_id' => $anggotakoperasi_id])->first();
            if (is_null($session)) {
                Session::create(['anggotakoperasi_id' => $anggotakoperasi_id, 'status' => 1, 'session_key' => str_random(16)]);
                $session = Session::where(['anggotakoperasi_id' => $anggotakoperasi_id])->first();
            }
            $session->update(['status' => 1, 'expired_at' => $expired]);
            if (!is_null($session)) {
                $session_key = $session->session_key;
            }
        }
        return $session_key;
    }

    private function firstLogin($anggotakoperasi_id = null){
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

    private function getAngggotakoperasiId($session_key = null){
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

    private function checkIfSessionExpired($session_key = null){
        $boolean = false;
        if (!is_null($session_key)) {
            $session = Session::where(['session_key' => $session_key])->where('expired_at', '>=', Carbon::now())->first();
            if (!is_null($session)) {
                $boolean = true;
                $this->createOrUpdateSession($session->anggotakoperasi_id);
            }
        }
        return $boolean;
    }

    private function getKoperasiId($session_key = null){
        $koperasi_id = null;
        if (!is_null($session_key)) {
            $session = Session::where(['session_key' => $session_key])->where('expired_at', '>=', Carbon::now())->first();
            if (!is_null($session)) {
                $anggotakoperasi_id = Anggotakoperasi::find($session->anggotakoperasi_id);
                if (!is_null($anggotakoperasi_id)) {
                    $koperasi_id = $anggotakoperasi_id->koperasi_id;
                }
            }
        }
        return $koperasi_id; 
    }
	
	
	
/*------------ Get Iuran Wajib ----------------*/
	public function getiuranwajibmember(Request $request){
        
		/* ---------- Checking Authority ---------- */
		$session_key = $request->input('session_key');
		
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
		
        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/* ---------- Checking Authority ---------- */
		
		$data = Anggotakoperasi::find($anggotakoperasi_id);
		$id_unik = $data->idunik;
		
        $member = DB::select('call sp_getTagihanSimpananWajibAnggota(?)',[$id_unik]);
						
        if ($member[0]->status == 0) {
            return Response::json(['status' => 1, 'message' => 'Tidak ada tagihan']);
        }

        return Response::json(['status' => 1, 'message' => 'Ada Tagihan',
            'session_key' => $session_key, 'data' => $member]);
    }
	
	public function getiuranwajibmemberssp(Request $request){
		
		$id_unik = $request->input('anggotakoperasi_id');
		
		if($id_unik == null){
			return Response::json(['status' => 0, 'message' => 'Anggota Koperasi Kosong']);
		}
		
		$ang = Anggotakoperasi::where('idunik',$id_unik)->first();
		//print_r($ang); die();
		$id = $ang->id;
		//print_r($id); die();
		$sess = Session::where('anggotakoperasi_id',$id)->first();
		$session_key = $sess->session_key;
		
		$data = Anggotakoperasi::find($id);
		$id_unik = $data->idunik;
		//$id_unik = $request->input('unik_id');
		//print_r($id_unik); die();
        $member = DB::select('call sp_getTagihanSimpananWajibAnggota(?)',[$id_unik]);
		
		$a = new Apiv5Controller;
		$a->a = "a";
		$a->b = "b";
		$a->c = "c";
		$a->d = "d";
		
        if ($member[0]->status == 0) {
            return Response::json(['status' => 0, 'message' => 'Tidak ada tagihan','session_key' => $session_key, 'tes' => $a]);
        }

        return Response::json(['status' => 1, 'message' => 'Ada Tagihan', 'session_key' => $session_key,
								'data' => $member]);
    }
	
/*------------ Insert Iuran Wajib ----------------*/
	public function insertiuranwajibanggota(Request $request){
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		
		$koperasi_id = $request->input('koperasi_id');
		$periode = $request->input('periode');
		
		$nama_koperasi = $request->input('nama_koperasi');
		$nama_anggota = $request->input('nama_anggota');
		$total_tagihan = $request->input('total_tagihan');
		$jumlah_tagihan = $request->input('jumlah_tagihan');
		
		$periodearr = explode(', ',$periode);
		
		foreach($periodearr as $period){
			$profile = DB::select('call sp_insertSimpananWajibAnggota(?,?,?)',[$koperasi_id, $anggotakoperasi_id, $period]);
		}
		$data['namaanggotakoperasi'] = $nama_anggota;
		$data['namakoperasi'] = $nama_koperasi;
		$data['totaltagihansimpananwajib'] = $total_tagihan;
		$data['tagihan'] = $jumlah_tagihan;
						
		if($periode == null){
			return Response::json(['status' => 0, 'message' => 'inputkan periode']);
		}
		return Response::json(['status' => 1, 'message' => 'Sukses dibayarkan', 'data' => $data]);

    }
	
/*------------ Ambil Token ----------------*/
	public function ambiltoken(Request $request){
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'anggota koperasi tidak ditemukan']);
        }
		
		$input = $request->all();
		
		$profile = DB::select('call sp_getToken(?,?)',[$input['koperasi_id'], $input['anggotakoperasi_id']]);
		
		if($profile == null){
			return Response::json(['status' => 0, 'message' => 'Failed']);
		}
		return Response::json(['status' => 1, 'message' => 'Token', 'data' => $profile]);

    }
	
/*------------  Get Simpanan Berjangka  ----------------*/
    public function getsimpananberjangka(Request $request){
		
		/*---------- checking authority ----------*/
		$session_key = $request->input('session_key');
		
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
		
        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/*---------- checking authority ----------*/
		
		$select = Simpananberjangka::where('anggotakoperasi_id','=',$anggotakoperasi_id)->get();
		
        if($select->isEmpty()){
            return Response::json(['status' => 0, 'message' => 'Data kosong']);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}

/*------------  Get Simpanan Berjangka Detail ----------------*/
    public function getsimpananberjangkadetail(Request $request){
		
		/*---------- checking authority ----------*/
		$session_key = $request->input('session_key');
		
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
		
        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/*---------- checking authority ----------*/
		
		$dataid = $request->input('id');
		
		$select = Simpananberjangka::with('setingsimka')->where([['anggotakoperasi_id','=',$anggotakoperasi_id],['simpananberjangka.id','=',$dataid]])->get();
		
        if ($select->isEmpty()) {
            return Response::json(['status' => 0, 'message' => 'Data kosong']);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}

/*------------  Get Tabungan Detail  ----------------*/
	public function gettabungandetail(Request $request){
		
		/*---------- checking authority ----------*/
		$session_key = $request->input('session_key');
		
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
		
        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/*---------- checking authority ----------*/
		
		$tabungan_id = Tabungan::where(['anggotakoperasi_id' => $anggotakoperasi_id])->first();
		//print_r($tabungan_id); die();
		if(empty($tabungan_id)){
			return Response::json(['status' => 0, 'message' => 'Tabungan tidak ada','session_key' => $session_key]);
		}
		$tabid = $tabungan_id->id;
		$kopid = $tabungan_id->koperasi_id;
		
        $get['tabungandetail']= DB::select('call sp_getMutasiTabungan(?)',[$tabid]);
		$get["koperasi_id"] = $kopid;
		$get["anggotakoperasi_id"] = $anggotakoperasi_id;
		
        if (is_null($get)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong']);
        }

        return Response::json(['status' => 1, 'message' => 'Success',
            'session_key' => $session_key, 'data' => $get]);
	}
	
	/*------------  Get Rincian Tabungan ----------------*/
	public function getrinciantabunganssp(Request $request){
		
		$idunik = $request->input('anggotakoperasi_id');
		
		$anggotakoperasi = Anggotakoperasi::where('idunik', $idunik)->first();
		if(empty($anggotakoperasi)){
			return Response::json(['status' => 0, 'message' => 'Anggotakoperasi tidak ada']);
		}
		$anggotakoperasi_id = $anggotakoperasi->id;
		$koperasi_id = $anggotakoperasi->koperasi_id;
		
		$koperasi = Koperasi::where('id',$koperasi_id)->first();
		
		$session = Session::where('anggotakoperasi_id', $anggotakoperasi_id)->first();
		$session_key = $session->session_key;
		
		/*$data = Koperasi::where('id', $koperasi_id)->first();
		$data['anggotakoperasi_id'] = $anggotakoperasi_id;
		$data['koperasi_id'] = $koperasi_id;*/
		
		$data = $anggotakoperasi;
		
        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil ditemukan',
            'session_key' => $session_key, 'data' => $data, 'koperasi' => $koperasi]);
	}
	
	/*------------  Insert Tabungan ----------------*/
	public function inserttabunganssp(Request $request){
		
		/*---------- checking authority ----------*/
		$session_key = $request->input('session_key');
		
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
		
        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		$input['metode'] = 2;
		if(!(array_key_exists('refnumberssp', $input))){
			$input['refnumberssp'] = null;
		}
		if(!(array_key_exists('sumberdana', $input))){
			$input['sumberdana'] = null;
		}
		if(!(array_key_exists('penyetor', $input))){
			$input['penyetor'] = null;
		}
		//print_r($input); die();
		$insert = DB::select('call sp_insertTabunganKredit(?,?,?,?,?,?,?)',[$input['koperasi_id'],$input['anggotakoperasi_id'],$input['metode'],$input['setoran'],$input['sumberdana'],$input['refnumberssp'],$input['penyetor']]);
		
        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $input]);
	}

/*------------  Get Peminjaman  ----------------*/
	public function getpeminjaman(Request $request){
		
		/*---------- checking authority ----------*/
		$session_key = $request->input('session_key');
		
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
		
        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/*---------- checking authority ----------*/
		
		$get = Peminjaman::with('anggotakoperasi','peminjamandetail')->where('anggotakoperasi_id',$anggotakoperasi_id)
																	 ->where('status', 'Aktif')->orderBy('id','desc')->get();
		//return Response::json(['data' => $get]); die();
		if($get->isEmpty()){
			return Response::json(['status' => 0, 'message' => 'tidak ada peminjaman']); die();	
		}
		
		$tanggal = $get[0]['jatuhtempo'];
		$peminjaman_id = $get[0]['id'];
		
		foreach($get as $a){
			$get = $a;
		}
		
		$data = Peminjamandetail::where('jatuhtempo',$tanggal)->where('peminjaman_id',$peminjaman_id)->first();
		if(empty($data)){
			 return Response::json(['status' => 0, 'message' => 'Tidak ada Peminjaman']);
		}
		$get['status'] = $data['status'];
		$get['hari_ini'] = date('Y-m-d');
		
        if (is_null($get)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong']);
        }

        return Response::json(['status' => 1, 'message' => 'Success',
            'session_key' => $session_key, 'data' => $get]);
	}
	
	/*------------  Get Peminjaman  ----------------*/
	public function getpeminjamandetail(Request $request){
		
		/*---------- checking authority ----------*/
		$session_key = $request->input('session_key');
		
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
		
        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		
		$koperasi_id = Anggotakoperasi::where('id',$anggotakoperasi_id)->first();
		if(empty($koperasi_id)){
			return Response::json(['status' => 0, 'message' => 'Anggotakoperasi tidak ada']);
		}
		$koperasi = $koperasi_id['koperasi_id'];
		/*---------- checking authority ----------*/
		$peminjaman_id = $request->input('peminjaman_id');
		
		$geta = Peminjamandetail::where('peminjaman_id',$peminjaman_id)->get();
		if($geta->isEmpty()){
			return Response::json(['status' => 0, 'message' => 'Peminjaman id not found']);
		}
		
		$index = 0;
		foreach($geta as $data){
			$get[] = $data;
			$get[$index]['koperasi_id'] = $koperasi;
			$index++;
		}
		
        if (is_null($get)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong']);
        }

        return Response::json(['status' => 1, 'message' => 'Success',
            'session_key' => $session_key, 'data' => $get]);
	}
	
	/*------------  Get Peminjaman Rincian  ----------------*/
	public function getpeminjamanrincian(Request $request){
		
		/*---------- checking authority ----------*/
		$session_key = $request->input('session_key');
		
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
		
        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		
		if($input == null){
			
			return Response::json(['status' => 0, 'message' => 'data kosong']);
		}else if(array_key_exists('koperasi_id',$input) == false){
			
			return Response::json(['status' => 0, 'message' => 'koperasi_id kosong']);
		}elseif(array_key_exists('peminjaman_id',$input)  == false){
			
			return Response::json(['status' => 0, 'message' => 'peminjaman_id kosong']);
		}
		
		$getrefcode = DB::select('call sp_getReffCode(?,?)',[$input['koperasi_id'],$input['peminjaman_id']]);
		
		$array = json_decode(json_encode($getrefcode), True);
		foreach($array[0] as $a => $b){
			$dat[$a] = $b;
		}
				
		if(array_key_exists('reffcode',$dat) == false){
			return Response::json(['status' => 0, 'message' => 'peminajaman tidak terdaftar']);
		}
		
		$getcode = $dat['reffcode'];
		//print_r($getcode); die();
		
		$id = $request->input('id');
		if($id == null){
			return Response::json(['status' => 0, 'message' => 'id kosong']);
		}
		$idarr = explode('/',$id);
		
		$inputx = ["idunik" => $getcode];
		//print_r($inputx); die();
		
		foreach($idarr as $id_value){
			$update= Peminjamandetail::where('id',$id_value)->update(['idunik' => $getcode]);
		}
		
		$get = Peminjamandetail::where('idunik',$getcode)->with('peminjaman.koperasi','peminjaman.anggotakoperasi')->get();
		//$get['refcode'] = $getcode;
		
        if ($get->isEmpty()) {
            return Response::json(['status' => 0, 'message' => 'Data kosong']);
        }

        return Response::json(['status' => 1, 'message' => 'Success',
            'session_key' => $session_key, 'data' => $get]);
	}
	
	/*------------ Get Peminjaman Rincian SSP ------------*/
	public function getpeminjamanrincianssp(Request $request){
		
		$input = $request->input('refcode');
		if($input == null){
			return Response::json(['status' => 0, 'message' => 'refcode kosong']);
		}
				
		$get = Peminjamandetail::where('idunik', $input)->get();
		if($get->isEmpty()){
			return Response::json(['status' => 0, 'message' => 'Data kosong']);
		}
		
		$peminjaman_id = $get[0]->peminjaman_id;
		
		
		$get['peminjaman'] = Peminjaman::where('id', $peminjaman_id)->with('koperasi','anggotakoperasi')->first();
		if(empty($get['peminjaman'])){
			return Response::json(['status' => 0, 'message' => 'Peminjaman kosong']);
		}
		
		$anggotakoperasi_id = $get['peminjaman']->anggotakoperasi_id;
		
		$session_key = Session::where('anggotakoperasi_id',$anggotakoperasi_id)->select('session_key')->first();
		if(empty($session_key)){
			return Response::json(['status' => 0, 'message' => 'Session tidak ada']);
		}
		$sess = $session_key->session_key;
		
        if (is_null($get)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong']);
        }

        return Response::json(['status' => 1, 'message' => 'Success', 'session_key' => $sess,
             'data' => $get]);
	}
	
	/*------------  Insert Peminjaman ----------------*/
	public function prosespeminjamandetailssp(Request $request){
		
		$input = $request->all();
		//print_r($input); die();
		$pemid = $input['detail_id'];
		
		$id = explode(',',$pemid);
		//print_r($id); die();
		$metode = 2;
		foreach($id as $idx){
			$get = DB::select('call sp_updatePeminjamanDetail(?,?,?)',[$input['peminjaman_id'], $idx, $metode]);
		}
		
        if (is_null($get)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Success',
             'data' => $get]);
	}
	
	/*------------  Get Profile  ----------------*/
    public function getprofile(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }


        $profile = Anggotakoperasi::with('koperasi.tipekoperasi')->with('kelurahan.kecamatan.kabupatenkota.provinsi')->where(['anggotakoperasi.id' => $anggotakoperasi_id])->first();

        if (is_null($profile)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Profile ditemukan',
            'session_key' => $session_key, 'data' => $profile]);

    }
}
