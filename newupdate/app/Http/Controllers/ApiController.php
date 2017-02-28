<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;


use App\Komentarinformasikementerian;
use App\Komentarinformasikoperasi;
use App\Transaksi;
use App\Transaksidetail;
use App\Transaksidetailtemp;
use Illuminate\Http\Request;
use Illuminate\Contracts\Hashing\Hasher;

use Carbon\Carbon;
use App\Http\Requests;

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
//use carbon;


use App\Koperasi;
use App\Anggotakoperasi;
use App\Session;
use App\Provinsi;
use App\Kabupatenkota;
use App\Kecamatan;
use App\Kelurahan;
use App\Infokementerian;
use App\Infokoperasi;
use App\Password_resets;
use App\Trainingkoperasi;
use App\Trainingkementerian;
use App\Adminkoperasi;
use App\Seminarkementerian;
use App\Seminarkoperasi;
use App\Rat;
use App\Kehadiranrat;
use App\Bookingtrainingkoperasi;
use App\Bookingseminarkoperasi;

use App\Bookingtrainingkementerian;
use App\Bookingseminarkementerian;
use App\Tahunoperasi;
use App\Shu;
use App\Produk;
use App\Kategori;


class ApiController extends Controller
{

    private function createOrUpdateSession($anggotakoperasi_id = null)
    {
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
            $session = Session::where(['session_key' => $session_key])->where('expired_at', '>=', Carbon::now())->first();
            if (!is_null($session)) {
                $boolean = true;
                $this->createOrUpdateSession($session->anggotakoperasi_id);
            }
        }
        return $boolean;
    }


    private function getKoperasiId($session_key = null)
    {
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



    /*------------  Get Auth  ----------------*/

    public function auth(Request $request)
    {
        $email = $request->input('email');
        $username = $request->input('username');
        $password = $request->input('password');
        $unik_id = $request->input('unik_id');

        $anggotakoperasi = Anggotakoperasi::where(
            'email', $email)->orWhere('username', $username)->first();

        $cekaktifuser = Session::where(['anggotakoperasi_id' => $anggotakoperasi->id])->first();
        // dd($cekaktifuser->status);


        if (!is_null($anggotakoperasi)) {

            if ($anggotakoperasi->status == 'Blocked') {

                return Response::json(['status' => 0, 'message' => 'Your account is blocked']);
            } else {

                if (Hash::check($password, $anggotakoperasi->password)) {


                    if (is_null($cekaktifuser)) {

                        $session_key = $this->createOrUpdateSession($anggotakoperasi->id);
                        Sessionkoperasi::where(['anggotakoperasi_id' => $anggotakoperasi->id])->update(['unik_id' => $unik_id]);
                        Adminkoperasi::find($anggotakoperasi->id)->update(['logingagal' => '0']);

                        return Response::json(['status' => 1, 'message' => 'adminkoperasi ditemukan', 'statuslogin' => '1', 'session_key' => $session_key, 'data' => $unik_id]);
                    } elseif ($cekaktifuser->status == 1 && $unik_id != $cekaktifuser->unik_id) {
                        return Response::json(['status' => 0, 'statuslogin' => '1', 'message' => 'Akun sedang login pada perangkat lain, silahkan logout']);
                    } elseif ($cekaktifuser->status == 1 && $unik_id == $cekaktifuser->unik_id) {
                        $session_key = $this->createOrUpdateSession($anggotakoperasi->id);

                        $cekaktifuser->update(['unik_id' => $unik_id]);
                        //dd($cekaktifuser);
                        Anggotakoperasi::find($anggotakoperasi->id)->update(['logingagal' => '0']);
                        return Response::json(['status' => 1, 'message' => 'anggotakoperasi ditemukan', 'statuslogin' => '1', 'session_key' => $session_key, 'data' => $anggotakoperasi]);

                    } elseif ($cekaktifuser->status == 0) {

                        $session_key = $this->createOrUpdateSession($anggotakoperasi->id);

                        $cekaktifuser->update(['unik_id' => $unik_id]);
                        //dd($cekaktifuser);
                        Anggotakoperasi::find($anggotakoperasi->id)->update(['logingagal' => '0']);

                        return Response::json(['status' => 1, 'message' => 'anggotakoperasi ditemukan', 'statuslogin' => '1', 'session_key' => $session_key, 'data' => $anggotakoperasi]);
                    }
                } else {

                    if ($anggotakoperasi->logingagal > 4) {
                        Anggotakoperasi::find($anggotakoperasi->id)->update(['status' => 'Blocked']);
                        return Response::json(['status' => 0, 'message' => 'You have 5 times incorrectly entering data,Your account is blocked']);

                    } else {

                        $addgagal = $anggotakoperasi->logingagal + 1;
                        Anggotakoperasi::find($anggotakoperasi->id)->update(['logingagal' => $addgagal]);

                        $countgagal = Anggotakoperasi::find($anggotakoperasi->id);

                        if ($countgagal->logingagal == 5) {
                            return Response::json(['status' => 0, 'message' => 'You have 5 times incorrectly entering data,Your account is blocked', 'countgagal' => $countgagal->logingagal]);
                        } else {
                            return Response::json(['status' => 0, 'message' => 'erorr..email or password salah', 'countgagal' => $countgagal->logingagal]);

                        }
                    }
                }
            }


        } else if (empty($email) || empty($anggotakoperasiname) || empty($password)) {

            return Response::json(['status' => 0, 'message' => 'eror...kosong']);
        } else {

            return Response::json(['status' => 0, 'message' => 'anggotakoperasi tidak ditemukan']);
        }


    }


    /*------ Logout ---------- */

    public function logout(Request $request)
    {

        $session_key = $request->input('session_key');
        if ($this->checkIfSessionExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        Session::where(['session_key' => $session_key])->first()->update(['status' => 0]);
        return Response::json(['status' => 1, 'message' => 'berhasil logout']);

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


        $profile = Anggotakoperasi::with('koperasi')->with('kelurahan.kecamatan.kabupatenkota.provinsi')->where(['anggotakoperasi.id' => $anggotakoperasi_id])->first();

        if (is_null($profile)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Profile ditemukan',
            'session_key' => $session_key, 'data' => $profile]);

    }

    /*------------  Update Profile  ----------------*/
    public function updateprofile(Anggotakoperasi $anggotakoperasi, Request $request)
    {
        $session_key = $request->input('session_key');


        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
        $anggotakoperasi = Anggotakoperasi::findorfail($anggotakoperasi_id);
        $input = $request->all();
        $passwordlama = $request->input('passwordlama');
        $password = $request->input('password');


        if ($passwordlama == '') {

            $anggotakoperasi->password;

        } elseif ($passwordlama != '') {


            if (Hash::check($passwordlama, $anggotakoperasi->password)) {
                if ($password == '') {
                    return Response::json(['status' => 0, 'message' => 'Password baru tdk boleh kosong']);
                } else {

                    $anggotakoperasi->password = Hash::make($request->input('password'));

                }
            } else {

                $anggotakoperasi->password;
                return Response::json(['status' => 0, 'message' => 'Password lama tdk cocok']);

            }
        }

        $input['foto'] = $anggotakoperasi->foto;
        $input['password'] = $anggotakoperasi->password;

        $update = Anggotakoperasi::find($anggotakoperasi_id)->update($input);

        if (!$update) {
            return Response::json(['status' => 0, 'message' => 'Update profile gagal']);
        }
        $updated = Anggotakoperasi::find($anggotakoperasi_id);
        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $updated]);
    }


    /*------------  Update image  ----------------*/
    public function updateimage(Anggotakoperasi $anggotakoperasi, Request $request)
    {
        $session_key = $request->input('session_key');

        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
        $anggotakoperasi = Anggotakoperasi::find($anggotakoperasi_id);

        $image = Input::file('foto');

        if (!is_null($image)) {

            if (file_exists(public_path('images/anggotakoperasi/' . $anggotakoperasi->foto))) {

                File::delete(public_path('images/anggotakoperasi/' . $anggotakoperasi->foto));
                File::delete(public_path('images/anggotakoperasi/thumb_' . $anggotakoperasi->foto));
            }


            $filename = date('YmdHis') . '.' . $image->getClientOriginalExtension();
            $filename_thumb = 'thumb_' . date('YmdHis') . '.' . $image->getClientOriginalExtension();

            $path = public_path('images/anggotakoperasi/' . $filename);
            $path_thumb = public_path('images/anggotakoperasi/' . $filename_thumb);

            Image::make($image->getRealPath())->resize(250, 250)->save($path);
            Image::make($image->getRealPath())->resize(90, 90)->save($path_thumb);
            $anggotakoperasi->foto = $filename;

        }

        $input['foto'] = $anggotakoperasi->foto;
        $update = Anggotakoperasi::find($anggotakoperasi_id)->update($input);


        if (!$update) {
            return Response::json(['status' => 0, 'message' => 'Update profile gagal']);
        }
        $updated = Anggotakoperasi::find($anggotakoperasi_id);
        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $updated]);
    }


    /*------------  forgotpassword  ----------------*/
    public function forgotpassword(Password_resets $password_resets, Request $request)
    {
        $input['email'] = $request->input('email');
        $input['token'] = str_random(64);
        $input['expired_at'] = Carbon::now()->addMinutes(10);

        $findtoken = Password_resets::where(['email' => $input['email'], 'status' => 'New'])->where('expired_at', '>=', Carbon::now())->first();

        if (!is_null($findtoken)) {
            return Response::json(['status' => 0, 'message' => 'Anda sudah merequest password reset, tunggu 10 mnt']);
        }


        //dd($idlastinsert);

        $findemail = Anggotakoperasi::where(['email' => $input['email']])->first();
        if (is_null($findemail)) {
            return Response::json(['status' => 0, 'message' => 'User tdk ditemukan']);
        }
        $password_resets->create($input);
        $idlastinsert = DB::getPdo()->lastInsertId();

        $ambiltoken = Password_resets::findorfail($idlastinsert);

        $data = [
            'nama' => $findemail->nama,
            'email' => $findemail->email,
            'token' => $ambiltoken->token
        ];

        $sentmail = Mail::send('sentmail.maill', $data, function ($message) {
            $message->to(Input::get('email'))->subject('Lupa Password::Koperasi Modern');
        });

        if (!$sentmail) {
            return Response::json(['status' => 0, 'message' => 'eror']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses',
        ]);

    }


    /*------------  Reset Pssword  ----------------*/
    public function resetpassword(Anggotakoperasi $anggotakoperasi, Request $request)
    {
        $email = $request->input('email');

        $token = $request->input('token');

        $findtoken = Password_resets::where(['email' => $email, 'token' => $token, 'status' => 'New'])->where('expired_at', '>=', Carbon::now())->first();

        if (is_null($findtoken)) {
            return Response::json(['status' => 0, 'message' => 'Request tdk ditemukan atau token expired']);
        }


        $newpass = Hash::make($request->input('password'));

        $update = Anggotakoperasi::where(['email' => $findtoken->email])->update(['password' => $newpass]);

        if (!$update) {
            return Response::json(['status' => 0, 'message' => 'Update password  gagal']);
        }

        $findtoken->update(['status' => 'Success']);

        return Response::json(['status' => 1, 'message' => 'sukses update password']);
    }



    /*------------  Get Provinsi  ----------------*/
    public function getprovinsi(Request $request)
    {
        /*$session_key = $request->input('session_key');
        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)){
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        } */

        $provinsi = Provinsi::orderby('nama','asc')->get();

        if (is_null($provinsi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $provinsi]);

    }


    /*------------  Get Kabupatenkota  ----------------*/
    public function getkabupatenkota(Request $request)
    {
        //$session_key = $request->input('session_key');
        $provinsi_id = $request->input('provinsi_id');

        /* $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
         if (is_null($anggotakoperasi_id)){
             return Response::json(['status' => 0, 'message' => 'session key not found']);
         } */

        $kabupatenkota = Kabupatenkota::where('provinsi_id', $provinsi_id)->orderby('nama','asc')->get();

        if (is_null($kabupatenkota)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $kabupatenkota]);

    }


    /*------------  Get kecamatan  ----------------*/
    public function getkecamatan(Request $request)
    {
        $kabupatenkota_id = $request->input('kabupatenkota_id');

        $kabupatenkota = Kecamatan::where('kabupatenkota_id', $kabupatenkota_id)->orderby('nama','asc')->get();

        if (is_null($kabupatenkota)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $kabupatenkota]);

    }


    /*------------  Get kelurahan  ----------------*/
    public function getkelurahan(Request $request)
    {
        $kecamatan_id = $request->input('kecamatan_id');
        $kelurahan = Kelurahan::where('kecamatan_id', $kecamatan_id)->orderby('nama','asc')->get(['id', 'kecamatan_id', 'nama']);

        if (is_null($kelurahan)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $kelurahan]);

    }


    /*------------  Get alamat  ----------------*/
    public function getalamat(Request $request)
    {
        $session_key = $request->input('session_key');
        $kelurahan_id = $request->input('kelurahan_id');

        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $kelurahan = Kelurahan::where('id', $kelurahan_id)->with('kecamatan.kabupatenkota.provinsi')->get();

        if (is_null($kelurahan)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $kelurahan]);

    }

