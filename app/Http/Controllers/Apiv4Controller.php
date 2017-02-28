<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Adminkoperasi;
use App\Sessionkoperasi;
use App\Simpananberjangka;
use App\Setingsimka;
use App\Pengambilansimka;
use App\Metode;
use App\Minimalsimpanan;
use App\Tabungan;
use App\Anggotakoperasi;
use App\Komentarinformasikoperasi;
use App\Setingtabungan;
use App\Setingdasartabungan;
use App\Koperasi;
use App\Bungapinjam;
use App\Tipebunga;
use App\Peminjaman;
use App\Password_resets;
use App\Pembeliantemp;
use App\Pembeliandetailtemp;
use App\Tahunoperasi;
use App\Pembelian;
use App\Pembeliandetail;
use App\Bookingseminarkoperasi;
use App\Bookingtrainingkoperasi;
use App\Produk;
use App\Setingpeminjaman;
use App\Simpanan;
use App\Notifikasi;

use Carbon\Carbon;
use Response;
use DB;
use Input;
use File;
use Image;
use Mail;
use Hash;

class Apiv4Controller extends Controller
{
	private function createOrUpdateSessionkoperasi($adminkoperasi_id = null){
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
	
	private function checkIfSessionkoperasiExpired($session_key = null){
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

    private function getKoperasiId($session_key = null){
        $koperasi_id = null;
        if (!is_null($session_key)) {
            $session = Sessionkoperasi::where(['session_key' => $session_key])->where('expired_at', '>=', Carbon::now())->first();
            if (!is_null($session)) {
                $adminkoperasi_id = Adminkoperasi::find($session->adminkoperasi_id);
                if (!is_null($adminkoperasi_id)) {
                    $koperasi_id = $adminkoperasi_id->koperasi_id;
                }
            }
        }
        return $koperasi_id;
    }
	
	/*------------  Forgot Password  ----------------*/
	public function forgotpassword(Password_resets $password_resets, Request $request)
    {
        $input['email'] = $request->input('email');
        $input['token'] = str_random(64);
        $input['expired_at'] = Carbon::now()->addMinutes(10);

        $findtoken = Password_resets::where(['email' => $input['email'], 'status' => 'New'])->where('expired_at', '>=', Carbon::now())->first();

        if (!is_null($findtoken)) {
            return Response::json(['status' => 1, 'message' => 'Anda sudah merequest password reset, tunggu 10 mnt']);
        }


        //dd($idlastinsert);

        $findemail = Adminkoperasi::where(['email' => $input['email']])->first();
		
        if (is_null($findemail)) {
            return Response::json(['status' => 0, 'message' => 'User tdk ditemukan']);
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

        $sentmail = Mail::send('sentmail.maill', $data, function ($message) {
            $message->to(Input::get('email'))->subject('Lupa Password::Koperasi Modern');
        });

        if (!$sentmail) {
            return Response::json(['status' => 0, 'message' => 'error']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses',
        ]);

    }
	
	public function resetpassword(Anggotakoperasi $anggotakoperasi, Request $request){
        $email = $request->input('email');

        $token = $request->input('token');

        $findtoken = Password_resets::where(['email' => $email, 'token' => $token, 'status' => 'New'])->where('expired_at', '>=', Carbon::now())->first();

        if (is_null($findtoken)) {
            return Response::json(['status' => 0, 'message' => 'Request tdk ditemukan atau token expired']);
        }


        $newpass = Hash::make($request->input('password'));

        $update = Adminkoperasi::where(['email' => $findtoken->email])->update(['password' => $newpass]);

        if (!$update) {
            return Response::json(['status' => 0, 'message' => 'Update password  gagal']);
        }

        $findtoken->update(['status' => 'Success']);

        return Response::json(['status' => 1, 'message' => 'sukses update password']);
    }
	
	/*------------  Get Simpanan Berjangka  ----------------*/
    public function getsimpananberjangka(Request $request){
		
		$session_key = $request->input('session_key');
		
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			//print_r($koperasi_id); die();
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		//$select1 = Simpananberjangka::where(['koperasi_id' => $koperasi_id])->with('anggotakoperasi','setingsimka')->get();
		$select1 = DB::table('simpananberjangka')->join('anggotakoperasi', 'simpananberjangka.anggotakoperasi_id', '=', 'anggotakoperasi.id')
												 ->join('setingsimka', 'simpananberjangka.setingsimka_id', '=', 'setingsimka.id')
												 ->select('simpananberjangka.*','anggotakoperasi.nama','setingsimka.tenor')->orderBy('simpananberjangka.id', 'asc')->where([['simpananberjangka.koperasi_id', '=', $koperasi_id],['simpananberjangka.status', '=','Belum']])->get();
		$select2 = DB::select('call sp_getTanggalAmbilSimpananBerjangka(?)',[$koperasi_id]);
		//print_r($select['tglambil']);die();
		foreach($select1 as $index => $value){
			$select[$index]= $value;
			$select[$index]->tgl = $select2[$index];
		}

        if (empty($select)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong', 'data' => 0]);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $select]);
	}
	
	/*------------  Insert Simpanan Berjangka  ----------------*/
	public function insertsimpananberjangka(Request $request){		
		$session_key = $request->input('session_key');
	/*---------- checking authority ----------*/
		if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
			return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
		}

		$koperasi_id = $this->getKoperasiId($session_key);
		
		if (is_null($koperasi_id)) {
			return Response::json(['status' => 0, 'message' => 'session key not found']);
		}
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		$insert = DB::select('call sp_insertSimpananBerjangka(?,?,?,?,?,?,?,?)',[$koperasi_id,$input['anggotakoperasi_id'],$input['simpanan'],$input['tenor'],$input['bunga'],$input['sumberdana'],$input['penyetor'],$input['norekening']]);
	
        if (($insert[0]->status == '0') && ($insert[0]->message == 'Jumlah simpanan kurang')) {
            return Response::json(['status' => 0, 'message' => 'Jumlah simpanan kurang']);
        }
        if (($insert[0]->status == '0') && ($insert[0]->message == 'Rekening sudah digunakan')) {
        	return Response::json(['status' => 0, 'message' => 'Rekening sudah digunakan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key]);
	}
	
	/*------------  Ambil Simka  ----------------*/
	public function ambilsimka(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		$input['metode_id'] = 1;
		
		if(!(array_key_exists('refnumberssp', $input))){
			$input['refnumberssp'] = null;
		}
		//print_r($input); die();
		
		$insert = DB::select('call sp_ambilSimka(?,?,?,?,?)',[$input['simka_id'], $input['metode_id'], $input['penerima'], $input['token'], $input['refnumberssp']]);
		
        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'Failed']);
        }

        return Response::json(['status' => 1, 'message' => 'Success',
            'session_key' => $session_key, 'data' => $insert]);
	}
	
	/*------------  Perpanjang Simka  ----------------*/
	public function perpanjangsimka(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		//print_r($input);die();
		//$input = Simpananberjangka::create($input);
		$insert = DB::select('call sp_perpanjangSimka(?,?,?,?,?,?)',[$input['simka_id'], $input['koperasi_id'], $input['anggotakoperasi_id'], $input['tenor'], $input['bunga'], $input['token']]);
		
        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'Failed']);
        }

        return Response::json(['status' => 1, 'message' => 'Success',
            'session_key' => $session_key, 'data' => $insert]);
	}
	
