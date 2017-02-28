<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Illuminate\Contracts\Hashing\Hasher;
use Carbon\Carbon;

use App\Kementerian;
use App\Adminkementerian;
use App\Sessionkementerian;
use App\Provinsi;
use App\Kabupatenkota;
use App\Kecamatan;
use App\Kelurahan;
use App\Infokementerian;
use App\Seminarkementerian;
use App\Trainingkementerian;
use App\Password_resets;

use App\Adminkoperasi;
use App\Anggotakoperasi;
use App\Koperasi;
use App\Pesankementeriankoperasi;
use App\Skalakoperasi;
use App\Tipekoperasi;

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

class Apiv6Controller extends Controller
{
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
	
	
	
	
	
	/*------------  Get Grafik Transaksi Koperasi  ----------------*/
    public function gettransaksikoperasikonsumsi(Request $request){
		
		/* ----- Checking Authority -----*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/* ----- Checking Authority -----*/
		
		$select = DB::select('call sp_getJumlahTransaksiKoperasiKonsumsi');
		
		//print_r($select); die();
        if (empty($select)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong', 'data' => 0]);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}
	
	/*------------  Get Grafik Transaksi Koperasi Hari Ini  ----------------*/
    public function gettransaksikoperasikonsumsihariini(Request $request){
		
		/* ----- Checking Authority -----*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/* ----- Checking Authority -----*/
		
		$select = DB::select('call sp_getJumlahTransaksiHariIni');
		
		//print_r($select); die();
        if (empty($select)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong', 'data' => 0]);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}
	
	/*------------  Get Grafik Transaksi Koperasi Simpan Pinjam  ----------------*/
    public function gettransaksikoperasisimpanpinjam(Request $request){
		
		/* ----- Checking Authority -----*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/* ----- Checking Authority -----*/
		
		$select = DB::select('call sp_getjumlahtransaksikospin');
		
		//print_r($select); die();
        if (empty($select)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong', 'data' => 0]);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}
	
	/*------------  Get Grafik Transaksi Koperasi Simpan Pinjam Hari Ini  ----------------*/
    public function gettransaksikoperasisimpanpinjamhariini(Request $request){
		
		/* ----- Checking Authority -----*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/* ----- Checking Authority -----*/
		
		$select = DB::select('call sp_getJumlahTransaksiKospinHariIni');
		
		//print_r($select); die();
        if (empty($select)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong', 'data' => 0]);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}
	
	/*------------  Get Grafik Jumlah Koperasi  ----------------*/
    public function getgrafikjumlahkoperasi(Request $request){
		
		/* ----- Checking Authority -----*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/* ----- Checking Authority -----*/
		
		//$tipekoperasi = DB::table('koperasi')->distinct('tipekoperasi_id')->get();
		$koperasi = DB::table('tipekoperasi')->select('id')->get();
		foreach($koperasi as $kop){
			$koperasiid[] = $kop->id;
		}
		//print_r($koperasiid); die();
		$input = $request->all();
		$index = 0;

		foreach($koperasiid as $id){
			$select[$id] = DB::select('call sp_getJumlahKoperasiMinggu(?)',[$id]);
		}
		//print_r($select); die();
        if (empty($select)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong', 'data' => 0]);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}
	
	/*------------  Get Grafik Jumlah Koperasi Hari Ini  ----------------*/
	public function getgrafikjumlahkoperasihariini(Request $request){
		
		/* ----- Checking Authority -----*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/* ----- Checking Authority -----*/
				
		$id = $request->input('id');
		
		$select= DB::select('call sp_getJumlahKoperasihariini');
			
		//print_r($select); die();
        if (empty($select)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong', 'data' => 0]);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}
	
	/*------------  Get Grafik Jumlah Anggota  ----------------*/
    public function getgrafikjumlahanggota(Request $request){
		
		/* ----- Checking Authority -----*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/* ----- Checking Authority -----*/
		
		//$tipekoperasi = DB::table('koperasi')->distinct('tipekoperasi_id')->get();
		$koperasi = DB::table('tipekoperasi')->select('id')->get();
		foreach($koperasi as $kop){
			$koperasiid[] = $kop->id;
		}
		//print_r($koperasiid); die();
		foreach($koperasiid as $id){
			$select[$id] = DB::select('call sp_getJumlahAnggotaMinggu(?)',[$id]);
		}
		//print_r($select); die();
        if (empty($select)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong', 'data' => 0]);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}
	
	/*------------  Get Grafik Jumlah Anggota Hari Ini  ----------------*/
	public function getgrafikjumlahanggotahariini(Request $request){
		
		/* ----- Checking Authority -----*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/* ----- Checking Authority -----*/
		
		$select = DB::select('call sp_getJumlahanggotahariini');
		
		//print_r($select); die();
        if (empty($select)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong', 'data' => 0]);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}
	
	/*------------  Get Grafik Jumlah Anggota  ----------------*/
    public function getpetakoperasi(Request $request){
		
		/* ----- Checking Authority -----*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/* ----- Checking Authority -----*/
		
		$select = Koperasi::with('tipekoperasi')->get();
		
        if (empty($select)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong', 'data' => 0]);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}
	
	/*------------  Get Grafik Jumlah Koperasi Perbulan ----------------*/
    public function getgrafikjumlahkoperasiperbulan(Request $request){
		
		/* ----- Checking Authority -----*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/* ----- Checking Authority -----*/
		
		//$tipekoperasi = DB::table('koperasi')->distinct('tipekoperasi_id')->get();
		$koperasi = DB::table('tipekoperasi')->select('id')->get();
		foreach($koperasi as $kop){
			$koperasiid[] = $kop->id;
		}
		//print_r($koperasiid); die();
		$input = $request->all();
		$index = 0;
		foreach($koperasiid as $id){
			$select[$id] = DB::select('call sp_getJumlahKoperasiBulan(?)',[$id]);
		}
		//print_r($select); die();
        if (empty($select)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong', 'data' => 0]);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}
	
	/*------------  Get Grafik Jumlah Koperasi Pertahun ----------------*/
    public function getgrafikjumlahkoperasipertahun(Request $request){
		
		/* ----- Checking Authority -----*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/* ----- Checking Authority -----*/
		
		//$tipekoperasi = DB::table('koperasi')->distinct('tipekoperasi_id')->get();
		$koperasi = DB::table('tipekoperasi')->select('id')->get();
		foreach($koperasi as $kop){
			$koperasiid[] = $kop->id;
		}
		//print_r($koperasiid); die();
		$input = $request->all();
		foreach($koperasiid as $id){
			$select[$id] = DB::select('call sp_getJumlahKoperasiTahun(?)',[$id]);
		}
		//print_r($select); die();
        if (empty($select)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong', 'data' => 0]);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}
	
	/*------------  Get Grafik Jumlah Koperasi Konsumsi Perbulan ----------------*/
	public function getgrafiktransaksikoperasikonsumsiperbulan(Request $request){
		
		/* ----- Checking Authority -----*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/* ----- Checking Authority -----*/
		
		//$tipekoperasi = DB::table('koperasi')->distinct('tipekoperasi_id')->get();
		$input = $request->all();
		$select = DB::select('call sp_getJumlahTransaksiKonsumsiBulan');
		
        if (empty($select)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong', 'data' => 0]);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}
	
	/*------------  Get Grafik Jumlah Koperasi Konsumsi Pertahun ----------------*/
	public function getgrafiktransaksikoperasikonsumsipertahun(Request $request){
		
		/* ----- Checking Authority -----*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/* ----- Checking Authority -----*/
		$input = $request->all();
		$select = DB::select('call sp_getJumlahTransaksiKonsumsiTahun');
			
        if (empty($select)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong', 'data' => 0]);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}
	
	/*------------  Get Grafik Jumlah Koperasi  ----------------*/
    public function getgrafiktransaksikoperasisimpanpinjam(Request $request){
		
		/* ----- Checking Authority -----*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/* ----- Checking Authority -----*/
		
		$input = $request->all();
		
		$select = DB::select('call sp_getJumlahTransaksiKospinBulan');
			
        if (empty($select)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong', 'data' => 0]);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}
	
	/*------------  Get Grafik Jumlah Koperasi  ----------------*/
    public function getgrafiktransaksikoperasisimpanpinjampertahun(Request $request){
		
		/* ----- Checking Authority -----*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/* ----- Checking Authority -----*/
		$input = $request->all();
		
		$select = DB::select('call sp_getJumlahTransaksiKospinTahun');
		
		//print_r($select); die();
        if (empty($select)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong', 'data' => 0]);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}
	
	/*------------  Get Grafik Jumlah Anggota  ----------------*/
    public function getjumlahanggotaperbulan(Request $request){
		
		/* ----- Checking Authority -----*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/* ----- Checking Authority -----*/
		
		//$tipekoperasi = DB::table('koperasi')->distinct('tipekoperasi_id')->get();
		$koperasi = DB::table('tipekoperasi')->select('id')->get();
		foreach($koperasi as $kop){
			$koperasiid[] = $kop->id;
		}
		//print_r($koperasiid); die();
		$input = $request->all();
		$index = 0;
		foreach($koperasiid as $id){
			$select[]= DB::select('call sp_getJumlahAnggotaBulan(?)',[$id]);
		}
		//print_r($select); die();
        if (empty($select)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong', 'data' => 0]);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}
	public function getjumlahanggotapertahun(Request $request){
		
		/* ----- Checking Authority -----*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/* ----- Checking Authority -----*/
		
		//$tipekoperasi = DB::table('koperasi')->distinct('tipekoperasi_id')->get();
		$koperasi = DB::table('tipekoperasi')->select('id')->get();
		foreach($koperasi as $kop){
			$koperasiid[] = $kop->id;
		}
		//print_r($koperasiid); die();
		foreach($koperasiid as $id){
			$select[] = DB::select('call sp_getJumlahAnggotaTahun(?)',[$id]);			
		}
		//print_r($select); die();
        if (empty($select)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong', 'data' => 0]);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}
	
	public function gettipekopeasi(Request $request){
		/* ----- Checking Authority -----*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
		/* ----- Checking Authority -----*/
		
		$select = Tipekoperasi::all();
		
		if (empty($select)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong', 'data' => 0]);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}
	
	/*------------  Forgot Password  ----------------*/
	public function forgotpassword(Password_resets $password_resets, Request $request)
    {
        $input['email'] = $request->input('email');
        $input['token'] = str_random(64);
        $input['expired_at'] = Carbon::now()->addMinutes(5);

        $findtoken = Password_resets::where(['email' => $input['email'], 'status' => 'New'])->where('expired_at', '>=', Carbon::now())->first();

        if (!is_null($findtoken)) {
            return Response::json(['status' => 1, 'message' => 'Anda sudah merequest password reset, tunggu 5 mnt']);
        }


        //dd($idlastinsert);

        $findemail = Adminkementerian::where(['email' => $input['email']])->first();
		
        if (is_null($findemail)) {
            return Response::json(['status' => 0, 'message' => 'Admin tdk ditemukan']);
        }
        $password_resets->create($input);
        $idlastinsert = DB::getPdo()->lastInsertId();

        $ambiltoken = Password_resets::findorfail($idlastinsert);

        $data = [
            'nama' => $findemail->nama,
            'email' => $findemail->email,
            'token' => $ambiltoken->token,
			'username' => $findemail->username
        ];

        $sentmail = Mail::send('sentmail.maillkementerian', $data, function ($message) {
            $message->to(Input::get('email'))->subject('Lupa Password::Kementerian Koperasi dan UKM');
        });

        if (!$sentmail) {
            return Response::json(['status' => 0, 'message' => 'error']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses',
        ]);
    }
	
	public function resetpassword(Adminkementerian $adminkementerian, Request $request){
        $email = $request->input('email');

        $token = $request->input('token');

        $findtoken = Password_resets::where(['email' => $email, 'token' => $token, 'status' => 'New'])->where('expired_at', '>=', Carbon::now())->first();

        if (is_null($findtoken)) {
            return Response::json(['status' => 0, 'message' => 'Request tdk ditemukan atau token expired']);
        }


        $newpass = Hash::make($request->input('password'));

        $update = Adminkementerian::where(['email' => $findtoken->email])->update(['password' => $newpass]);

        if (!$update) {
            return Response::json(['status' => 0, 'message' => 'Update password  gagal']);
        }

        $findtoken->update(['status' => 'Success']);

        return Response::json(['status' => 1, 'message' => 'sukses update password']);
    }
}