//$desa = \App\Desa::where('nama', 'like', '%sukamakmur%')->with('kecamatan.kabupaten.provinsi')->get();


    /*------------  getinfokementriann  ----------------*/
    public function getinfokementerian(Request $request)
    {
        $session_key = $request->input('session_key');
        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }


        $kementerian = Infokementerian::orderBy('id', 'desc')->get();

        if (is_null($kementerian)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $kementerian]);

    }


    /*------------  get info koperasi  ----------------*/
    public function getinfokoperasi(Request $request)
    {
        $session_key = $request->input('session_key');
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $infokoperasi = Infokoperasi::where(['koperasi_id' => $koperasi_id])->orderBy('id', 'desc')->get();


        if (is_null($infokoperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $infokoperasi]);

    }


    /*------------  get Training koperasi ----------------*/
    public function gettrainingkoperasi(Request $request)
    {
        $session_key = $request->input('session_key');
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $training = Trainingkoperasi::where(['koperasi_id' => $koperasi_id])->orderBy('id', 'desc')->get();


        if (is_null($training)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $training]);

    }


    /*------------  get Training kementrian ----------------*/
    public function gettrainingkementerian(Request $request)
    {
        $session_key = $request->input('session_key');
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $training = Trainingkementerian::orderBy('id', 'desc')->get();


        if (is_null($training)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $training]);

    }

    /*------------  get seminar kementerian ----------------*/


    public function getseminarkementerian(Request $request)
    {
        $session_key = $request->input('session_key');
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $seminar = Seminarkementerian::orderBy('id', 'desc')->get();


        if (is_null($seminar)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $seminar]);

    }


    /*------------  get seminar koperasi ----------------*/
    public function getseminarkoperasi(Request $request)
    {
        $session_key = $request->input('session_key');
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $seminar = Seminarkoperasi::where(['koperasi_id' => $koperasi_id])->orderBy('id', 'desc')->get();

        if (is_null($seminar)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $seminar]);

    }


    /*------------  get RAT ----------------*/
    public function getrat(Request $request)
    {
        $session_key = $request->input('session_key');
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $rat = Rat::where(['koperasi_id' => $koperasi_id])->orderBy('id', 'desc')->get();

        if (is_null($rat)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $rat]);

    }


    /*------------  Kehadiran  RAT ----------------*/
    public function insertkehadiranrat(Request $request)
    {
        $session_key = $request->input('session_key');


        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }


        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $input = $request->all();
        $input['kehadiran'] = $request->input('kehadiran');
        $input['koperasi_id'] = $koperasi_id;
        $input['anggotakoperasi_id'] = $anggotakoperasi_id;


        try {

            $insert = Kehadiranrat::create($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'duplicate entry']);
        }

        if (!($insert)) {
            return Response::json(['status' => 0, 'message' => 'insert gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses..data ditemukan',
            'data' => $insert]);

    }


    /*------------  bookingtrainingkoperasi ----------------*/
    public function insertbookingtrainingkoperasi(Request $request)
    {
        $session_key = $request->input('session_key');
        $input['trainingkoperasi_id'] = $request->input('trainingkoperasi_id');

        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }


        $input['anggotakoperasi_id'] = $anggotakoperasi_id;


        try {
            $insert = Bookingtrainingkoperasi::create($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'duplicate entry']);
        }

        if (!($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $insert]);
    }



    /*------------  bookingseminarkoperasi ----------------*/
    public function insertbookingseminarkoperasi(Request $request)
    {
        $session_key = $request->input('session_key');
        $input['seminarkoperasi_id'] = $request->input('seminarkoperasi_id');

        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }



        $input['anggotakoperasi_id'] = $anggotakoperasi_id;


        try {
            $insert = Bookingseminarkoperasi::create($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'duplicate entry']);
        }

        if (!($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $insert]);
    }




    /*------------  bookingtrainingkementerian----------------*/
    public function insertbookingtrainingkementerian(Request $request)
    {
        $session_key = $request->input('session_key');
        $input['trainingkementerian_id'] = $request->input('trainingkementerian_id');

        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $input['anggotakoperasi_id'] = $anggotakoperasi_id;
        $input['koperasi_id'] = $koperasi_id;

        try {
            $insert = Bookingtrainingkementerian::create($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'duplicate entry']);
        }

        if (!($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $insert]);
    }



    /*------------  bookingseminarkoperasi ----------------*/
    public function insertbookingseminarkementerian(Request $request)
    {
        $session_key = $request->input('session_key');
        $input['seminarkementerian_id'] = $request->input('seminarkementerian_id');

        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $input['anggotakoperasi_id'] = $anggotakoperasi_id;
        $input['koperasi_id'] = $koperasi_id;

        try {
            $insert = Bookingseminarkementerian::create($input);
        }catch(\Exception $e){
            return Response::json(['status' => 0, 'message' => 'duplicate entry']);
        }

        if (!($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $insert]);
    }




    /*------------  insert komentar info koperasi ----------------*/
    public function insertkomentarinfokoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $input = $request->all();
        $input['anggotakoperasi_id'] = $anggotakoperasi_id;

        $insert = Komentarinformasikoperasi::create($input);
        $idlastinsert= DB::getPdo()->lastInsertId();



        if (!$insert) {
            return Response::json(['status' => 0, 'message' => 'gagal']);
        }

        $komentar= Komentarinformasikoperasi::with(['anggotakoperasi'])->find($idlastinsert);

        return Response::json(['status' => 1, 'message' => 'berhasil',
            'data' => $komentar]);
    }




    /*------------ get info koperasi detail ----------------*/
    public function getinfokoperasidetail(Request $request)
    {
        $session_key = $request->input('session_key');
        $infokoperasi_id = $request->input('infokoperasi_id');

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $infokoperasi = Infokoperasi::with(['komentarinformasikoperasi.anggotakoperasi'])->where('infokoperasi.id',$infokoperasi_id)->first();


        if (is_null($infokoperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $infokoperasi]);

    }


    /*------------  insert komentar info koperasi ----------------*/
    public function insertkomentarinfokementerian(Request $request)
    {
        $session_key = $request->input('session_key');

        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $input = $request->all();
        $input['anggotakoperasi_id'] = $anggotakoperasi_id;
        $insert = Komentarinformasikementerian::create($input);
        $idlastinsert= DB::getPdo()->lastInsertId();

        if (!$insert) {
            return Response::json(['status' => 0, 'message' => 'gagal']);
        }

        $komentar= Komentarinformasikementerian::with('anggotakoperasi')->find($idlastinsert);

        return Response::json(['status' => 1, 'message' => 'berhasil',
            'data' => $komentar]);
    }




    /*------------ get info koperasi detail ----------------*/
    public function getinfokementeriandetail(Request $request)
    {
        $session_key = $request->input('session_key');
        $infokementerian_id = $request->input('infokementerian_id');

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $infokoperasi = Infokementerian::with(['komentarinformasikementerian.anggotakoperasi.koperasi'])->where('infokementerian.id',$infokementerian_id)->first();


        if (is_null($infokoperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $infokoperasi]);

    }




    /*------------ get info koperasi detail ----------------*/
    public function tes(Request $request)
    {

        $count = Anggotakoperasi::count();
        $skip = 2;
        $limit = 2;


  // $infokoperasi = Anggotakoperasi::get(['id','nama']);

       $infokoperasi = Anggotakoperasi::skip($skip)->take($limit)->get(['id','nama']);


        if (is_null($infokoperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $infokoperasi]);

    }





    /*------------ getshu ----------------*/
    public function getshu(Request $request)
    {
        $session_key = $request->input('session_key');

        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }


        $tahunoperasiaktif  =Tahunoperasi::where(['koperasi_id'=>$koperasi_id,'status'=>'Aktif'])->first();

        $myshu=Shu::where(['koperasi_id'=>$koperasi_id,'tahunoperasi_id'=>$tahunoperasiaktif->id,'anggotakoperasi_id'=>$anggotakoperasi_id])->first();


        if (is_null($myshu)) {
            return Response::json(['status' => 0, 'message' => 'SHU belum dikalkulasi']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $myshu]);

    }