	/*------------  Edit Simpanan Berjangka  ----------------*/
	public function editsimpananberjangka(Request $request){
		
		$session_key = $request->input('session_key');
        $simpananberjangka = $request->input('id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
		
		$simpananberjangka_id = $request->input('id');
        $data=Simpananberjangka::where(['id' => $simpananberjangka_id])->with('koperasi','anggotakoperasi','metode','setingsimka')->first();
		
        if(!$data){
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Update Simpanan Berjangka  ----------------*/
	public function updatesimpananberjangka(Request $request){
		
		$session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $simpananberjangka = $request->input('id');
		
        $info=Simpananberjangka::findorfail($simpananberjangka);
		//print_r($info); die();
        $input = $request->all();

        $update= Simpananberjangka::find($simpananberjangka)->update($input);

        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $update]);
	}
	
	/*------------  Get Simpanan Berjangka Hari ----------------*/
	public function getsimpananberjangkahari(Request $request){
		
		$session_key = $request->input('session_key');
		
		/*------------- Authentication -------------*/
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
		$koperasi_id = $this->getKoperasiId($session_key);
			
		if (is_null($koperasi_id)) {
			return Response::json(['status' => 0, 'message' => 'session key not found']);
		}
		/*------------- Authentication -------------*/

        $data = DB::select('call sp_getSimpananBerjangkaHari(?)', [$koperasi_id]);

        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $data]);
	}
	
	/*------------  Get Simpanan Berjangka Hari ----------------*/
	public function getsimpananberjangkaminggu(Request $request){
		
		$session_key = $request->input('session_key');
		
		/*------------- Authentication -------------*/
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
		$koperasi_id = $this->getKoperasiId($session_key);
			
		if (is_null($koperasi_id)) {
			return Response::json(['status' => 0, 'message' => 'session key not found']);
		}
		/*------------- Authentication -------------*/

        $data = DB::select('call sp_getSimpananBerjangkaMinggu(?)', [$koperasi_id]);

        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $data]);
	}
	
	/*------------  Get Simpanan Berjangka Hari ----------------*/
	public function getsimpananberjangkabulan(Request $request){
		
		$session_key = $request->input('session_key');
		
		/*------------- Authentication -------------*/
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
		$koperasi_id = $this->getKoperasiId($session_key);
			
		if (is_null($koperasi_id)) {
			return Response::json(['status' => 0, 'message' => 'session key not found']);
		}
		/*------------- Authentication -------------*/

        $data = DB::select('call sp_getSimpananBerjangkaBulan(?)', [$koperasi_id]);

        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $data]);
	}
	
	/*------------  Get Setting Simka  ----------------*/
    public function getsetingsimka(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			//print_r($koperasi_id); die();
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		
		//$seting = Setingsimka::where(['koperasi_id' => $koperasi_id ])->get();
		$seting = DB::table('setingsimka')->where('koperasi_id',$koperasi_id)->get();
		
        if(empty($seting) == true) {
			return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan']);	
        }else{
			return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $seting]);	
		}
		
	}
	
	/*------------ Insert Setting Simka  ----------------*/
	public function insertsetingsimka(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		
		$insert = Setingsimka::create($input);

        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $input]);
	}
	
	/*------------  Edit Setting Simka  ----------------*/
	public function editsetingsimka(Request $request){
		
		$id = $request->input('id');
		
		/*---------- checking authority ----------*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
		$koperasi_id = $this->getKoperasiId($session_key);
		
		if (is_null($koperasi_id)) {
			return Response::json(['status' => 0, 'message' => 'session key not found']);
		}
		/*---------- checking authority ----------*/
		
		$setingsimka_id = $request->input('id');
        $data=Setingsimka::where(['id' => $setingsimka_id])->with('koperasi','simpananberjangka','pengambilansimka')->first();

        if(!$data){
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Update Setting Simka  ----------------*/
	public function updatesetingsimka(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$settingid = $request->input('id');
        $info=Setingsimka::findorfail($settingid);

        $input = $request->all();

        $update= Setingsimka::find($settingid)->update($input);

        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'data gagal diupdate']);
        }

        return Response::json(['status' => 1, 'message' => 'Data sukses diupdate',
            'session_key' => $session_key, 'data' => $update]);
	}
	
	/*------------  Delete Setting Simka  ----------------*/
	public function deletesetingsimka(Request $request){
		
		$session_key = $request->input('session_key');
        $id = $request->input('id');
		
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Setingsimka::find($id);
		if(!$info){
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan']);
        }
		
        $del= DB::select('call sp_deleteSetingSimka(?)',[$id]);

        if(!$del){
            return Response::json(['status' => 0, 'message' => 'Delete gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses delete',
            'session_key' => $session_key, 'data' => $del]);
	}
	
	/*------------  Get Pengambilan Simka  ----------------*/
    public function getpengambilansimka(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$profile = Pengambilansimka::with('setingsimka','metode')->get();

        if (is_null($profile)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $profile]);
	}
	
	/*------------ Insert Pengambilan Simka  ----------------*/
	public function insertpengambilansimka(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		
		$insert = Pengambilansimka::create($input);

        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $insert]);
	}
	
	/*------------  Edit Pengambilan Simka  ----------------*/
	public function editpengambilansimka(Request $request){
		
		/*---------- checking authority ----------*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
		$koperasi_id = $this->getKoperasiId($session_key);
		
		if (is_null($koperasi_id)) {
			return Response::json(['status' => 0, 'message' => 'session key not found']);
		}
		/*---------- checking authority ----------*/
		
		$id = $request->input('id');
        $data=Pengambilansimka::where(['id' => $id])->with('metode')->first();

        if(!$data){
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Update Pengambilan Simka  ----------------*/
	public function updatepengambilansimka(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$simpananberjangka = $request->input('id');
        $info=Pengambilansimka::findorfail($simpananberjangka);

        $input = $request->all();

        $update= Jangkawaktu::find($simpananberjangka)->update($input);

        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $update]);
	}
	
	/*------------  Delete Pengambilan Simka  ----------------*/
	public function deletepengambilansimka(Request $request){
		
		$session_key = $request->input('session_key');
        $id = $request->input('id');
		
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
		$info= Setingsimka::find($id);
		if(!$info){
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan']);
        }
        $del= Pengambilansimka::find($id)->delete();

        if (is_null($del)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dihapus',
            'session_key' => $session_key, 'data' => $del]);
	}
	
	/*------------  Get Metode  ----------------*/
    public function getmetode(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$profile = Metode::all();

        if (is_null($profile)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $profile]);
	}
	
	/*------------ Insert Metode ----------------*/
	public function insertmetode(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		
		$insert = Metode::create($input);

        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $insert]);
	}
	
	/*------------  Edit Metode ----------------*/
	public function editmetode(Request $request){
		
		/*---------- checking authority ----------*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
		$koperasi_id = $this->getKoperasiId($session_key);
		//print_r($koperasi_id); die();
		if (is_null($koperasi_id)) {
			return Response::json(['status' => 0, 'message' => 'session key not found']);
		}
		/*---------- checking authority ----------*/
		
		$id = $request->input('id');
        $data=Metode::findorfail($id);

        if(!$data){
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Update Metode ----------------*/
	public function updatemetode(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$simpananberjangka = $request->input('id');
        $info=Metode::findorfail($simpananberjangka);

        $input = $request->all();

        $update= Jangkawaktu::find($simpananberjangka)->update($input);

        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $update]);
	}
	
	/*------------  Delete Metode ----------------*/
	public function deletemetode(Request $request){
		
		$session_key = $request->input('session_key');
        $id = $request->input('id');
		
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
		$info= Setingsimka::find($id);
		if(!$info){
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan']);
        }
        $del= Metode::find($id)->delete();

        if (is_null($del)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dihapus',
            'session_key' => $session_key, 'data' => $del]);
	}
	
	/*------------  Get Minimal Simpanan  ----------------*/
    public function getminimalsimpanan(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$profile = Minimalsimpanan::where(['koperasi_id' => $koperasi_id])->get();

        if (is_null($profile)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $profile]);
	}
	
	/*------------ Insert Minimal Simpanan ----------------*/
	public function insertminimalsimpanan(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		
		$insert = Minimalsimpanan::create($input);

        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $insert]);
	}
	
	/*------------  Edit Minimal Simpanan ----------------*/
	public function editminimalsimpanan(Request $request){
		
		$id = $request->input('id');
		
		/*---------- checking authority ----------*/
		$session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
		$koperasi_id = $this->getKoperasiId($session_key);
		//print_r($koperasi_id); die();
		if (is_null($koperasi_id)) {
			return Response::json(['status' => 0, 'message' => 'session key not found']);
		}
		/*---------- checking authority ----------*/
		
        $data=Minimalsimpanan::where(['koperasi_id' => $koperasi_id])->with('koperasi')->get();

        if(!$data){
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Update Minimal Simpanan ----------------*/
	public function updateminimalsimpanan(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$minimalsimpanan = $request->input('id');
        $info=Minimalsimpanan::findorfail($minimalsimpanan);
		
        $input = $request->all();
		
        $update= Minimalsimpanan::find($minimalsimpanan)->update($input);
		
        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }
		
        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $update]);
	}
	
	/*------------  Delete Minimal Simpanan ----------------*/
	public function deleteminimalsimpanan(Request $request){
		
		$session_key = $request->input('session_key');
        $id = $request->input('id');
		
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
		
		$info= Minimalsimpanan::find($id);
		
		if(!$info){
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan']);
        }
        $del= Metode::find($id)->delete();

        if (is_null($del)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dihapus',
            'session_key' => $session_key, 'data' => $del]);
	}
	
	/*------------ Get Iuran Wajib ----------------*/
	public function getiuranwajib(Request $request){
        $session_key = $request->input('session_key');
		
        if ($this->createOrUpdateSessionkoperasi($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $anggotakoperasi_id = $this->getKoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

		$id_koperasi = $request->input('koperasi_id');
		
        $profile = DB::select('call sp_getListTagihanSimpananWajib(?)',[$id_koperasi]);
		
        if (is_null($profile)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Profile ditemukan',
            'session_key' => $session_key, 'data' => $profile]);

    }
	
	/*------------ Get Iuran Wajib Detail ----------------*/
	public function getiuranwajibdetail(Request $request){
        $session_key = $request->input('session_key');
		
        if ($this->createOrUpdateSessionkoperasi($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $anggotakoperasi_id = $this->getKoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

		$id_unik = $request->input('id_unik');
		
        $member = DB::select('call sp_getTagihanSimpananWajibAnggota(?)',[$id_unik]);

		$dabul = $member[0]->daftarbulan;
		$var1 = explode(',', $dabul);
		
		$dat = $member[0]->daftarbulanhuruf;
		$var = explode(',', $dat);

		$array_baru = array();
	    foreach($var1 as $index => $value){

	        $array_baru[$index]['angka'] = $value;                  
	        $array_baru[$index]['nama'] = $var[$index];    
	    }
		$member[0]->dafbul = $array_baru;		
		//print_r($var); die();
		$data = $member[0]->status;
        if ($data == NULL) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Anggota ditemukan',
            'session_key' => $session_key, 'data' => $member]);

    }
	
	/*------------ Insert Iuran Wajib ----------------*/
	public function insertiuranwajib(Request $request){
		
        $session_key = $request->input('session_key');
			
        if ($this->createOrUpdateSessionkoperasi($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
		
        $koperasi_id = $this->getKoperasiId($session_key);
		
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'Koperasi id tidak ditemukan']);
        }	//return Response::json(['status' => 1, 'message' => 'session key dan anggota koperasi ditemukan']);
		
		$input = $request->all();		
		$periode = $request->input('periode');	
		
		foreach($periode as $period){
			//return Response::json($period);
			$profile = DB::select('call sp_insertSimpananWajibAnggota(?,?,?)',[$koperasi_id, $input['anggotakoperasi_id'],$period]);
		}
		
		if (is_null($profile)) {
			return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
		}

		return Response::json(['status' => 1, 'message' => 'data sukses',
		'session_key' => $session_key, 'data' => $profile]);
		        
    }
	
	/*------------  Get Daftar Tabungan ----------------*/
	public function gettabungan(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'koperasi id tidak ditemukan']);
			}
		/*---------- checking authority ----------*/
		
        $get= Tabungan::where(['koperasi_id' => $koperasi_id])->with('anggotakoperasi')->get();

        if (is_null($get)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data ditemukan',
            'session_key' => $session_key, 'data' => $get]);
	}
	
	/*------------  Get Daftar Tabungan Detail ----------------*/
	public function gettabungandetail(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'koperasi id tidak ditemukan']);
			}
		/*---------- checking authority ----------*/
		$input = $request->all();
        $get= DB::select('call sp_getMutasiTabungan(?)',[$input['tabungan_id']]);		
		$get1 = Anggotakoperasi::with('koperasi')->where('anggotakoperasi.id',$input['anggotakoperasi_id'])->get();
		$get1[0]['tabungan_id'] = $input['tabungan_id'];
		
        if (is_null($get)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dihapus',
            'session_key' => $session_key, 'data' => $get, 'data1' => $get1]);
	}
	
	/*------------  Insert Tabungan ----------------*/
	public function inserttabungan(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		$input['metode'] = 1;
		if(!(array_key_exists('refnumberssp', $input))){
			$input['refnumberssp'] = null;
		}
		//print_r($input); die();
		$insert = DB::select('call sp_insertTabunganKredit(?,?,?,?,?,?,?,?)',[$input['koperasi_id'],$input['anggotakoperasi_id'],$input['metode'],$input['setoran'],$input['sumberdana'],$input['refnumberssp'],$input['penyetor'],$input['norekening']]);
		//print_r($insert);die();
        if ($insert[0]->status == 0) {
            return Response::json(['status' => 0, 'message' => $insert[0]->message]);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $input]);
	}
	
	/*------------  Ambil Tabungan ----------------*/
	public function ambiltabungan(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		if(!(array_key_exists('refnumberssp', $input))){
			$input['refnumberssp'] = null;
		}
		//print_r($input); die();
		$insert = DB::select('call sp_ambilTabungan(?,?,?,?,?,?)',[$input['tabungan_id'],$input['metode_id'],$input['token'],$input['penerima'],$input['jumlah'],$input['refnumberssp']]);
		
        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $input]);
	}
	
	/*------------  Get Seting Tabungan ----------------*/
	public function getsetingtabungan(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$insert = Setingtabungan::where('koperasi_id', $koperasi_id)->get();
		$insert2 = Setingdasartabungan::where('koperasi_id','=',$koperasi_id)->get();
				
        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }
		
        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $insert, 'data2' => $insert2]);
	}
	
	/*------------  Insert Suku Bunga ----------------*/
	public function insertsukubunga(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		
		$insert = Setingtabungan::create($input);
		
        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $insert]);
	}
	
	/*------------  Edit Suku Bunga ----------------*/
	public function editsukubunga(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$id = $request->input('id');
        $data=Setingtabungan::where(['id' => $id])->first();
		
        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Update Suku Bunga ----------------*/
	public function updatesukubunga(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		$id = $request->input('id');
		
        $update= Setingtabungan::find($id)->update($input);
		
        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $update]);
	}
	
	/*------------  Delete Suku Bunga ----------------*/
	public function deletesukubunga(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$id = $request->input('id');
		$info= Setingtabungan::find($id);
		
		if(!$info){
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan']);
        }
        $del= DB::select('call sp_deleteSetingTabungan(?)',[$id]);
		
        if (is_null($del)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $del]);
	}
	
	/*------------  Insert Administrasi ----------------*/
	public function insertadministrasi(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$insert = Setingdasartabungan::create('administrasi',$a);
				
        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }
		
        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $insert, 'data2' => $insert2]);
	}
	
	/*------------  Edit Administrasi Tabungan ----------------*/
	public function editadministrasitabungan(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$id = $request->input('id');
        $data=Setingdasartabungan::where(['id' => $id])->first();
		
        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'Data kosong']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Update Administrasi Tabungan ----------------*/
	public function updateadministrasitabungan(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		$id = $request->input('id');
		
        $update= Setingdasartabungan::find($id)->update($input);
		
        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $update]);
	}
	
	/*------------  Update Administrasi Tabungan ----------------*/
	public function updateperhitunganbungatiapbulan(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		$id = $request->input('id');
        $update= Setingdasartabungan::find($id)->update($input);
		
        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $update]);
	}
	
	/*------------  Get Tabungan Koperasi Hari ----------------*/
	public function gettabungankoperasihari(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$update = DB::select('call sp_getTabunganKoperasiHari(?)', [$koperasi_id]);
		
        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $update]);
	}
	
	/*------------  Get Tabungan Koperasi Minggu ----------------*/
	public function gettabungankoperasiminggu(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$update = DB::select('call sp_getTabunganKoperasiMinggu(?)', [$koperasi_id]);
		
        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $update]);
	}
	
	/*------------  Get Tabungan Koperasi Hari ----------------*/
	public function gettabungankoperasibulan(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$update = DB::select('call sp_getTabunganKoperasiBulan(?)', [$koperasi_id]);
		
        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $update]);
	}
	
	/*------------  Get Peminjaman ----------------*/
	public function getpeminjaman(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$get = DB::table('peminjaman')->join('anggotakoperasi','peminjaman.anggotakoperasi_id','=','anggotakoperasi.id')
									  ->join('bungapinjam','bungapinjam.id','=','peminjaman.bungapinjam_id')
									  ->join('tipebunga','bungapinjam.tipebunga_id','=','tipebunga.id')
									  ->where('peminjaman.koperasi_id',$koperasi_id)
									  ->select('peminjaman.*','anggotakoperasi.nama as nama_anggota_koperasi','tipebunga.nama as nama_bunga')->get();
		
        if (is_null($get)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Success',
            'session_key' => $session_key, 'data' => $get]);
	}
	
	/*------------  Get Peminjaman Detail ----------------*/
	public function getpeminjamandetail(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$id = $request->input('id');
		
		$get = Peminjaman::where('id',$id)->with('peminjamandetail.metode','koperasi','anggotakoperasi')->get();
		
        if (is_null($get)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Success',
            'session_key' => $session_key, 'data' => $get]);
	}
	
	/*------------  Insert Peminjaman ----------------*/
	public function prosespeminjamandetail(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		//print_r($input); die();
		$metode = 1;
		$get = DB::select('call sp_updatePeminjamanDetail(?,?,?)',[$input['peminjaman_id'], $input['detail_id'], $metode]);
			
        if (is_null($get)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Success',
            'session_key' => $session_key, 'data' => $get]);
	}
	
	/*------------  Insert Peminjaman ----------------*/
	public function insertpeminjaman(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$input = $request->input();
		
		$get = DB::select('call sp_insertPeminjaman(?,?,?,?,?,?,?)',[$koperasi_id, $input['anggotakoperasi_id'], $input['jumlah'], $input['tenor'], $input['bunga_id'],$input['keperluan'], $input['token']]);
		//print_r($get); die();
		if($get[0]->status == 0){
			return Response::json(['status' => 0, 'message' => 'Anggota memiliki kewajiban membayar hutang']);
		}
		
		$peminjaman = DB::table('peminjaman')->where('anggotakoperasi_id',$input['anggotakoperasi_id'])->orderby('created_at', 'desc')->first();
		$peminjaman_id = $peminjaman->id;
		
		$metode=1;
		
		$get2 = DB::select('call sp_insertpeminjamandetail(?,?,?)',[$peminjaman_id, $metode, $input['tipebunga_id']]);
		
        if (is_null($get2)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Success',
            'session_key' => $session_key, 'data' => $get2]);
	}
	
	/*------------  Get Minimal Peminajaman ----------------*/
	public function getminimalpeminjaman(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$get = Setingpeminjaman::where('koperasi_id', $koperasi_id)->first();
		
        if (empty($get)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Success',
            'session_key' => $session_key, 'data' => $get]);
	}
	
	/*------------  Insert Minimal Peminajaman ----------------*/
	public function insertminimalpeminjaman(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$input = $request->input();
		
		try{
			$get = Setingpeminjaman::create($input);	
		}catch(\Exception $e){
			return Response::json(['status' => 0, 'message' => 'Data gagal']);
		}
		
		
        if (is_null($get)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Success',
            'session_key' => $session_key, 'data' => $get]);
	}
	
	/*------------  Update Minimal Peminajaman ----------------*/
	public function updateminimalpeminjaman(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		$id = $request->input('id');
		$input = $request->input('minimalpinjam');
		
		$get = Setingpeminjaman::where('id',$id)->update(['minimalpinjam' => $input]);
		
        if (is_null($get)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Success',
            'session_key' => $session_key, 'data' => $get]);
	}
	
	/*------------  Get Setting Peminjaman ----------------*/
	public function getsetingpeminjaman(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$get = DB::table('bungapinjam')->join('tipebunga','bungapinjam.tipebunga_id','=','tipebunga.id')
									   ->where('bungapinjam.koperasi_id',$koperasi_id)
									   ->select('bungapinjam.*','tipebunga.nama')->get();
		$min = Setingpeminjaman::where('koperasi_id',$koperasi_id)->first();
		if(empty($min)){
			$data = 0;
		}else{
			$data = $min['minimalpinjam'];
		}
        if (is_null($get)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Success',
            'session_key' => $session_key, 'data' => $get, 'minimalpinjam' => $data]);
	}
	
	/*------------  Insert Seting Peminjaman ----------------*/
	public function insertsetingpeminjaman(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		
		$insert = Bungapinjam::create($input);
		
        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $insert]);
	}
	
	/*------------  Edit Seting Peminjaman ----------------*/
	public function editsetingpeminjaman(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$id = $request->input('id');
        $data=Bungapinjam::where(['id' => $id])->first();
		
        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Update Seting Peminjaman ----------------*/
	public function updatesetingpeminjaman(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		$id = $request->input('id');
		
        $update= Bungapinjam::find($id)->update($input);
		
        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $update]);
	}
	
	/*------------  Delete Seting Peminjaman ----------------*/
	public function deletesetingpeminjaman(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$id = $request->input('id');
		$info= Bungapinjam::find($id);
		
		if(!$info){
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan']);
        }
        $del= DB::select('call sp_deleteSetingPeminjaman(?)',[$id]);
		
        if (is_null($del)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $del]);
	}
	
	/*------------  Get Tipe Bunga ----------------*/
	public function getpeminjamanhari(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$data = DB::select('call sp_getPeminjamanHari(?)', [$koperasi_id]);
		
        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Get Tipe Bunga ----------------*/
	public function getpeminjamanminggu(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$data = DB::select('call sp_getPeminjamanMinggu(?)', [$koperasi_id]);
		
        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Get Tipe Bunga ----------------*/
	public function getpeminjamanbulan(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$data = DB::select('call sp_getPeminjamanBulan(?)', [$koperasi_id]);
		
        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Get Tipe Bunga ----------------*/
	public function gettipebunga(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$data = Tipebunga::all();
		
        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Get Tipe Bunga Detail ----------------*/
	public function gettipebungadetail(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$id = $request->input('id');
        $data=Tipebunga::where(['id' => $id])->first();
		
        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Komentar koperasi ----------------*/
	public function getkomentar(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$infokoperasi_id = $request->input('infokoperasi_id');
		if($infokoperasi_id == null){
			return Response::json(['status' => 0, 'message' => 'infokoperasi id tidak ada']);
		}
		$insert = DB::table('komentarinformasikoperasi')->join('infokoperasi', 'komentarinformasikoperasi.infokoperasi_id', '=', 'infokoperasi.id')
														->join('anggotakoperasi', 'komentarinformasikoperasi.anggotakoperasi_id', '=', 'anggotakoperasi.id')
														->where('komentarinformasikoperasi.infokoperasi_id','=',$infokoperasi_id)
														->select('komentarinformasikoperasi.*','anggotakoperasi.nama as nama_anggota','anggotakoperasi.foto')->get();
		
        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil',
            'session_key' => $session_key, 'data' => $insert]);
	}
	
	/*------------  Delete Komentar Informasi koperasi ----------------*/
	public function deletekomentarinformasikoperasi (Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$id = $request->input('id');
		$info= Komentarinformasikoperasi::find($id);
		
		if(!$info){
            return Response::json(['status' => 0, 'message' => 'Data tidak ditemukan']);
        }
        $del= Komentarinformasikoperasi::find($id)->delete();
		
        if (is_null($del)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Success',
            'session_key' => $session_key, 'data' => $del]);
	}
	
	/*------------  Insert Anggota Koperasi ----------------*/
    public function insertanggotakoperasi(Request $request)
    {
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $input = $request->all();
        $input['koperasi_id']=$koperasi_id;
        $foto = Input::file('foto');

        if(!is_null($foto)) {

            $filename = date('YmdHis') . '.' . $foto->getClientOriginalExtension();
            $filename_thumb = 'thumb_'.date('YmdHis') . '.' . $foto->getClientOriginalExtension();

            $path = public_path('images/anggotakoperasi/' . $filename);
            $path_thumb = public_path('images/anggotakoperasi/' . $filename_thumb);

            Image::make($foto->getRealPath())->resize(300, 300)->save($path);
            Image::make($foto->getRealPath())->resize(60, 60)->save($path_thumb);

            $foto->image = $filename;
            $input['foto'] = $foto->image;
        }
        else {

            $input['foto'] ='no_image.png';
        }

        $insert = Anggotakoperasi::create($input);

        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $insert]);

    }
	
	/*------------  Update Anggota Koperasi ----------------*/
	public function updateanggotakoperasi (Request $request){
        $session_key = $request->input('session_key');
        $anggotakoperasi_id = $request->input('id');


        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }


        $info=Anggotakoperasi::findorfail($anggotakoperasi_id);
        $input = $request->all();
        $foto = Input::file('foto');

        if(!is_null($foto)) {

            if(file_exists(public_path('images/infokoperasi/'.$info->foto))) {

                File::delete(public_path('images/infokoperasi/'.$info->foto));
                File::delete(public_path('images/infokoperasi/thumb_'.$info->foto));
            }

            $filename = date('YmdHis') . '.' . $foto->getClientOriginalExtension();
            $filename_thumb = 'thumb_'.date('YmdHis') . '.' . $foto->getClientOriginalExtension();

            $path = public_path('images/anggotakoperasi/' . $filename);
            $path_thumb = public_path('images/anggotakoperasi/' . $filename_thumb);

            Image::make($foto->getRealPath())->resize(300, 300)->save($path);
            Image::make($foto->getRealPath())->resize(60, 60)->save($path_thumb);

            $info->foto = $filename;
        }
        $input['foto'] = $info->foto;

        $update= Anggotakoperasi::find($anggotakoperasi_id)->update($input);

        if(!$update){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $update]);

    }
	
	/*------------  Get Notif Seminar Koperasi ----------------*/
	public function getnotifseminarkoperasi(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$data = DB::select('call sp_getNotifSeminarKoperasi(?)',[$koperasi_id]);
				
        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Get Notif Info Koperasi ----------------*/
	public function getnotifinfokoperasi (Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$infoid = $request->input('infoid');
		if($infoid == null){
			return Response::json(['messgae' => 0, 'message' => 'infoid kosong']);
		}
		
		$data = DB::select('call sp_getNotifInfoKoperasi(?,?)',[$koperasi_id, $infoid]);
		
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Insert Viewer Info Koperasi ----------------*/
	public function insertviewerinfokoperasi(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		
		$data = Viewinfokoperasi::create($input);
		
        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Update Notif Seminar Koperasi----------------*/
	public function updatenotifseminarkoperasi(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		$id = $request->input('id');
		
		$data = Bookingseminarkoperasi::where('id',$id)->update(['status' => 'Dibaca']);
				
        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Update Notif Training Koperasi----------------*/
	public function updatenotiftrainingkoperasi(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		$id = $request->input('id');
		
		$data = Bookingtrainingkoperasi::where('id',$id)->update(['status' => 'Dibaca']);
				
        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Insert Pembelian Temp ----------------*/
	public function insertpembeliantemp(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$input2 = "Cash";
		$input3 = $request->input('tanggal');
		if($input3 == null){
			return Response::json(['status' => 0, 'message' => 'tanggal belum diisi']);
		}
		$tahunoperasi = Tahunoperasi::where(['koperasi_id' => $koperasi_id,'status'=>'Aktif'])->select('id')->first();
		if(empty($tahunoperasi)){
			return Response::json(['status' => 0, 'message' => 'Tahun operasi kosong']);
		}
		$tahun = $tahunoperasi->id;
		if(empty($tahun)){
			return Response::json(['status' => 0, 'message' => 'tahun operasi tidak ada']);
		}
		
		$data = Pembeliantemp::create(['koperasi_id' => $koperasi_id,'tahunoperasi_id' => $tahun,'tanggal' => $input3, 'metode' => $input2]);
		
        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Insert Pembelian Detail Temp ----------------*/
	public function insertpembeliandetailtemp(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		
		$data = Pembeliandetailtemp::create($input);
		
        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Get Pembelian Detail Temp ----------------*/
	public function getpembeliandetailtemp(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		//$data = DB::table('pembeliantemp')->where('koperasi_id',$koperasi_id)->join('pembeliandetailtemp','pembeliandetailtemp.pembelian_id','=','pembeliantemp.id')->get();
		
		$data = Pembeliantemp::where('koperasi_id',$koperasi_id)->with('pembeliandetailtemp.produk')->get()->last();
		
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Insert Pembelian Detail ----------------*/
	public function insertpembelian(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$pembelian_id = $request->input('pembelian_id');
		
		$pembeliantemp = Pembeliantemp::where('koperasi_id',$koperasi_id)->select('koperasi_id','tahunoperasi_id','tanggal','metode')->first();
		if(empty($pembeliantemp)){
			return Response::json(['status' => 0, 'message' => 'tidak ada pembelian']);
		}
		
		$totalharga = DB::table('pembeliandetailtemp')->where('pembelian_id',$pembelian_id)->sum('subtotalhargabeli');
		if(empty($totalharga)){
			return Response::json(['status' => 0, 'message' => 'Total tidak ada']);
		}
		$pembeliantemp['totalhargabeli'] = $totalharga;
		
		$pemb = $pembeliantemp->toArray();
		$data = Pembelian::create($pemb);
		
		$last_id = Pembelian::all()->last()->toArray();
		
		if(empty($last_id)){
			return Response::json(['status' => 0, 'message' => 'Data gagal']);
		}
		
		$last = $last_id['id'];
		//print_r($last); die();
		
		$pembeliantemp = Pembeliandetailtemp::where('pembelian_id',$pembelian_id)->select('pembelian_id','tanggal','produk_id','hargabeli','kuantitas','subtotalhargabeli')->get();
		$pembelianarray = $pembeliantemp->toArray();
		
		foreach($pembelianarray as $arr){
			$data = Pembeliandetail::create(['pembelian_id' => $last,'tanggal' => $arr['tanggal'],'produk_id' => $arr['produk_id'], 'hargabeli' => $arr['hargabeli'], 'kuantitas'=>$arr['kuantitas'], 'subtotalhargabeli' => $arr['subtotalhargabeli']]);
			
			$stokproduk = Produk::where('id',$arr['produk_id'])->select('stok')->first();
			$stok = $stokproduk->stok + $arr['kuantitas'];
			$update = Produk::where('id',$arr['produk_id'])->update(['stok' => $stok]);
		}
				
		$delete = Pembeliantemp::where('koperasi_id',$koperasi_id)->delete();
		
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dimasukan',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Delete Pembelian Temp ----------------*/
	public function deletepembeliantemp(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$data = DB::table('pembeliantemp')->where('koperasi_id',$koperasi_id)->delete();
		
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dihapus',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Delete Pembelian ----------------*/
	public function deletepembelian(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$id = $request->input('id');
		
		$data = DB::table('pembelian')->where('id',$id)->delete();
		
        if (empty($data)) {
            return Response::json(['status' => 0, 'message' => 'Data gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dihapus',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Update Notifikasi ----------------*/
	public function updatenotifikasi(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'session key not found']);
			}
		/*---------- checking authority ----------*/
		
		$id = $request->input('id');
		$kategori = $request->input('kategori');
		
		if($id != null){
			$data = Notifikasi::where('id',$id)->update(['status' => "Dibaca"]);	
		}else{
			$data = Notifikasi::where('koperasi_id',$koperasi_id)->where(['status' => "Belum Dibaca"])->whereNotNull($kategori)->update(['status' => "Dibaca"]);	
		}
		
		if(empty($data)){
			return Response::json(['status' => 0, 'message' => $data]);
		}
		
        return Response::json(['status' => 1, 'message' => 'Data berhasil diupdate',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Get Simpanan Pokok ----------------*/
	public function getsimpananpokok(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'koperasi id tidak ditemukan']);
			}
		/*---------- checking authority ----------*/
		
        $get= Simpanan::where(['koperasi_id' => $koperasi_id, 'jenissimpanan' => "Pokok"])->with('anggotakoperasi')->get();

        if ($get->isEmpty()) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dihapus',
            'session_key' => $session_key, 'data' => $get]);
	}
	
	/*------------  Insert Simpanan Pokok ----------------*/
	public function insertsimpananpokok(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'koperasi id tidak ditemukan']);
			}
		/*---------- checking authority ----------*/
		
		$input = $request->all();
		$input['koperasi_id'] = $koperasi_id;
		$input['jenissimpanan'] = "Pokok";
		$input['tanggalbayar'] = Carbon::now();
		$input['status'] = "Belum Diambil";
		
		
        $insert = Simpanan::create($input);

        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dihapus',
            'session_key' => $session_key, 'data' => $insert]);
	}
	
	/*------------  Edit Simpanan Pokok ----------------*/
	public function editsimpananpokok(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'koperasi id tidak ditemukan']);
			}
		/*---------- checking authority ----------*/
		
		$id = $request->input('id');
		$data = Simpanan::find($id);

        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dihapus',
            'session_key' => $session_key, 'data' => $data]);
	}
	
	/*------------  Update Simpanan Pokok ----------------*/
	public function updatesimpananpokok(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'koperasi id tidak ditemukan']);
			}
		/*---------- checking authority ----------*/
		
		$id = $request->input('id');
		$input = $request->all();
		
		$data = Simpanan::find($id)->update($input);

        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dihapus',
            'session_key' => $session_key, 'data' => $input]);
	}
	
	/*------------  Delete Simpanan Pokok ----------------*/
	public function deletesimpananpokok(Request $request){
		
		$session_key = $request->input('session_key');
		/*---------- checking authority ----------*/
			if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
				return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
			}

			$koperasi_id = $this->getKoperasiId($session_key);
			
			if (is_null($koperasi_id)) {
				return Response::json(['status' => 0, 'message' => 'koperasi id tidak ditemukan']);
			}
		/*---------- checking authority ----------*/
		
		$id = $request->input('id');
		
		$data = Simpanan::find($id)->delete();

        if (is_null($data)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Data berhasil dihapus',
            'session_key' => $session_key, 'data' => $id]);
	}
}