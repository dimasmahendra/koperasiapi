<?php

namespace App\Http\Controllers;


use App\Adminkoperasi;
use App\Anggotakoperasi;
use App\Koperasi;
use App\Pesankementeriankoperasi;
use App\Skalakoperasi;
use App\Tipekoperasi;
use Illuminate\Http\Request;
use Illuminate\Contracts\Hashing\Hasher;
use Carbon\Carbon;
use App\Http\Requests;



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
//use table;




class Apiv3Controller extends Controller
{

    private function createOrUpdateSessionkementerian($adminkementerian_id = null)
    {
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


    private function getAdminkementerianId($session_key = null)
    {
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


    private function checkIfSessionKementerianExpired($session_key = null)
    {
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



    /*------------  Login admin  ----------------*/
    public function loginadmin(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');
        $ip_address = $request->input('ip_address');

        $adminkementerian = Adminkementerian::where(
            'username', $username)->first();

        if (is_null($adminkementerian)) {

            return Response::json(['status' => 0, 'message' => 'Inputan Salah']);
        }


        $cekaktifuser = Sessionkementerian::where(['adminkementerian_id' => $adminkementerian->id])->first();

        if (!is_null($adminkementerian)) {

            if ($adminkementerian->status == 'Blocked') {

                return Response::json(['status' => 0, 'message' => 'Your account is blocked']);
            } else {

                if (Hash::check($password, $adminkementerian->password)) {

                    if (is_null($cekaktifuser)) {

                        $session_key = $this->createOrUpdateSessionkementerian($adminkementerian->id);
                        $recallcekaktifuser = Sessionkementerian::where(['adminkementerian_id' => $adminkementerian->id])->update(['ip_address' => $ip_address]);
                        Adminkementerian::find($adminkementerian->id)->update(['logingagal' => '0']);

                        return Response::json(['status' => 1, 'message' => 'adminkementerian ditemukan', 'statuslogin' => '1', 'session_key' => $session_key, 'data' => $adminkementerian]);
                    } elseif ($cekaktifuser->status == 1 && $ip_address != $cekaktifuser->ip_address) {
                        $cekexpired = Sessionkementerian::where(['adminkementerian_id' => $adminkementerian->id])->where('expired_at', '>=', Carbon::now())->first();
                        if (!is_null($cekexpired)) {
                            return Response::json(['status' => 0, 'statuslogin' => '1', 'message' => 'lg ada yg onlen coy']);
                        } else {
                            $session_key = $this->createOrUpdateSessionkementerian($adminkementerian->id);
                            $recallcekaktifuser = Sessionkementerian::where(['adminkementerian_id' => $adminkementerian->id])->update(['ip_address' => $ip_address]);
                            Adminkementerian::find($adminkementerian->id)->update(['logingagal' => '0']);
                            return Response::json(['status' => 1, 'message' => 'adminkementerian ditemukan', 'statuslogin' => '1', 'session_key' => $session_key, 'data' => $adminkementerian]);
                        }


                    } elseif ($cekaktifuser->status == 1 && $ip_address == $cekaktifuser->ip_address) {
                        $session_key = $this->createOrUpdateSessionkementerian($adminkementerian->id);

                        $cekaktifuser->update(['ip_address' => $ip_address]);

                        Adminkementerian::find($adminkementerian->id)->update(['logingagal' => '0']);
                        return Response::json(['status' => 1, 'message' => 'adminkementerian ditemukan', 'statuslogin' => '1', 'session_key' => $session_key, 'data' => $adminkementerian]);

                    } elseif ($cekaktifuser->status == 0) {

                        $session_key = $this->createOrUpdateSessionkementerian($adminkementerian->id);

                        $cekaktifuser->update(['ip_address' => $ip_address]);
                        //dd($cekaktifuser);
                        Adminkementerian::find($adminkementerian->id)->update(['logingagal' => '0']);

                        return Response::json(['status' => 1, 'message' => 'adminkementerian ditemukan', 'statuslogin' => '1', 'session_key' => $session_key, 'data' => $adminkementerian]);
                    }

                } else {

                    if ($adminkementerian->logingagal > 4) {
                        Adminkementerian::find($adminkementerian->id)->update(['status' => 'Blocked']);
                        return Response::json(['status' => 0, 'message' => 'You have 5 times incorrectly entering data,Your account is blocked']);

                    } else {

                        $addgagal = $adminkementerian->logingagal + 1;
                        Adminkementerian::find($adminkementerian->id)->update(['logingagal' => $addgagal]);

                        $countgagal = Adminkementerian::find($adminkementerian->id);

                        if ($countgagal->logingagal == 5) {
                            return Response::json(['status' => 0, 'message' => 'You have 5 times incorrectly entering data,Your account is blocked', 'countgagal' => $countgagal->logingagal]);
                        } else {
                            return Response::json(['status' => 0, 'message' => 'erorr..email or password salah', 'countgagal' => $countgagal->logingagal]);

                        }
                    }
                }
            }


        } else if (empty($username) || empty($password)) {
            return Response::json(['status' => 0, 'message' => 'eror...kosong']);
        } else {
            return Response::json(['status' => 0, 'message' => 'adminkementerian tidak ditemukan']);
        }


    }


    /*------ Logoutkementerian ---------- */

    public function logout(Request $request)
    {

        $session_key = $request->input('session_key');
        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        Sessionkementerian::where(['session_key' => $session_key])->first()->update(['status' => 0]);
        return Response::json(['status' => 1, 'message' => 'berhasil logout']);

    }


    /*------------  Get Profile  ----------------*/
    public function getprofile(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }


        $profile = Adminkementerian::where(['id' => $adminkementerian_id])->first();

        if (is_null($profile)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Profile ditemukan',
            'session_key' => $session_key, 'data' => $profile]);

    }

    /*------------  Update Profile  ----------------*/
    public function updateprofile(Adminkementerian $adminkementerian, Request $request)
    {
        $session_key = $request->input('session_key');


        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
        $adminkementerian = Adminkementerian::findorfail($adminkementerian_id);
        $input = $request->all();
        $passwordlama = $request->input('passwordlama');
        $password = $request->input('password');


        $image = Input::file('foto');

        if (!is_null($image)) {

            if (file_exists(public_path('images/adminkementerian/' . $adminkementerian->foto))) {

                File::delete(public_path('images/adminkementerian/' . $adminkementerian->foto));
                File::delete(public_path('images/adminkementerian/thumb_' . $adminkementerian->foto));
            }


            $filename = date('YmdHis') . '.' . $image->getClientOriginalExtension();
            $filename_thumb = 'thumb_' . date('YmdHis') . '.' . $image->getClientOriginalExtension();

            $path = public_path('images/adminkementerian/' . $filename);
            $path_thumb = public_path('images/adminkementerian/' . $filename_thumb);

            Image::make($image->getRealPath())->resize(250, 250)->save($path);
            Image::make($image->getRealPath())->resize(90, 90)->save($path_thumb);

            $adminkementerian->foto = $filename;

        }



        if ($passwordlama == '') {

            $adminkementerian->password;

        } elseif ($passwordlama != '') {


            if (Hash::check($passwordlama, $adminkementerian->password)) {
                if ($password == '') {
                    return Response::json(['status' => 0, 'message' => 'Password baru tdk boleh kosong']);
                } else {

                    $adminkementerian->password = Hash::make($request->input('password'));

                }
            } else {

                $adminkementerian->password;
                return Response::json(['status' => 0, 'message' => 'Password lama tdk cocok']);

            }
        }

        $input['foto'] = $adminkementerian->foto;
        $input['password'] = $adminkementerian->password;

        $update = Adminkementerian::find($adminkementerian_id)->update($input);

        if (!$update) {
            return Response::json(['status' => 0, 'message' => 'Update profile gagal']);
        }
        $updated = Adminkementerian::find($adminkementerian_id);
        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $updated]);
    }



    /*------------  Get akses kementerian  ----------------*/
    public function getakseskementerian(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }


        $akses=DB::select('call sp_getAksesKoperasi()');


        if (is_null($akses)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'akses tersedia ditemukan',
            'session_key' => $session_key, 'data' => $akses]);

    }






    /*------------  Get admin kementerian  ----------------*/
    public function getadminkementerian(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $profile = Adminkementerian::with('akseskementerian')->where('akseskementerian_id','!=','1')->get();

        if (is_null($profile)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Profile ditemukan',
            'session_key' => $session_key, 'data' => $profile]);

    }


    /*------------  Insert admin Kementerian ----------------*/
    public function insertadminkementerian(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $input = $request->all();

        $hash = $request->input('password');
        $input['password'] = Hash::make($hash);

        // dd($input);
        $insert = Adminkementerian::create($input);

        if (!$insert) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'insert berhasil..data ditemukan', 'session_key' => $session_key,
            'data' => $insert]);

    }


    /*------------  Edit Admin Kementerian ----------------*/
    public function editadminkementerian(Request $request)
    {
        $session_key = $request->input('session_key');


        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $request->input('adminkementerian_id');

        $profile = Adminkementerian::where(['id' => $adminkementerian_id])->first();

        if (is_null($profile)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Profile ditemukan',
            'session_key' => $session_key, 'data' => $profile]);

    }


    /*------------  Update admin kementerian  ----------------*/
    public function updateadminkementerian(Adminkementerian $adminkementerian, Request $request)
    {
        $session_key = $request->input('session_key');

        $adminkementerian_id = $request->input('adminkementerian_id');
        $adminkementerian = Adminkementerian::find($adminkementerian_id);

        $input = $request->all();
        $passwordlama = $request->input('passwordlama');
        $password = $request->input('password');


        if ($passwordlama == '') {

            $adminkementerian->password;

        } elseif ($passwordlama != '') {


            if (Hash::check($passwordlama, $adminkementerian->password)) {
                if ($password == '') {
                    return Response::json(['status' => 0, 'message' => 'Password baru tdk boleh kosong']);
                } else {

                    $adminkementerian->password = Hash::make($request->input('password'));

                }
            } else {

                $adminkementerian->password;
                return Response::json(['status' => 0, 'message' => 'Password lama tdk cocok']);

            }
        }

        $input['password'] = $adminkementerian->password;

        $update = Adminkementerian::find($adminkementerian_id)->update($input);

        if (!$update) {
            return Response::json(['status' => 0, 'message' => 'Update profile gagal']);
        }
        $updated = Adminkementerian::find($adminkementerian_id);
        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $updated]);
    }


    /*------------  delete admin kementerian  ----------------*/
    public function deleteadminkementerian(Request $request)
    {
        $session_key = $request->input('session_key');
        $adminkementerian_id = $request->input('adminkementerian_id');


        $adminkementerian = Adminkementerian::find($adminkementerian_id);
        if (is_null($adminkementerian)) {
            return Response::json(['status' => 0, 'message' => 'data tdk ditemukan']);
        }

        $adminkementerian->delete();


        if (is_null($adminkementerian)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data berhasil di hapus',
            'session_key' => $session_key]);

    }





    /*------------  Get Provinsi  ----------------*/
    public function getprovinsi(Request $request)
    {
        $provinsi = Provinsi::get();
        if (is_null($provinsi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }
        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $provinsi]);

    }


    /*------------  Get Kabupatenkota  ----------------*/
    public function getkabupatenkota(Request $request)
    {

        $provinsi_id = $request->input('provinsi_id');
        $kabupatenkota = Kabupatenkota::where('provinsi_id', $provinsi_id)->get();
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
        $kabupatenkota = Kecamatan::where('kabupatenkota_id', $kabupatenkota_id)->get();
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
        $kelurahan = Kelurahan::where('kecamatan_id', $kecamatan_id)->get(['id', 'kecamatan_id', 'nama']);

        if (is_null($kelurahan)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $kelurahan]);

    }



    /*------------  get Koperasi  ----------------*/
    public function getkoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }


        $koperasi = Koperasi::with('kelurahan.kecamatan.kabupatenkota.provinsi')->orderBy('koperasi.id', 'desc')->get();

        if (is_null($koperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan', 'session_key' => $session_key,
            'data' => $koperasi]);

    }



    /*------------  get Koperasi  ----------------*/
    public function getkoperasibykategori(Request $request)
    {
        $session_key = $request->input('session_key');
        $provinsi_id = $request->input('provinsi_id');
        $kabupatenkota_id= $request->input('kabupatenkota_id');
        $skalakoperasi_id= $request->input('skalakoperasi_id');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }


        if ($skalakoperasi_id=='') {

            if ($provinsi_id!=''){

                if($kabupatenkota_id!=''){

                    //tampil semua tapi filter kotakabupaten id

                    $koperasi = Koperasi::with('kelurahan.kecamatan.kabupatenkota.provinsi')

                        ->whereHas('kelurahan.kecamatan.kabupatenkota.provinsi', function($query) use ($kabupatenkota_id) {

                            $query->whereKabupatenkotaId($kabupatenkota_id);
                        })
                        ->orderBy('koperasi.nama', 'asc')

                        ->get();
                }

                else {

                    //tampil semua tapi filter provinsi id

                    $koperasi = Koperasi::with('kelurahan.kecamatan.kabupatenkota.provinsi')

                            ->whereHas('kelurahan.kecamatan.kabupatenkota.provinsi', function($query) use ($provinsi_id) {

                                $query->whereProvinsiId($provinsi_id);
                             })
                    ->orderBy('koperasi.nama', 'asc')

                    ->get();

                }

            }

            else {
                //tampilsemua

                $koperasi = Koperasi::with('kelurahan.kecamatan.kabupatenkota.provinsi')->orderBy('koperasi.nama', 'asc')->get();

            }


        }

        else {

            if ($provinsi_id!=''){

                if($kabupatenkota_id!=''){

                    //tampil semua tapi filter kotakabupaten id + skala

                    $koperasi = Koperasi::with('kelurahan.kecamatan.kabupatenkota.provinsi')->where('koperasi.skalakoperasi_id',$skalakoperasi_id)

                        ->whereHas('kelurahan.kecamatan.kabupatenkota.provinsi', function($query) use ($kabupatenkota_id) {

                            $query->whereKabupatenkotaId($kabupatenkota_id);
                        })

                        ->orderBy('koperasi.nama', 'asc')

                        ->get();

                }

                else {

                    //tampil semua tapi filter provinsi id + skala

                    $koperasi = Koperasi::with('kelurahan.kecamatan.kabupatenkota.provinsi')->where('koperasi.skalakoperasi_id',$skalakoperasi_id)

                        ->whereHas('kelurahan.kecamatan.kabupatenkota.provinsi', function($query) use ($provinsi_id) {

                            $query->whereProvinsiId($provinsi_id);
                        })

                        ->orderBy('koperasi.nama', 'asc')
                        ->get();

                }

            }

            else {
                //tampil semua filter skala

                $koperasi = Koperasi::with('kelurahan.kecamatan.kabupatenkota.provinsi')->where('koperasi.skalakoperasi_id',$skalakoperasi_id)->orderBy('koperasi.nama', 'asc')->get();


            }


        }


        if (is_null($koperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan', 'session_key' => $session_key,
            'data' => $koperasi]);

    }




    /*------------  get Koperasilist  ----------------*/
    public function getkoperasilist(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }


        $koperasi = Koperasi::orderBy('nama', 'asc')->get();

        if (is_null($koperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan', 'session_key' => $session_key,
            'data' => $koperasi]);

    }



    /*------------  get skalakoperasi  ----------------*/
    public function getskalakoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }


        $koperasi = Skalakoperasi::get();

        if (is_null($koperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan', 'session_key' => $session_key,
            'data' => $koperasi]);

    }


    /*------------  get tipekoperasi  ----------------*/
    public function gettipekoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }


        $koperasi = Tipekoperasi::get();

        if (is_null($koperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan', 'session_key' => $session_key,
            'data' => $koperasi]);

    }


    /*------------  Get admin koperasi  ----------------*/
    public function getadminkoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $profile = Adminkoperasi::with(['koperasi','akseskoperasi'])->where('akseskoperasi_id','1')->get();

        if (is_null($profile)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Profile ditemukan',
            'session_key' => $session_key, 'data' => $profile]);

    }



    /*------------  Insert  koperasi ----------------*/
    public function insertkoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $input = $request->all();
        $input['foto'] = 'no_image.png';

        $insert = Koperasi::create($input);

        if (!$insert) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'insert berhasil..data ditemukan', 'session_key' => $session_key,
            'data' => $insert]);

    }


    /*------------  Edit  koperasi ----------------*/
    public function editkoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $request->input('koperasi_id');

        $profile = Koperasi::find($koperasi_id);

        if (is_null($profile)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Profile ditemukan',
            'session_key' => $session_key, 'data' => $profile]);

    }


    /*------------  Update admin koperasi  ----------------*/
    public function updatekoperasi(Koperasi $koperasi, Request $request)
    {
        $session_key = $request->input('session_key');

        $koperasi_id = $request->input('koperasi_id');
        $input = $request->all();

        $update = Koperasi::find($koperasi_id)->update($input);

        if (!$update) {
            return Response::json(['status' => 0, 'message' => 'Update profile gagal']);
        }
      //  $updated = Koperasi::find($koperasi_id);
        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $update]);
    }


    /*------------  delete koperasi  ----------------*/
    public function deletekoperasi(Request $request)
    {
        $session_key = $request->input('session_key');
        $koperasi_id = $request->input('koperasi_id');


        $koperasi = Koperasi::find($koperasi_id);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'data tdk ditemukan']);
        }

        $koperasi->delete();


        if (is_null($koperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data berhasil di hapus',
            'session_key' => $session_key]);

    }



    /*------------  Insert admin koperasi ----------------*/
    public function insertadminkoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $input = $request->all();

        $hash = $request->input('password');
        $input['password'] = Hash::make($hash);
        $input['foto'] = 'no_image.png';
        $input['akseskoperasi_id']=1;

        $insert = Adminkoperasi::create($input);

        if (!$insert) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'insert berhasil..data ditemukan', 'session_key' => $session_key,
            'data' => $insert]);

    }


    /*------------  Edit Admin koperasi ----------------*/
    public function editadminkoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkoperasi_id = $request->input('adminkoperasi_id');

        $profile = Adminkoperasi::where(['id' => $adminkoperasi_id])->first();

        if (is_null($profile)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Profile ditemukan',
            'session_key' => $session_key, 'data' => $profile]);

    }


    /*------------  Update admin koperasi  ----------------*/
    public function updateadminkoperasi(Adminkoperasi $adminkoperasi, Request $request)
    {
        $session_key = $request->input('session_key');

        $adminkoperasi_id = $request->input('adminkoperasi_id');

        $adminkoperasi = Adminkoperasi::find($adminkoperasi_id);

        $input = $request->all();
        $passwordlama = $request->input('passwordlama');
        $password = $request->input('password');


        if ($passwordlama == '') {

            $adminkoperasi->password;

        } elseif ($passwordlama != '') {


            if (Hash::check($passwordlama, $adminkoperasi->password)) {
                if ($password == '') {
                    return Response::json(['status' => 0, 'message' => 'Password baru tdk boleh kosong']);
                } else {

                    $adminkoperasi->password = Hash::make($request->input('password'));

                }
            } else {

                $adminkoperasi->password;
                return Response::json(['status' => 0, 'message' => 'Password lama tdk cocok']);

            }
        }

        $input['password'] = $adminkoperasi->password;

        $update = Adminkoperasi::find($adminkoperasi_id)->update($input);

        if (!$update) {
            return Response::json(['status' => 0, 'message' => 'Update profile gagal']);
        }
        $updated = Adminkoperasi::find($adminkoperasi);
        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $updated]);
    }


    /*------------  delete admin koperasi  ----------------*/
    public function deleteadminkoperasi(Request $request)
    {
        $session_key = $request->input('session_key');
        $adminkoperasi_id = $request->input('adminkoperasi_id');


        $adminkoperasi = Adminkoperasi::find($adminkoperasi_id);
        if (is_null($adminkoperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tdk ditemukan']);
        }

        $adminkoperasi->delete();


        if (is_null($adminkoperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data berhasil di hapus',
            'session_key' => $session_key]);

    }





    /*------------  get pesan kementerian  ----------------*/
    public function getpesankementerian(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }


        $pesan = Pesankementeriankoperasi::with('koperasi.kelurahan.kecamatan.kabupatenkota.provinsi')->orderby('pesankementeriankoperasi.id','desc')->get();

        if (is_null($pesan)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan', 'session_key' => $session_key,
            'data' => $pesan]);

    }






    /*------------  Insert  pesan ----------------*/
    public function insertpesankementerian(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $input = $request->all();

        $insert = Pesankementeriankoperasi::create($input);

        if (!$insert) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'insert berhasil..data ditemukan', 'session_key' => $session_key,
            'data' => $insert]);

    }


    /*------------  Edit  pesan ----------------*/
    public function editpesankementerian(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $pesankementeriankoperasi_id = $request->input('pesankementeriankoperasi_id');

        $pesan = Pesankementeriankoperasi::with('koperasi.kelurahan.kecamatan.kabupatenkota.provinsi')->find($pesankementeriankoperasi_id);


        if (is_null($pesan)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Profile ditemukan',
            'session_key' => $session_key, 'data' => $pesan]);

    }


    /*------------  Update pesan  ----------------*/
    public function updatepesankementerian( Request $request)
    {
        $session_key = $request->input('session_key');

        $pesankementeriankoperasi_id = $request->input('pesankementeriankoperasi_id');

        $input = $request->all();

        $update = Pesankementeriankoperasi::find($pesankementeriankoperasi_id)->update($input);

        if (!$update) {
            return Response::json(['status' => 0, 'message' => 'Update profile gagal']);
        }
        $updated = Adminkoperasi::find($pesankementeriankoperasi_id);
        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $updated]);
    }


    /*------------  delete koperasi  ----------------*/
    public function deletepesankementerian(Request $request)
    {
        $session_key = $request->input('session_key');
        $pesankementeriankoperasi_id = $request->input('pesankementeriankoperasi_id');


        $pesankementeriankoperasi= Pesankementeriankoperasi::find($pesankementeriankoperasi_id);
        if (is_null($pesankementeriankoperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tdk ditemukan']);
        }

        $pesankementeriankoperasi->delete();

        if (is_null($pesankementeriankoperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data berhasil di hapus',
            'session_key' => $session_key]);

    }











    /*------------  get info kementerian  ----------------*/
    public function getinfokementerian(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }



        $infokementerian = Infokementerian::orderBy('id', 'desc')->get();


        if (is_null($infokementerian)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $infokementerian]);

    }



    /*------------  insert info kementerian  ----------------*/
    public function insertinfokementerian(Request $request)
    {
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $input = $request->all();
        $input['adminkementerian_id'] =$adminkementerian_id;

        $foto = Input::file('foto');

        if(!is_null($foto)) {

            $filename = date('YmdHis') . '.' . $foto->getClientOriginalExtension();
            $filename_thumb = 'thumb_'.date('YmdHis') . '.' . $foto->getClientOriginalExtension();

            $path = public_path('images/infokementerian/' . $filename);
            $path_thumb = public_path('images/infokementerian/' . $filename_thumb);

            Image::make($foto->getRealPath())->resize(300, 300)->save($path);
            Image::make($foto->getRealPath())->resize(60, 60)->save($path_thumb);

            $foto->image = $filename;
            $input['foto'] = $foto->image;
        }
        else {

            $input['foto'] ='no_image.png';
        }

        $insert = Infokementerian::create($input);

        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $insert]);

    }



    /*------------  edit info kementerian  ----------------*/
    public function editinfokementerian (Infokementerian $infokementerian,Request $request)
    {
        $session_key = $request->input('session_key');
        $infokementerian_id = $request->input('infokementerian_id');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Infokementerian::findorfail($infokementerian_id);

        if(!$info){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $info]);

    }



    /*------------  Update info kementerian  ----------------*/
    public function updateinfokementerian (Infokementerian $infokementerian,Request $request)
    {
        $session_key = $request->input('session_key');
        $infokementerian_id = $request->input('infokementerian_id');


        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($adminkementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }


        $info=Infokementerian::findorfail($infokementerian_id);
        $input = $request->all();
        $input['adminkementerian_id']=$adminkementerian_id;

        $foto = Input::file('foto');

        if(!is_null($foto)) {

            if(file_exists(public_path('images/infokementerian/'.$info->foto))) {

                File::delete(public_path('images/infokementerian/'.$info->foto));
                File::delete(public_path('images/infokementerian/thumb_'.$info->foto));
            }

            $filename = date('YmdHis') . '.' . $foto->getClientOriginalExtension();
            $filename_thumb = 'thumb_'.date('YmdHis') . '.' . $foto->getClientOriginalExtension();

            $path = public_path('images/infokementerian/' . $filename);
            $path_thumb = public_path('images/infokementerian/' . $filename_thumb);

            Image::make($foto->getRealPath())->resize(300, 300)->save($path);
            Image::make($foto->getRealPath())->resize(60, 60)->save($path_thumb);

            $info->foto = $filename;
        }
        $input['foto'] = $info->foto;

        $update= Infokementerian::find($infokementerian_id)->update($input);

        if(!$update){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $update]);

    }


    /*------------  delete deleteinfokementerian  ----------------*/
    public function deleteinfokementerian (Request $request)
    {
        $session_key = $request->input('session_key');
        $infokementerian_id= $request->input('infokementerian_id');
        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }


        $info=Infokementerian::findorfail($infokementerian_id);

        if(file_exists('public/images/infokementerian/'.$info->foto)) {

            File::delete('public/images/infokementerian/'.$info->foto);
            File::delete('public/images/infokementerian/thumb_'.$info->foto);
        }

        $del= Infokementerian::find($infokementerian_id)->delete();

        if(!$del){
            return Response::json(['status' => 0, 'message' => 'delete gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses delete',
            'session_key' => $session_key, 'data' => $del]);


    }


    /*------------  get Training kementerian ----------------*/
    public function gettrainingkementerian(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $kementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($kementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $training = Trainingkementerian::orderBy('id', 'desc')->get();


        if (is_null($training)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $training]);

    }


    /*------------  insert training kementerian  ----------------*/
    public function inserttrainingkementerian(Request $request)
    {
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $kementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($kementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $input = $request->all();
        $foto = Input::file('foto');

        if(!is_null($foto)) {

            $filename = date('YmdHis') . '.' . $foto->getClientOriginalExtension();
            $filename_thumb = 'thumb_'.date('YmdHis') . '.' . $foto->getClientOriginalExtension();

            $path = public_path('images/trainingkementerian/' . $filename);
            $path_thumb = public_path('images/trainingkementerian/' . $filename_thumb);

            Image::make($foto->getRealPath())->resize(300, 300)->save($path);
            Image::make($foto->getRealPath())->resize(60, 60)->save($path_thumb);

            $foto->image = $filename;
            $input['foto'] = $foto->image;
        }
        else {

            $input['foto'] ='no_image.png';
        }

        $insert = Trainingkementerian::create($input);

        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $insert]);

    }



    /*------------  edit training kementerian  ----------------*/
    public function edittrainingkementerian (Infokementerian $infokementerian,Request $request)
    {
        $session_key = $request->input('session_key');
        $trainingkementerian_id= $request->input('trainingkementerian_id');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Trainingkementerian::findorfail($trainingkementerian_id);

        if(!$info){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $info]);


    }



    /*------------  Update training kementerian  ----------------*/
    public function updatetrainingkementerian (Infokementerian $infokementerian,Request $request)
    {
        $session_key = $request->input('session_key');
        $trainingkementerian_id = $request->input('trainingkementerian_id');
        //dd($infokementerian_id);

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Trainingkementerian::find($trainingkementerian_id);

        $input = $request->all();
        $foto = Input::file('foto');

        if(!is_null($foto)) {

            if(file_exists(public_path('images/trainingkementerian/'.$info->foto))) {

                File::delete(public_path('images/trainingkementerian/'.$info->foto));
                File::delete(public_path('images/trainingkementerian/thumb_'.$info->foto));
            }

            $filename = date('YmdHis') . '.' . $foto->getClientOriginalExtension();
            $filename_thumb = 'thumb_'.date('YmdHis') . '.' . $foto->getClientOriginalExtension();

            $path = public_path('images/trainingkementerian/' . $filename);
            $path_thumb = public_path('images/trainingkementerian/' . $filename_thumb);

            Image::make($foto->getRealPath())->resize(300, 300)->save($path);
            Image::make($foto->getRealPath())->resize(60, 60)->save($path_thumb);

            $info->foto = $filename;
        }
        $input['foto'] = $info->foto;
        $update= Trainingkementerian::find($trainingkementerian_id)->update($input);

        if(!$update){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $update]);
    }


    /*------------  delete deletetrainingkementerian  ----------------*/
    public function deletetrainingkementerian (Request $request)
    {
        $session_key = $request->input('session_key');
        $trainingkementerian_id= $request->input('trainingkementerian_id');
        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Trainingkementerian::findorfail($trainingkementerian_id);
        if(file_exists('public/images/trainingkementerian/'.$info->foto)) {
            File::delete('public/images/trainingkementerian/'.$info->foto);
            File::delete('public/images/trainingkementerian/thumb_'.$info->foto);
        }
        $del= Trainingkementerian::find($trainingkementerian_id)->delete();

        if(!$del){
            return Response::json(['status' => 0, 'message' => 'delete gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses delete',
            'session_key' => $session_key, 'data' => $del]);

    }





    /*------------  get Seminar kementerian ----------------*/
    public function getseminarkementerian(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }



        $seminar= Seminarkementerian::orderby('id','desc')->get();

        if (is_null($seminar)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $seminar]);

    }


    /*------------  insert seminar kementerian  ----------------*/
    public function insertseminarkementerian(Request $request)
    {
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $kementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($kementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $input = $request->all();
        $foto = Input::file('foto');

        if(!is_null($foto)) {

            $filename = date('YmdHis') . '.' . $foto->getClientOriginalExtension();
            $filename_thumb = 'thumb_'.date('YmdHis') . '.' . $foto->getClientOriginalExtension();

            $path = public_path('images/seminarkementerian/' . $filename);
            $path_thumb = public_path('images/seminarkementerian/' . $filename_thumb);

            Image::make($foto->getRealPath())->resize(300, 300)->save($path);
            Image::make($foto->getRealPath())->resize(60, 60)->save($path_thumb);

            $foto->image = $filename;
            $input['foto'] = $foto->image;
        }
        else {

            $input['foto'] ='no_image.png';
        }

        $insert = Seminarkementerian::create($input);

        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $insert]);
    }



    /*------------  edit training kementerian  ----------------*/
    public function editseminarkementerian (Request $request)
    {
        $session_key = $request->input('session_key');
        $seminarkementerian_id = $request->input('seminarkementerian_id');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $info=Seminarkementerian::findorfail($seminarkementerian_id);
        if(!$info){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $info]);


    }



    /*------------  Update training kementerian  ----------------*/
    public function updateseminarkementerian (Request $request)
    {
        $session_key = $request->input('session_key');
        $seminarkementerian_id = $request->input('seminarkementerian_id');
        //dd($infokementerian_id);
        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Seminarkementerian::findorfail($seminarkementerian_id);
        $input = $request->all();
        $foto = Input::file('foto');

        if(!is_null($foto)) {

            if(file_exists(public_path('images/seminarkementerian/'.$info->foto))) {

                File::delete(public_path('images/seminarkementerian/'.$info->foto));
                File::delete(public_path('images/seminarkementerian/thumb_'.$info->foto));
            }

            $filename = date('YmdHis') . '.' . $foto->getClientOriginalExtension();
            $filename_thumb = 'thumb_'.date('YmdHis') . '.' . $foto->getClientOriginalExtension();

            $path = public_path('images/seminarkementerian/' . $filename);
            $path_thumb = public_path('images/seminarkementerian/' . $filename_thumb);

            Image::make($foto->getRealPath())->resize(300, 300)->save($path);
            Image::make($foto->getRealPath())->resize(60, 60)->save($path_thumb);

            $info->foto = $filename;
        }
        $input['foto'] = $info->foto;
        $update= Seminarkementerian::find($seminarkementerian_id)->update($input);

        if(!$update){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $update]);
    }


    /*------------  delete seminar kementerian  ----------------*/
    public function deleteseminarkementerian (Request $request)
    {
        $session_key = $request->input('session_key');
        $seminarkementerian_id= $request->input('seminarkementerian_id');
        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Seminarkementerian::find($seminarkementerian_id);
        if(file_exists('public/images/seminarkementerian/'.$info->foto)) {
            File::delete('public/images/seminarkementerian/'.$info->foto);
            File::delete('public/images/seminarkementerian/thumb_'.$info->foto);
        }
        $del= Seminarkementerian::find($seminarkementerian_id)->delete();

        if(!$del){
            return Response::json(['status' => 0, 'message' => 'delete gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses delete',
            'session_key' => $session_key, 'data' => $del]);
    }


    /*------------  get booking kementerian ----------------*/
    public function getbookingtrainingkementerian(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }


        // $training = Trainingkementerian::with('bookingtrainingkementerian.budget_count')->groupby('trainingkementerian.id')->get();
        $training=DB::table('Trainingkementerian')
            ->leftjoin('bookingtrainingkementerian','bookingtrainingkementerian.trainingkementerian_id','=','trainingkementerian.id')
            ->leftjoin(DB::raw('(SELECT id,trainingkementerian_id,anggotakoperasi_id, count(anggotakoperasi_id) AS jmlbuking FROM bookingtrainingkementerian GROUP BY bookingtrainingkementerian.trainingkementerian_id) as v'),
                'v.trainingkementerian_id','=','trainingkementerian.id')
            ->groupby('trainingkementerian.id')
            ->get(['*']);


        if (is_null($training)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $training]);

    }



    /*------------  get booking kementerian where ----------------*/
    public function getbookingtrainingkementerianwhere (Request $request)
    {
        $session_key = $request->input('session_key');
        $trainingkementerian_id = $request->input('trainingkementerian_id');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $training=DB::table('bookingtrainingkementerian')
            ->leftjoin('anggotakoperasi','anggotakoperasi.id','=','bookingtrainingkementerian.anggotakoperasi_id')
            ->select('bookingtrainingkementerian.*','anggotakoperasi.nama','anggotakoperasi.email','anggotakoperasi.telepon','anggotakoperasi.koperasi_id')
            ->where('trainingkementerian_id',$trainingkementerian_id)
            ->orderby('bookingtrainingkementerian.id','desc')

            ->get();


        if (is_null($training)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $training]);

    }




    /*------------  get seminar kementerian ----------------*/
    public function getbookingseminarkementerian(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $kementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($kementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $semiar=DB::table('seminarkementerian')
            ->leftjoin('bookingseminarkementerian','bookingseminarkementerian.seminarkementerian_id','=','seminarkementerian.id')
            ->leftjoin(DB::raw('(SELECT id,seminarkementerian_id,anggotakoperasi_id, count(anggotakoperasi_id) AS jmlbuking FROM bookingseminarkementerian GROUP BY bookingseminarkementerian.seminarkementerian_id) as v'),
                'v.seminarkementerian_id','=','seminarkementerian.id')
            ->groupby('seminarkementerian.id')
            ->get(['*']);

        if (is_null($semiar)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $semiar]);

    }



    /*------------  get booking kementerian where ----------------*/
    public function getbookingseminarkementerianwhere (Request $request)
    {
        $session_key = $request->input('session_key');
        $seminarkementerian_id = $request->input('seminarkementerian_id');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $kementerian_id = $this->getAdminkementerianId($session_key);
        if (is_null($kementerian_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $seminar=DB::table('bookingseminarkementerian')
            ->leftjoin('anggotakoperasi','anggotakoperasi.id','=','bookingseminarkementerian.anggotakoperasi_id')
            ->select('bookingseminarkementerian.*','anggotakoperasi.nama','anggotakoperasi.email','anggotakoperasi.telepon','anggotakoperasi.koperasi_id')
            ->where('seminarkementerian_id',$seminarkementerian_id)
            ->orderby('bookingseminarkementerian.id','desc')
            ->get();

        if (is_null($seminar)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $seminar]);

    }




    /*------------  Get transaksi  ----------------*/
    public function gettransaksi(Request $request)
    {
        $session_key = $request->input('session_key');

        //$date = $request->input('date');

        //$date = date('Y-m-d', strtotime('previous monday'));

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $transaksi=DB::select('call sp_getTransaksi()');


        if (is_null($transaksi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'akses tersedia ditemukan',
            'session_key' => $session_key, 'data' => $transaksi]);

    }


    /*------------  Get jumlah transaksirange  ----------------*/
    public function gettransaksirange(Request $request)
    {
        $session_key = $request->input('session_key');

        $tanggalmulai = $request->input('tanggalmulai');
        $tanggalselesai = $request->input('tanggalselesai');

        //$date = date('Y-m-d', strtotime('previous monday'));


        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $transaksi=DB::select('call sp_getTransaksiRange(?,?)',array($tanggalmulai,$tanggalselesai));

        if (is_null($transaksi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'akses tersedia ditemukan',
            'session_key' => $session_key, 'data' => $transaksi]);

    }



    /*------------  Get jumlah transaksi  ----------------*/
    public function getjumlahtransaksi(Request $request)
    {
        $session_key = $request->input('session_key');

        //$date = date('Y-m-d', strtotime('previous monday'));


        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }



        $transaksi=DB::select('call sp_getJumlahTransaksi()');


        if (is_null($transaksi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'akses tersedia ditemukan',
            'session_key' => $session_key, 'data' => $transaksi]);

    }


    /*------------  Get jumlah koperasi  ----------------*/
    public function getjumlahkoperasi(Request $request)
    {
        $session_key = $request->input('session_key');
        $bulanmulai = $request->input('bulanmulai');
        $bulanselesai = $request->input('bulanselesai');

        //$date = date('Y-m-d', strtotime('previous monday'));


        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }


        $jumlahkoperasi=DB::select('call sp_getJumlahKoperasi(?,?)',[$bulanmulai,$bulanselesai]);


        if (is_null($jumlahkoperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'akses tersedia ditemukan',
            'session_key' => $session_key, 'data' => $jumlahkoperasi]);

    }



    /*------------  Get jumlah anggota  ----------------*/
    public function getjumlahanggota(Request $request)
    {
        $session_key = $request->input('session_key');
        $bulanmulai = $request->input('bulanmulai');
        $bulanselesai = $request->input('bulanselesai');

        //$date = date('Y-m-d', strtotime('previous monday'));


        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }


        $jumlahanggota=DB::select('call sp_getJumlahAnggota(?,?)',[$bulanmulai,$bulanselesai]);


        if (is_null($jumlahanggota)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'akses tersedia ditemukan',
            'session_key' => $session_key, 'data' => $jumlahanggota]);

    }



    /*------------  Get jumlah transaksirange  ----------------*/
    public function getjumlahtransaksirange(Request $request)
    {
        $session_key = $request->input('session_key');

        $tanggalmulai = $request->input('tanggalmulai');
        $tanggalselesai = $request->input('tanggalselesai');

        //$date = date('Y-m-d', strtotime('previous monday'));


        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $transaksi=DB::select('call sp_getJumlahTransaksiRange(?,?)',array($tanggalmulai,$tanggalselesai));


        if (is_null($transaksi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'akses tersedia ditemukan',
            'session_key' => $session_key, 'data' => $transaksi]);

    }



    /*------------  Get jumlah transaksi  ----------------*/
    public function getjumlahtransaksihariini(Request $request)
    {
        $session_key = $request->input('session_key');

        //$date = date('Y-m-d', strtotime('previous monday'));


        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }



        $transaksi=DB::select('call sp_getJumlahTransaksiHariini()');


        if (is_null($transaksi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'akses tersedia ditemukan',
            'session_key' => $session_key, 'data' => $transaksi]);

    }


    /*------------  Get  transaksi  ----------------*/
    public function gettransaksihariini(Request $request)
    {
        $session_key = $request->input('session_key');

        //$date = date('Y-m-d', strtotime('previous monday'));


        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }



        $transaksi=DB::select('call sp_getTransaksiHariini()');


        if (is_null($transaksi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'akses tersedia ditemukan',
            'session_key' => $session_key, 'data' => $transaksi]);

    }



    /*------------  get Seminar kementerian ----------------*/
    public function getmemberbykategori(Request $request)
    {
        $session_key = $request->input('session_key');
        $provinsi_id = $request->input('provinsi_id');
        $kabupatenkota_id= $request->input('kabupatenkota_id');
        $skalakoperasi_id= $request->input('skalakoperasi_id');

        if ($this->checkIfSessionKementerianExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }




        if ($skalakoperasi_id=='') {

            if ($provinsi_id!=''){

                if($kabupatenkota_id!=''){

                    //tampil semua tapi filter kotakabupaten id

                    $member = Anggotakoperasi::with('koperasi')->with('kelurahan.kecamatan.kabupatenkota.provinsi')

                        ->whereHas('kelurahan.kecamatan.kabupatenkota.provinsi', function($query) use ($kabupatenkota_id) {

                            $query->whereKabupatenkotaId($kabupatenkota_id);
                        })
                        ->orderBy('anggotakoperasi.nama', 'asc')

                        ->get();
                }

                else {

                    //tampil semua tapi filter provinsi id

                    $member = Anggotakoperasi::with('koperasi')->with('kelurahan.kecamatan.kabupatenkota.provinsi')


                        ->whereHas('kelurahan.kecamatan.kabupatenkota.provinsi', function($query) use ($provinsi_id) {

                            $query->whereProvinsiId($provinsi_id);
                        })
                        ->orderBy('anggotakoperasi.nama', 'asc')

                        ->get();

                }

            }

            else {
                //tampilsemua

                $member = Anggotakoperasi::with('koperasi')->with('kelurahan.kecamatan.kabupatenkota.provinsi')

                    ->orderBy('anggotakoperasi.nama', 'asc')

                    ->get();
            }


        }

        else {

            if ($provinsi_id!=''){

                if($kabupatenkota_id!=''){

                    //tampil semua tapi filter kotakabupaten id + skala

                    $member = Anggotakoperasi::with('koperasi')->with('kelurahan.kecamatan.kabupatenkota.provinsi')

                        ->whereHas('koperasi', function($query) use ($skalakoperasi_id) {

                            $query->where('skalakoperasi_id',$skalakoperasi_id);
                        })

                        ->whereHas('kelurahan.kecamatan.kabupatenkota.provinsi', function($query) use ($kabupatenkota_id) {

                            $query->whereKabupatenkotaId($kabupatenkota_id);
                        })
                        ->orderBy('anggotakoperasi.nama', 'asc')

                        ->get();

                }

                else {

                    //tampil semua tapi filter provinsi id + skala

                    $member = Anggotakoperasi::with('koperasi')->with('kelurahan.kecamatan.kabupatenkota.provinsi')

                        ->whereHas('koperasi', function($query) use ($skalakoperasi_id) {

                            $query->where('skalakoperasi_id',$skalakoperasi_id);
                        })

                        ->whereHas('kelurahan.kecamatan.kabupatenkota.provinsi', function($query) use ($provinsi_id) {

                            $query->whereProvinsiId($provinsi_id);
                        })
                        ->orderBy('anggotakoperasi.nama', 'asc')

                        ->get();
                }

            }

            else {
                //tampil semua filter skala

                $member = Anggotakoperasi::with('koperasi')->with('kelurahan.kecamatan.kabupatenkota.provinsi')

                    ->whereHas('koperasi', function($query) use ($skalakoperasi_id) {

                        $query->where('skalakoperasi_id',$skalakoperasi_id);
                    })

                    ->orderBy('anggotakoperasi.nama', 'asc')

                    ->get();
            }


        }


        if (is_null($member)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $member]);

    }






}