/*------------  Get kategori  ----------------*/
    public function getkategori(Request $request)
    {
        $session_key = $request->input('session_key');

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $kategori= Kategori::where('koperasi_id', $koperasi_id)->orderby('id','desc')->get();

        if (is_null($kategori)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $kategori]);

    }


/*------------  Get myproduk  ----------------*/
    public function getmyproduk(Request $request)
    {
        $session_key = $request->input('session_key');
        $kategori_id= $request->input('kategori_id');

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $produk= Kategori::with('produk')->where(['koperasi_id'=> $koperasi_id,'id'=>$kategori_id])->orderby('id','desc')->get();

        if (is_null($produk)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $produk]);

    }



    //------------------insert transaksi ------------------------

    public function inserttransaksi(Request $request)
    {
        $session_key = $request->input('session_key');

        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $tahunoperasiaktif  =Tahunoperasi::where(['koperasi_id'=>$koperasi_id,'status'=>'Aktif'])->first();

        $input['koperasi_id'] = $koperasi_id;
        $input['anggotakoperasi_id'] = $anggotakoperasi_id;
        $input['tahunoperasi_id'] = $tahunoperasiaktif->id;
        $input['tanggal'] = Carbon::now();
        //dd($input);

        $insert = Transaksi::create($input);
        $insert['idlastinsert']= DB::getPdo()->lastInsertId();



        if (!$insert) {
            return Response::json(['status' => 0, 'message' => 'gagal']);
        }

        //$komentar= Komentarinformasikoperasi::with(['anggotakoperasi'])->find($idlastinsert);

        return Response::json(['status' => 1, 'message' => 'berhasil',
            'data' => $insert]);
    }



    /*------------  getmydetailtemp   ----------------*/
    public function getmydetailtemp(Request $request)
    {
        $session_key = $request->input('session_key');
        $transaksi_id = $request->input('transaksi_id');

        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $transaksi=Transaksidetailtemp::with('produk')->where('transaksi_id',$transaksi_id)->get();

        if (is_null($transaksi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $transaksi]);

    }



    //------------------insert transaksi detail temp ------------------------

    public function insertdetailtemp(Request $request)
    {
        $session_key = $request->input('session_key');

        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $input['produk_id'] = $request->input('produk_id');
        $input['kuantitas'] = $request->input('kuantitas');

        $produk=Produk::find($input['produk_id']);

        $cekstok=$produk->stok;
        $stokdikurangiQ= $cekstok-$input['kuantitas'];


        if ($cekstok <=0 ) {
            return Response::json(['status' => 0, 'message' => 'Stok Produk Habis']);
        }
        if ($stokdikurangiQ < 0){

            return Response::json(['status' => 0, 'message' => 'Jumlah produk yang diminta melebihi stok, sisa stok yang tersedia adalah '.$cekstok]);
        }



        $input['tanggal'] = Carbon::now();
        $input['transaksi_id'] = $request->input('transaksi_id');

       // $kuantitas=$input['kuantitas'];


        //$input['hargabeli']=$produk->hargabeli;
        $input['hargajual']=$produk->hargajual;
        //$input['subtotalhargabeli']=$input['kuantitas']*$produk->hargabeli;
        $input['subtotalhargajual']=$input['kuantitas']*$produk->hargajual;
        //   dd($input);

        $caritransaksi=Transaksi::find($input['transaksi_id']);

        if(is_null($caritransaksi))
        {
            return Response::json(['status' => 0, 'message' => 'transaksi tidak ditemukan']);

        }


        else {

            $caridetail = Transaksidetailtemp::where(['transaksi_id' => $input['transaksi_id'], 'produk_id' => $input['produk_id']])->first();
            //dd($caridetail);

            if ($caridetail=='') {
                DB::select('call sp_kurangiStok(?,?)', [$input['produk_id'],$input['kuantitas']]);
                $insertupdate = Transaksidetailtemp::create($input);


                if (!$insertupdate) {
                    return Response::json(['status' => 0, 'message' => 'gagal']);
                }
                return Response::json(['status' => 1, 'message' => 'berhasil',
                    'data' => $insertupdate]);

            } elseif (!is_null($caridetail)) {


                // $transaksidetail_id=$input['transaksidetail_id'];

                $insertupdate = Transaksidetailtemp::where(['transaksi_id' => $input['transaksi_id'], 'produk_id' => $input['produk_id']])->first();



                if ($insertupdate->kuantitas == $input['kuantitas']) {

                    return Response::json(['status' => 0, 'message' => 'Kuantitas Sama']);

                    //$input['kuantitas'] = $request->input('kuantitas');
                } elseif ($insertupdate->kuantitas < $input['kuantitas']) {
                    $newkuantitas = $input['kuantitas'] - $insertupdate->kuantitas;

                    $sp = DB::select('call sp_kurangiStok(?,?)', [$input['produk_id'], $newkuantitas]);

                    //$input['kuantitas']
                } elseif ($insertupdate->kuantitas > $input['kuantitas']) {
                    $newkuantitas = $insertupdate->kuantitas - $input['kuantitas'];

                    $sp = DB::select('call sp_tambahStok(?,?)', [$input['produk_id'], $newkuantitas]);

                }

                if (!$sp) {

                    return Response::json(['status' => 0, 'message' => 'sp eror']);
                }

                $insertupdate->update($input);

                if (!$insertupdate) {
                    return Response::json(['status' => 0, 'message' => 'gagal']);
                }


            }
        }





        //$komentar= Komentarinformasikoperasi::with(['anggotakoperasi'])->find($idlastinsert);

        return Response::json(['status' => 1, 'message' => 'berhasil',
            'data' => $insertupdate]);
    }


    /*------------  editdetailtemp  ----------------*/
    public function editdetailtemp (Request $request)
    {
        $session_key = $request->input('session_key');
        $transaksidetail_id = $request->input('transaksidetail_id');

        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $transaksi=Transaksidetailtemp::find($transaksidetail_id);

        if (is_null($transaksi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $transaksi]);

    }



    //------------------update transaksi detail ------------------------

    public function updatedetailtemp(Request $request)
    {
        $session_key = $request->input('session_key');


        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $transaksidetail_id = $request->input('transaksidetail_id');
        $input['tanggal'] = Carbon::now();

        $input['produk_id'] = $request->input('produk_id');
        $input['kuantitas'] = $request->input('kuantitas');

        $produk=Produk::find($input['produk_id']);

        $cekstok=$produk->stok;
        $stokdikurangiQ= $cekstok-$input['kuantitas'];


        if ($cekstok <=0 ) {
            return Response::json(['status' => 0, 'message' => 'Stok Produk Habis']);
        }
        if ($stokdikurangiQ < 0){

            return Response::json(['status' => 0, 'message' => 'Jumlah produk yang diminta melebihi stok, sisa stok yang tersedia adalah '.$cekstok]);
        }



        $kuantitas=$input['kuantitas'];

       // $input['hargabeli']=$produk->hargabeli;
        $input['hargajual']=$produk->hargajual;
       // $input['subtotalhargabeli']=$kuantitas*$produk->hargabeli;
        $input['subtotalhargajual']=$kuantitas*$produk->hargajual;
        //   dd($input);

        $update = Transaksidetailtemp::find($transaksidetail_id);

        if ($update->kuantitas==$kuantitas)

        {

            //$input['kuantitas'] = $request->input('kuantitas');
        }
        elseif($update->kuantitas < $kuantitas)
        {
            $newkuantitas=$kuantitas-$update->kuantitas;

           $sp=DB::select('call sp_kurangiStok(?,?)',[$input['produk_id'],$newkuantitas ]);

            //$input['kuantitas']
        }

        elseif($update->kuantitas > $kuantitas)
        {
            $newkuantitas=$update->kuantitas-$kuantitas;

            $sp=DB::select('call sp_tambahStok(?,?)',[$input['produk_id'],$newkuantitas ]);

        }

        if (!$sp){

            return Response::json(['status' => 0, 'message' => 'sp eror']);
        }

           $update ->update($input);

        if (!$update) {
            return Response::json(['status' => 0, 'message' => 'gagal']);
        }


        return Response::json(['status' => 1, 'message' => 'berhasil',
            'data' => $update]);
    }




    /*------------   delete temp----------------*/
    public function deletedetailtemp(Request $request)
    {
        $session_key = $request->input('session_key');


        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $transaksidetail_id = $request->input('transaksidetail_id');


        $transdet = Transaksidetailtemp::find($transaksidetail_id);
        if (is_null($transdet)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        DB::select('call sp_tambahStok(?,?)',[$transdet->produk_id,$transdet->kuantitas]);

        if (is_null($transdet)) {
            return Response::json(['status' => 0, 'message' => 'data tdk ditemukan']);
        }

        $transdet->delete();

        return Response::json(['status' => 1, 'message' => 'data berhasil di hapus',
            'session_key' => $session_key]);

    }



/*------------  checkout ----------------*/
    public function checkout(Request $request)
    {
        $session_key = $request->input('session_key');
        $transaksi_id= $request->input('transaksi_id');

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

      //  sp_moveTransaksiDetail

        DB::select('call sp_moveTransaksiDetail(?)',[$transaksi_id]);

       // $pindahkerealtransdet = Transaksidetail::create($input);

        $trans=Transaksidetail::where('transaksi_id',$transaksi_id)->get();

        $input['totalhargajual']=$trans->sum('subtotalhargajual');
        $input['jumlah']=$input['totalhargajual'];
        //$input['totalhargabeli']=$trans->sum('subtotalhargabeli');

        $updatetrans= Transaksi::find($transaksi_id)->update($input);

        $generatefaktur=DB::select('call sp_updateNomorFaktur(?)',[$transaksi_id]);


        if (is_null($updatetrans)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }
        if (is_null($generatefaktur)) {
            return Response::json(['status' => 0, 'message' => 'generate eror']);
        }

        $updated= Transaksi::find($transaksi_id);

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $updated]);

    }


    /*------------   ----------------*/
    public function canceltrans(Request $request)
    {
        $session_key = $request->input('session_key');
        $transaksi_id = $request->input('transaksi_id');

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);

        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $transaksi=Transaksi::find($transaksi_id);
        if (is_null($transaksi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        $transaksi->update(['status'=>'Kadaluarsa']);


        if (!$transaksi) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }
       // $deletetemp= Transaksidetailtemp::where('transaksi_id',$transaksi_id)->delete();
        $deletetrans=Transaksi::find($transaksi_id)->delete();


        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key,'deletetrans'=>$deletetrans]);

    }




    /*------------  cek trans  ----------------*/
    public function cektrans(Request $request)
    {
        $session_key = $request->input('session_key');
        $transaksi_id = $request->input('transaksi_id');

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }


        $transaksi=Transaksi::find($transaksi_id);

        if (is_null($transaksi)) {
            return Response::json(['status' => 1, 'message' => 'data tidak ditemukan']);
        }

        if($transaksi->status=='Kadaluarsa')
        {
            return Response::json(['status' => 1, 'message' => 'transaksi sudah kadaluarsa']);
        }

        elseif($transaksi->status=='Baru'){

            return Response::json(['status' => 0, 'message' => 'status transaksi baru']);
        }

        elseif($transaksi->status=='Dibayar'){

            return Response::json(['status' => 0, 'message' => 'status transaksi sudah dibayar']);
        }

        else {

            return Response::json(['status' => 0, 'message' => 'mmmmm server bingung']);
        }




    }




    /*------------  Get my transaksi  ----------------*/
    public function getmytransaksi(Request $request)
    {
        $session_key = $request->input('session_key');

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);

        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $transaksi=Transaksi::where(['koperasi_id'=>$koperasi_id,'anggotakoperasi_id'=>$anggotakoperasi_id])->orderby('id','desc')->get();


        if (is_null($transaksi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $transaksi]);

    }



    /*------------  Get my transaksi detail  ----------------*/
    public function getmytransaksidetail(Request $request)
    {
        $session_key = $request->input('session_key');
        $transaksi_id = $request->input('transaksi_id');

        $anggotakoperasi_id = $this->getAngggotakoperasiId($session_key);

        if (is_null($anggotakoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $transaksi=Transaksidetail::where('transaksi_id',$transaksi_id)->orderby('id','desc')->get();


        if (is_null($transaksi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $transaksi]);

    }












}
