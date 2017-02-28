<?php

namespace App\Http\Controllers;


use App\Kategori;
use App\Transaksidetail;
use App\Transaksidetailtemp;
use Illuminate\Http\Request;
use Illuminate\Contracts\Hashing\Hasher;
use Carbon\Carbon;
use App\Http\Requests;



use App\Koperasi;
use App\Adminkoperasi;
use App\Sessionkoperasi;
use App\Provinsi;
use App\Kabupatenkota;
use App\Kecamatan;
use App\Kelurahan;
use App\Infokementerian;
use App\Infokoperasi;
use App\Password_resets;
use App\Trainingkoperasi;
use App\Anggotakoperasi;
use App\Tahunoperasi;
use App\Biayausaha;
use App\Simpanan;
use App\Tipekomponenshu;
use App\Komponenshu;
use App\Shu;
use App\Pelanggan;
use App\Suplier;
use App\Akseskoperasi;
use App\Bookingtrainingkoperasi;
use App\Http\Controllers\Controller;
use App\Jurnalkoperasi;
use App\Seminarkoperasi;
use App\Pembelian;
use App\Pembeliandetail;
use App\Produk;
use App\Transaksi;
use App\Pesankementeriankoperasi;
use App\Seminarkementerian;
use App\Trainingkementerian;
use App\Bookingtrainingkementerian;
use App\Bookingseminarkementerian;



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




class Apiv2Controller extends Controller
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


    /*------------  Login admin  ----------------*/
    public function loginadmin(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');
        $ip_address = $request->input('ip_address');

        $adminkoperasi = Adminkoperasi::where(
            'username', $username)->first();

        if (is_null($adminkoperasi)) {

            return Response::json(['status' => 0, 'message' => 'Inputan Salah']);
        }


        $cekaktifuser = Sessionkoperasi::where(['adminkoperasi_id' => $adminkoperasi->id])->first();

        if (!is_null($adminkoperasi)) {

            if ($adminkoperasi->status == 'Blocked') {

                return Response::json(['status' => 0, 'message' => 'Your account is blocked']);
            } else {

                if (Hash::check($password, $adminkoperasi->password)) {

                    if (is_null($cekaktifuser)) {

                        $session_key = $this->createOrUpdateSessionkoperasi($adminkoperasi->id);
                        $recallcekaktifuser = Sessionkoperasi::where(['adminkoperasi_id' => $adminkoperasi->id])->update(['ip_address' => $ip_address]);
                        Adminkoperasi::find($adminkoperasi->id)->update(['logingagal' => '0']);

                        return Response::json(['status' => 1, 'message' => 'adminkoperasi ditemukan', 'statuslogin' => '1', 'session_key' => $session_key, 'data' => $adminkoperasi]);
                    } elseif ($cekaktifuser->status == 1 && $ip_address != $cekaktifuser->ip_address) {
                        $cekexpired = Sessionkoperasi::where(['adminkoperasi_id' => $adminkoperasi->id])->where('expired_at', '>=', Carbon::now())->first();
                        if (!is_null($cekexpired)) {
                            return Response::json(['status' => 0, 'statuslogin' => '1', 'message' => 'lg ada yg onlen coy']);
                        } else {
                            $session_key = $this->createOrUpdateSessionkoperasi($adminkoperasi->id);
                            $recallcekaktifuser = Sessionkoperasi::where(['adminkoperasi_id' => $adminkoperasi->id])->update(['ip_address' => $ip_address]);
                            Adminkoperasi::find($adminkoperasi->id)->update(['logingagal' => '0']);
                            return Response::json(['status' => 1, 'message' => 'adminkoperasi ditemukan', 'statuslogin' => '1', 'session_key' => $session_key, 'data' => $adminkoperasi]);
                        }


                    } elseif ($cekaktifuser->status == 1 && $ip_address == $cekaktifuser->ip_address) {
                        $session_key = $this->createOrUpdateSessionkoperasi($adminkoperasi->id);

                        $cekaktifuser->update(['ip_address' => $ip_address]);

                        Adminkoperasi::find($adminkoperasi->id)->update(['logingagal' => '0']);
                        return Response::json(['status' => 1, 'message' => 'adminkoperasi ditemukan', 'statuslogin' => '1', 'session_key' => $session_key, 'data' => $adminkoperasi]);

                    } elseif ($cekaktifuser->status == 0) {

                        $session_key = $this->createOrUpdateSessionkoperasi($adminkoperasi->id);

                        $cekaktifuser->update(['ip_address' => $ip_address]);
                        //dd($cekaktifuser);
                        Adminkoperasi::find($adminkoperasi->id)->update(['logingagal' => '0']);

                        return Response::json(['status' => 1, 'message' => 'adminkoperasi ditemukan', 'statuslogin' => '1', 'session_key' => $session_key, 'data' => $adminkoperasi]);
                    }

                } else {

                    if ($adminkoperasi->logingagal > 4) {
                        Adminkoperasi::find($adminkoperasi->id)->update(['status' => 'Blocked']);
                        return Response::json(['status' => 0, 'message' => 'You have 5 times incorrectly entering data,Your account is blocked']);

                    } else {

                        $addgagal = $adminkoperasi->logingagal + 1;
                        Adminkoperasi::find($adminkoperasi->id)->update(['logingagal' => $addgagal]);

                        $countgagal = Adminkoperasi::find($adminkoperasi->id);

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
            return Response::json(['status' => 0, 'message' => 'adminkoperasi tidak ditemukan']);
        }


    }


    /*------ Logoutkoperasi ---------- */

    public function logout(Request $request)
    {

        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        Sessionkoperasi::where(['session_key' => $session_key])->first()->update(['status' => 0]);
        return Response::json(['status' => 1, 'message' => 'berhasil logout']);

    }

    /*------------  Get mykoperasi  ----------------*/
    public function getmykoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }


        $profile = Koperasi::find($koperasi_id)->with('kelurahan.kecamatan.kabupatenkota.provinsi')->first();

        if (is_null($profile)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Profile ditemukan',
            'session_key' => $session_key, 'data' => $profile]);

    }


    /*------------  Update mykoperasi  ----------------*/
    public function updatemykoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }


        $koperasi = Koperasi::findorfail($koperasi_id);
        $input = $request->all();


        $image = Input::file('foto');

        if (!is_null($image)) {

            if (file_exists(public_path('images/koperasi/' . $koperasi->foto))) {

                File::delete(public_path('images/koperasi/' . $koperasi->foto));
                File::delete(public_path('images/koperasi/thumb_' . $koperasi->foto));
            }


            $filename = date('YmdHis') . '.' . $image->getClientOriginalExtension();
            $filename_thumb = 'thumb_' . date('YmdHis') . '.' . $image->getClientOriginalExtension();

            $path = public_path('images/koperasi/' . $filename);
            $path_thumb = public_path('images/koperasi/' . $filename_thumb);

            Image::make($image->getRealPath())->resize(250, 250)->save($path);
            Image::make($image->getRealPath())->resize(90, 90)->save($path_thumb);

            $koperasi->foto = $filename;

        }

        $input['foto'] = $koperasi->foto;

        $update = Koperasi::find($koperasi_id)->update($input);

        if (!$update) {
            return Response::json(['status' => 0, 'message' => 'Update profile gagal']);
        }
        $updated = Koperasi::find($koperasi_id);
        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $updated]);
    }






    /*------------  Get Profile  ----------------*/
    public function getprofile(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkoperasi_id = $this->getAdminkoperasiId($session_key);
        if (is_null($adminkoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }


        $profile = Adminkoperasi::where(['id' => $adminkoperasi_id])->with('koperasi')->first();

        if (is_null($profile)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Profile ditemukan',
            'session_key' => $session_key, 'data' => $profile]);

    }

    /*------------  Update Profile  ----------------*/
    public function updateprofile(Adminkoperasi $adminkoperasi, Request $request)
    {
        $session_key = $request->input('session_key');


        $adminkoperasi_id = $this->getAdminkoperasiId($session_key);
        if (is_null($adminkoperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
        $adminkoperasi = Adminkoperasi::findorfail($adminkoperasi_id);
        $input = $request->all();
        $passwordlama = $request->input('passwordlama');
        $password = $request->input('password');


        $image = Input::file('foto');

        if (!is_null($image)) {

            if (file_exists(public_path('images/adminkoperasi/' . $adminkoperasi->foto))) {

                File::delete(public_path('images/adminkoperasi/' . $adminkoperasi->foto));
                File::delete(public_path('images/adminkoperasi/thumb_' . $adminkoperasi->foto));
            }


            $filename = date('YmdHis') . '.' . $image->getClientOriginalExtension();
            $filename_thumb = 'thumb_' . date('YmdHis') . '.' . $image->getClientOriginalExtension();

            $path = public_path('images/adminkoperasi/' . $filename);
            $path_thumb = public_path('images/adminkoperasi/' . $filename_thumb);

            Image::make($image->getRealPath())->resize(250, 250)->save($path);
            Image::make($image->getRealPath())->resize(90, 90)->save($path_thumb);

            $adminkoperasi->foto = $filename;

        }



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

        $input['foto'] = $adminkoperasi->foto;
        $input['password'] = $adminkoperasi->password;

        $update = Adminkoperasi::find($adminkoperasi_id)->update($input);

        if (!$update) {
            return Response::json(['status' => 0, 'message' => 'Update profile gagal']);
        }
        $updated = Adminkoperasi::find($adminkoperasi_id);
        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $updated]);
    }



    /*------------  Get akses koperasi  ----------------*/
    public function getakseskoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }


        $akses= DB::table('akseskoperasi')
            ->select('id','akses')
            ->whereNotIn('id', function($query) use ($koperasi_id)
            {
                $query->select('akseskoperasi_id')
                    ->from('adminkoperasi')
                    ->where('koperasi_id',$koperasi_id)
                    ->whereIn('akseskoperasi_id',[1,2]);
            })
            ->get();



        if (is_null($akses)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'akses tersedia ditemukan',
            'session_key' => $session_key, 'data' => $akses]);

    }






    /*------------  Get admin koperasi  ----------------*/
    public function getadminkoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $profile = Adminkoperasi::with('akseskoperasi')->where(['koperasi_id' => $koperasi_id])->where('akseskoperasi_id','!=','1')->get();

        if (is_null($profile)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Profile ditemukan',
            'session_key' => $session_key, 'data' => $profile]);

    }


    /*------------  Insert admin Koperasi ----------------*/
    public function insertadminkoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $input = $request->all();
        $input['koperasi_id'] = $koperasi_id;

        $input['foto'] = 'no_image.png';
        $hash = $request->input('password');
        $input['password'] = Hash::make($hash);

        // dd($input);
        $insert = Adminkoperasi::create($input);

        if (!$insert) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'insert berhasil..data ditemukan', 'session_key' => $session_key,
            'data' => $insert]);

    }


    /*------------  Edit Admin Koperasi ----------------*/
    public function editadminkoperasi(Request $request)
    {
        $session_key = $request->input('session_key');


        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
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




        $image = Input::file('foto');

        if (!is_null($image)) {

            if (file_exists(public_path('images/adminkoperasi/' . $adminkoperasi->foto))) {

                File::delete(public_path('images/adminkoperasi/' . $adminkoperasi->foto));
                File::delete(public_path('images/adminkoperasi/thumb_' . $adminkoperasi->foto));
            }


            $filename = date('YmdHis') . '.' . $image->getClientOriginalExtension();
            $filename_thumb = 'thumb_' . date('YmdHis') . '.' . $image->getClientOriginalExtension();

            $path = public_path('images/adminkoperasi/' . $filename);
            $path_thumb = public_path('images/adminkoperasi/' . $filename_thumb);

            Image::make($image->getRealPath())->resize(250, 250)->save($path);
            Image::make($image->getRealPath())->resize(90, 90)->save($path_thumb);

            $adminkoperasi->foto = $filename;

        }



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

        $input['foto'] = $adminkoperasi->foto;
        $input['password'] = $adminkoperasi->password;

        $update = Adminkoperasi::find($adminkoperasi_id)->update($input);

        if (!$update) {
            return Response::json(['status' => 0, 'message' => 'Update profile gagal']);
        }
        $updated = Adminkoperasi::find($adminkoperasi_id);
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


        if (file_exists('public/images/adminkoperasi/' . $adminkoperasi->foto)) {

            File::delete('public/images/adminkoperasi/' . $adminkoperasi->foto);
            File::delete('public/images/adminkoperasi/thumb_' . $adminkoperasi->foto);
        }
        $adminkoperasi->delete();


        if (is_null($adminkoperasi)) {
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


    /*------------  get Anggota Koperasi  ----------------*/
    public function getanggotakoperasi(Request $request)
    {
        $session_key = $request->input('session_key');
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        //dd($koperasi_id);

        $anggotakoperasi = Anggotakoperasi::where(['koperasi_id' => $koperasi_id])->with('kelurahan.kecamatan.kabupatenkota.provinsi')->orderBy('anggotakoperasi.id', 'desc')->get();

        if (is_null($anggotakoperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan', 'session_key' => $session_key,
            'data' => $anggotakoperasi]);

    }


    /*------------  Insert Anggota Koperasi ----------------*/
    public function insertanggotakoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $input = $request->all();
        $input['koperasi_id'] = $koperasi_id;

        $input['foto'] = 'no_image.png';
        $hash = $request->input('password');
        $input['password'] = Hash::make($hash);
        $input['status'] = 'Registered';

        // dd($input);
        $insert = Anggotakoperasi::create($input);

        if (!($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'insert berhasil..data ditemukan', 'session_key' => $session_key,
            'data' => $insert]);

    }


    /*------------  Get Anggotakoperasi  ----------------*/
    public function getanggotakoperasidetail(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $anggotakoperasi_id = $request->input('anggotakoperasi_id');

        $profile = Anggotakoperasi::where(['id' => $anggotakoperasi_id])->with('kelurahan.kecamatan.kabupatenkota.provinsi')->with('koperasi')->first();

        if (is_null($profile)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Profile ditemukan',
            'session_key' => $session_key, 'data' => $profile]);

    }


    /*------------  Update anggotakoperasi  ----------------*/
    public function updateanggotakoperasi(Anggotakoperasi $anggotakoperasi, Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $anggotakoperasi_id = $request->input('anggotakoperasi_id');

        $anggotakoperasi = Anggotakoperasi::find($anggotakoperasi_id);

        if (is_null($anggotakoperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tdk ditemukan']);
        }


        $input = $request->all();
        $password = $request->input('password');


        if ($password == '') {

            $anggotakoperasi->password;

        } elseif ($password != '') {

            $anggotakoperasi->password = Hash::make($request->input('password'));

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


    /*------------  delete anggota koperasi  ----------------*/
    public function deleteanggotakoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $anggotakoperasi_id = $request->input('anggotakoperasi_id');
        $anggotakoperasi = Anggotakoperasi::find($anggotakoperasi_id);
        if (is_null($anggotakoperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tdk ditemukan']);
        }


        if (file_exists('public/images/anggotakoperasi/' . $anggotakoperasi->foto)) {

            File::delete('public/images/anggotakoperasi/' . $anggotakoperasi->foto);
            File::delete('public/images/anggotakoperasi/thumb_' . $anggotakoperasi->foto);
        }
        $anggotakoperasi->delete();


        if (is_null($anggotakoperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data berhasil di hapus',
            'session_key' => $session_key]);

    }


    /*------------  get info koperasi  ----------------*/
    public function getinfokoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

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



    /*------------  insert info koperasi  ----------------*/
    public function insertinfokoperasi(Request $request)
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

            $path = public_path('images/infokoperasi/' . $filename);
            $path_thumb = public_path('images/infokoperasi/' . $filename_thumb);

            Image::make($foto->getRealPath())->resize(300, 300)->save($path);
            Image::make($foto->getRealPath())->resize(60, 60)->save($path_thumb);

            $foto->image = $filename;
            $input['foto'] = $foto->image;
        }
        else {

            $input['foto'] ='no_image.png';
        }

        $insert = Infokoperasi::create($input);

        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $insert]);

    }



    /*------------  edit info koperasi  ----------------*/
    public function editinfokoperasi (Infokoperasi $infokoperasi,Request $request)
    {
        $session_key = $request->input('session_key');
        $infokoperasi_id = $request->input('infokoperasi_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Infokoperasi::findorfail($infokoperasi_id);

        if(!$info){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $info]);


    }



    /*------------  Update info koperasi  ----------------*/
    public function updateinfokoperasi (Infokoperasi $infokoperasi,Request $request)
    {
        $session_key = $request->input('session_key');
        $infokoperasi_id = $request->input('infokoperasi_id');


        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }


        $info=Infokoperasi::findorfail($infokoperasi_id);
        $input = $request->all();
        $foto = Input::file('foto');

        if(!is_null($foto)) {

            if(file_exists(public_path('images/infokoperasi/'.$info->foto))) {

                File::delete(public_path('images/infokoperasi/'.$info->foto));
                File::delete(public_path('images/infokoperasi/thumb_'.$info->foto));
            }

            $filename = date('YmdHis') . '.' . $foto->getClientOriginalExtension();
            $filename_thumb = 'thumb_'.date('YmdHis') . '.' . $foto->getClientOriginalExtension();

            $path = public_path('images/infokoperasi/' . $filename);
            $path_thumb = public_path('images/infokoperasi/' . $filename_thumb);

            Image::make($foto->getRealPath())->resize(300, 300)->save($path);
            Image::make($foto->getRealPath())->resize(60, 60)->save($path_thumb);

            $info->foto = $filename;
        }
        $input['foto'] = $info->foto;

        $update= Infokoperasi::find($infokoperasi_id)->update($input);

        if(!$update){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $update]);

    }


    /*------------  delete deleteinfokoperasi  ----------------*/
    public function deleteinfokoperasi (Request $request)
    {
        $session_key = $request->input('session_key');
        $infokoperasi_id= $request->input('infokoperasi_id');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }


        $info=Infokoperasi::findorfail($infokoperasi_id);

        if(file_exists('public/images/infokoperasi/'.$info->foto)) {

            File::delete('public/images/infokoperasi/'.$info->foto);
            File::delete('public/images/infokoperasi/thumb_'.$info->foto);
        }

        $del= Infokoperasi::find($infokoperasi_id)->delete();

        if(!$del){
            return Response::json(['status' => 0, 'message' => 'delete gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses delete',
            'session_key' => $session_key, 'data' => $del]);


    }


    /*------------  get Training koperasi ----------------*/
    public function gettrainingkoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
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


    /*------------  insert training koperasi  ----------------*/
    public function inserttrainingkoperasi(Request $request)
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

            $path = public_path('images/trainingkoperasi/' . $filename);
            $path_thumb = public_path('images/trainingkoperasi/' . $filename_thumb);

            Image::make($foto->getRealPath())->resize(300, 300)->save($path);
            Image::make($foto->getRealPath())->resize(60, 60)->save($path_thumb);

            $foto->image = $filename;
            $input['foto'] = $foto->image;
        }
        else {

            $input['foto'] ='no_image.png';
        }

        $insert = Trainingkoperasi::create($input);

        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $insert]);

    }



    /*------------  edit training koperasi  ----------------*/
    public function edittrainingkoperasi (Infokoperasi $infokoperasi,Request $request)
    {
        $session_key = $request->input('session_key');
        $trainingkoperasi_id= $request->input('trainingkoperasi_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Trainingkoperasi::findorfail($trainingkoperasi_id);

        if(!$info){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $info]);


    }



    /*------------  Update training koperasi  ----------------*/
    public function updatetrainingkoperasi (Infokoperasi $infokoperasi,Request $request)
    {
        $session_key = $request->input('session_key');
        $trainingkoperasi_id = $request->input('trainingkoperasi_id');
        //dd($infokoperasi_id);

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Trainingkoperasi::find($trainingkoperasi_id);

        $input = $request->all();
        $foto = Input::file('foto');

        if(!is_null($foto)) {

            if(file_exists(public_path('images/trainingkoperasi/'.$info->foto))) {

                File::delete(public_path('images/trainingkoperasi/'.$info->foto));
                File::delete(public_path('images/trainingkoperasi/thumb_'.$info->foto));
            }

            $filename = date('YmdHis') . '.' . $foto->getClientOriginalExtension();
            $filename_thumb = 'thumb_'.date('YmdHis') . '.' . $foto->getClientOriginalExtension();

            $path = public_path('images/trainingkoperasi/' . $filename);
            $path_thumb = public_path('images/trainingkoperasi/' . $filename_thumb);

            Image::make($foto->getRealPath())->resize(300, 300)->save($path);
            Image::make($foto->getRealPath())->resize(60, 60)->save($path_thumb);

            $info->foto = $filename;
        }
        $input['foto'] = $info->foto;
        $update= Trainingkoperasi::find($trainingkoperasi_id)->update($input);

        if(!$update){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $update]);
    }


    /*------------  delete deletetrainingkoperasi  ----------------*/
    public function deletetrainingkoperasi (Request $request)
    {
        $session_key = $request->input('session_key');
        $trainingkoperasi_id= $request->input('trainingkoperasi_id');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Trainingkoperasi::findorfail($trainingkoperasi_id);
        if(file_exists('public/images/trainingkoperasi/'.$info->foto)) {
            File::delete('public/images/trainingkoperasi/'.$info->foto);
            File::delete('public/images/trainingkoperasi/thumb_'.$info->foto);
        }
        $del= Trainingkoperasi::find($trainingkoperasi_id)->delete();

        if(!$del){
            return Response::json(['status' => 0, 'message' => 'delete gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses delete',
            'session_key' => $session_key, 'data' => $del]);

    }





    /*------------  get Seminar koperasi ----------------*/
    public function getseminarkoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $seminar= Seminarkoperasi::where(['koperasi_id' => $koperasi_id])->orderBy('id', 'desc')->get();


        if (is_null($seminar)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $seminar]);

    }


    /*------------  insert seminar koperasi  ----------------*/
    public function insertseminarkoperasi(Request $request)
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

            $path = public_path('images/seminarkoperasi/' . $filename);
            $path_thumb = public_path('images/seminarkoperasi/' . $filename_thumb);

            Image::make($foto->getRealPath())->resize(300, 300)->save($path);
            Image::make($foto->getRealPath())->resize(60, 60)->save($path_thumb);

            $foto->image = $filename;
            $input['foto'] = $foto->image;
        }
        else {

            $input['foto'] ='no_image.png';
        }

        $insert = Seminarkoperasi::create($input);

        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $insert]);
    }



    /*------------  edit training koperasi  ----------------*/
    public function editseminarkoperasi (Request $request)
    {
        $session_key = $request->input('session_key');
        $seminarkoperasi_id = $request->input('seminarkoperasi_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $info=Seminarkoperasi::findorfail($seminarkoperasi_id);
        if(!$info){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $info]);


    }



    /*------------  Update training koperasi  ----------------*/
    public function updateseminarkoperasi (Request $request)
    {
        $session_key = $request->input('session_key');
        $seminarkoperasi_id = $request->input('seminarkoperasi_id');
        //dd($infokoperasi_id);
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Seminarkoperasi::findorfail($seminarkoperasi_id);
        $input = $request->all();
        $foto = Input::file('foto');

        if(!is_null($foto)) {

            if(file_exists(public_path('images/seminarkoperasi/'.$info->foto))) {

                File::delete(public_path('images/seminarkoperasi/'.$info->foto));
                File::delete(public_path('images/seminarkoperasi/thumb_'.$info->foto));
            }

            $filename = date('YmdHis') . '.' . $foto->getClientOriginalExtension();
            $filename_thumb = 'thumb_'.date('YmdHis') . '.' . $foto->getClientOriginalExtension();

            $path = public_path('images/seminarkoperasi/' . $filename);
            $path_thumb = public_path('images/seminarkoperasi/' . $filename_thumb);

            Image::make($foto->getRealPath())->resize(300, 300)->save($path);
            Image::make($foto->getRealPath())->resize(60, 60)->save($path_thumb);

            $info->foto = $filename;
        }
        $input['foto'] = $info->foto;
        $update= Seminarkoperasi::find($seminarkoperasi_id)->update($input);

        if(!$update){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $update]);
    }


    /*------------  delete seminar koperasi  ----------------*/
    public function deleteseminarkoperasi (Request $request)
    {
        $session_key = $request->input('session_key');
        $seminarkoperasi_id= $request->input('seminarkoperasi_id');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Seminarkoperasi::find($seminarkoperasi_id);
        if(file_exists('public/images/seminarkoperasi/'.$info->foto)) {
            File::delete('public/images/seminarkoperasi/'.$info->foto);
            File::delete('public/images/seminarkoperasi/thumb_'.$info->foto);
        }
        $del= Seminarkoperasi::find($seminarkoperasi_id)->delete();

        if(!$del){
            return Response::json(['status' => 0, 'message' => 'delete gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses delete',
            'session_key' => $session_key, 'data' => $del]);
    }



    /*------------  get booking koperasi ----------------*/
    public function getbookingtrainingkoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        // $training = Trainingkoperasi::with('bookingtrainingkoperasi.budget_count')->groupby('trainingkoperasi.id')->get();
        $training=DB::table('Trainingkoperasi')
            ->leftjoin('bookingtrainingkoperasi','bookingtrainingkoperasi.trainingkoperasi_id','=','trainingkoperasi.id')
            ->leftjoin(DB::raw('(SELECT id,trainingkoperasi_id,anggotakoperasi_id, count(anggotakoperasi_id) AS jmlbuking FROM bookingtrainingkoperasi GROUP BY bookingtrainingkoperasi.trainingkoperasi_id) as v'),
                'v.trainingkoperasi_id','=','trainingkoperasi.id')
            ->groupby('trainingkoperasi.id')
            ->get(['*']);


        if (is_null($training)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $training]);

    }



    /*------------  get booking koperasi where ----------------*/
    public function getbookingtrainingkoperasiwhere (Request $request)
    {
        $session_key = $request->input('session_key');
        $trainingkoperasi_id = $request->input('trainingkoperasi_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $training=DB::table('bookingtrainingkoperasi')
            ->leftjoin('anggotakoperasi','anggotakoperasi.id','=','bookingtrainingkoperasi.anggotakoperasi_id')
            ->where('trainingkoperasi_id',$trainingkoperasi_id)
            ->orderby('anggotakoperasi_id','desc')

            ->get();


        if (is_null($training)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $training]);

    }




    /*------------  get seminar koperasi ----------------*/
    public function getbookingseminarkoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $semiar=DB::table('seminarkoperasi')
            ->leftjoin('bookingseminarkoperasi','bookingseminarkoperasi.seminarkoperasi_id','=','seminarkoperasi.id')
            ->leftjoin(DB::raw('(SELECT id,seminarkoperasi_id,anggotakoperasi_id, count(anggotakoperasi_id) AS jmlbuking FROM bookingseminarkoperasi GROUP BY bookingseminarkoperasi.seminarkoperasi_id) as v'),
                'v.seminarkoperasi_id','=','seminarkoperasi.id')
            ->groupby('seminarkoperasi.id')
            ->get(['*']);

        if (is_null($semiar)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $semiar]);

    }



    /*------------  get booking koperasi where ----------------*/
    public function getbookingseminarkoperasiwhere (Request $request)
    {
        $session_key = $request->input('session_key');
        $seminarkoperasi_id = $request->input('seminarkoperasi_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $seminar=DB::table('bookingseminarkoperasi')
            ->leftjoin('anggotakoperasi','anggotakoperasi.id','=','bookingseminarkoperasi.anggotakoperasi_id')
            ->where('seminarkoperasi_id',$seminarkoperasi_id)
            ->orderby('anggotakoperasi_id','desc')
            ->get();

        if (is_null($seminar)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $seminar]);

    }





    /*------------  get tahun operasi  ----------------*/
    public function gettahunoperasi(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $infokoperasi = Tahunoperasi::where(['koperasi_id' => $koperasi_id])->orderBy('id', 'desc')->get();


        if (is_null($infokoperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $infokoperasi]);

    }



    /*------------  insert tahun operasi  ----------------*/
    public function inserttahunoperasi(Request $request)
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
        $insert = Tahunoperasi::create($input);

        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $insert]);

    }



    /*------------  edit tahun operasi  ----------------*/
    public function edittahunoperasi (Infokoperasi $infokoperasi,Request $request)
    {
        $session_key = $request->input('session_key');
        $tahunoperasi_id = $request->input('tahunoperasi_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Tahunoperasi::findorfail($tahunoperasi_id);

        if(!$info){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $info]);


    }



    /*------------  Update tahun operasi  ----------------*/
    public function updatetahunoperasi (Request $request)
    {
        $session_key = $request->input('session_key');
        $tahunoperasi_id = $request->input('tahunoperasi_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }



        $input = $request->all();

        $update= Tahunoperasi::find($tahunoperasi_id)->update($input);

        if(!$update){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $update]);

    }


    /*------------  Update status tahun operasi  ----------------*/
    public function updatestatustahunoperasi (Request $request)
    {
        $session_key = $request->input('session_key');
        $tahunoperasi_id = $request->input('tahunoperasi_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $update= Tahunoperasi::find($tahunoperasi_id)->update(['status'=>'Aktif']);
        //dd($update);

        $nonaktifkanyanglain=Tahunoperasi::where('koperasi_id',$koperasi_id)->where('id','!=',$tahunoperasi_id)->update(['status'=>'Tidak Aktif']);

        if(!$update){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $update]);

    }


    /*------------  delete deletetahunoperasi  ----------------*/
    public function deletetahunoperasi (Request $request)
    {
        $session_key = $request->input('session_key');
        $tahunoperasi_id = $request->input('tahunoperasi_id');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }


        $del= Tahunoperasi::find($tahunoperasi_id)->delete();

        if(!$del){
            return Response::json(['status' => 0, 'message' => 'delete gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses delete',
            'session_key' => $session_key, 'data' => $del]);


    }




    /*------------  get biaya usaha  ----------------*/
    public function getbiayausaha(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $tahunoperasi = Tahunoperasi::where(['koperasi_id' => $koperasi_id,'status'=>'Aktif'])->select('id','status')->first();


        $infokoperasi = Biayausaha::with('tahunoperasi')->where(['koperasi_id' => $koperasi_id,'tahunoperasi_id'=>$tahunoperasi->id])->orderBy('id', 'desc')->get();


        if (is_null($infokoperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $infokoperasi]);

    }



    /*------------  insert biaya usaha  ----------------*/
    public function insertbiayausaha(Request $request)
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
        $insert = Biayausaha::create($input);

        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $insert]);

    }



    /*------------  edit biaya usaha ----------------*/
    public function editbiayausaha (Infokoperasi $infokoperasi,Request $request)
    {
        $session_key = $request->input('session_key');
        $biayausaha_id = $request->input('biayausaha_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Biayausaha::findorfail($biayausaha_id);

        if(!$info){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $info]);


    }



    /*------------  Update biaya usaha  ----------------*/
    public function updatebiayausaha (Infokoperasi $infokoperasi,Request $request)
    {
        $session_key = $request->input('session_key');
        $biayausaha_id = $request->input('biayausaha_id');


        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $input = $request->all();

        $update= Biayausaha::find($biayausaha_id)->update($input);

        if(!$update){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $update]);

    }


    /*------------  delete biaya usaha  ----------------*/
    public function deletebiayausaha (Request $request)
    {
        $session_key = $request->input('session_key');
        $biayausaha_id = $request->input('biayausaha_id');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }


        $del= Biayausaha::find($biayausaha_id)->delete();

        if(!$del){
            return Response::json(['status' => 0, 'message' => 'delete gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses delete',
            'session_key' => $session_key, 'data' => $del]);


    }






    /*------------  get jurnalaktif  ----------------*/
    public function getjurnalaktif(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $tahunoperasiaktif  =Tahunoperasi::where(['koperasi_id'=>$koperasi_id,'status'=>'Aktif'])->first();

        $jurnal = Jurnalkoperasi::with('tahunoperasi')->where(['koperasi_id' => $koperasi_id,'tahunoperasi_id'=>$tahunoperasiaktif->id])->orderBy('id', 'desc')->get();
        if (is_null($jurnal)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $jurnal]);
    }



    /*------------  get jurnal where  ----------------*/
    public function getjurnalwhere(Request $request)
    {
        $session_key = $request->input('session_key');
        $tahunoperasi_id = $request->input('tahunoperasi_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        if($tahunoperasi_id!=''){

            $jurnal = Jurnalkoperasi::with('tahunoperasi')->where(['koperasi_id' => $koperasi_id,'tahunoperasi_id'=>$tahunoperasi_id])->first();
        }

        else{

            $jurnal = Jurnalkoperasi::with('tahunoperasi')->where(['koperasi_id' => $koperasi_id])->orderBy('tahunoperasi.id', 'desc')->get();
        }


      if (is_null($jurnal)) {



            return Response::json(['status' => 1, 'message' => 'data tidak ditemukan','data' =>[] ]);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan','data' => $jurnal]);
    }


    /*------------  reset jurnal  ----------------*/
    public function resetjurnal(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $tahunoperasiaktif  =Tahunoperasi::where(['koperasi_id'=>$koperasi_id,'status'=>'Aktif'])->first();

        $jurnal = Jurnalkoperasi::where(['koperasi_id' => $koperasi_id,'tahunoperasi_id'=>$tahunoperasiaktif->id])->delete();
        if (is_null($jurnal)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $jurnal]);
    }



    /*------------ Kalkulasi JUrnal  ----------------*/
    public function kalkulasijurnal(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }


        $tahunoperasiaktif  =Tahunoperasi::where(['koperasi_id'=>$koperasi_id,'status'=>'Aktif'])->first();


        $cekjurnal=Jurnalkoperasi::where(['koperasi_id'=>$koperasi_id,'tahunoperasi_id'=>$tahunoperasiaktif->id])->first();
       // dd($cekjurnal);
        if (!is_null($cekjurnal)) {
            return Response::json(['status' => 0, 'message' => 'Jurnal sudah di kalkulasi,silahkan reset terlebih dahulu untuk melakukan kalkulasi Jurnal ']);
        }



            $hpp = Pembelian::where(['koperasi_id' => $koperasi_id, 'tahunoperasi_id' => $tahunoperasiaktif->id])->sum('totalhargabeli');
            $penjualan = Transaksi::where(['koperasi_id' => $koperasi_id, 'tahunoperasi_id' => $tahunoperasiaktif->id])->sum('totalhargajual');

            $input['koperasi_id'] = $koperasi_id;
            $input['tahunoperasi_id'] = $tahunoperasiaktif->id;
            $input['penjualan'] = $penjualan;
            $input['hpp'] = $hpp;
            $thoperasi = $input['tahunoperasi_id'];
            $kadaluarsa = Tahunoperasi::where('id', $thoperasi)->select('tanggalselesai')->first();

            $input['biayausaha'] = Biayausaha::where(['koperasi_id' => $koperasi_id, 'tahunoperasi_id' => $thoperasi])
                ->sum('jumlah');


            $input['totalsimpanan'] = Simpanan::where(['koperasi_id' => $koperasi_id, 'status' => 'Belum Diambil'])
                ->where('tanggalbayar', '<=', $kadaluarsa->tanggalselesai)
                ->sum('jumlah');

            $input['labakotor'] = $input['penjualan'] - $input['hpp'];
            $input['lababersih'] = $input['labakotor'] - $input['biayausaha'];


            //dd($input);

            $insert = Jurnalkoperasi::create($input);

            if (is_null($insert)) {
                return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
            }

            return Response::json(['status' => 1, 'message' => 'data ditemukan',
                'data' => $insert]);



    }



    /*------------  get Simpanan  ----------------*/
    public function getsimpanan(Request $request)
    {
        $session_key = $request->input('session_key');

        //dd(Carbon::now());

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $infokoperasi = Simpanan::with('anggotakoperasi')->where(['koperasi_id' => $koperasi_id])->orderBy('simpanan.id', 'desc')->get();


        if (is_null($infokoperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $infokoperasi]);

    }


    /*------------  cekjenissimpanan ----------------*/
    public function cekjenissimpanan ( Request $request)
    {
        $session_key = $request->input('session_key');
        $anggotakoperasi_id = $request->input('anggotakoperasi_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $cek=DB::select('call sp_cekSimpananPokok(?)', [$anggotakoperasi_id]);

        if ($cek[0]->status==1){

            $hasil= '[{"id":1,"nama":"Wajib"},{"id":2,"nama":"Sukarela"}]';

        }
        elseif ($cek[0]->status==0){

            $hasil= '[{"id":1,"nama":"Pokok"},{"id":2,"nama":"Wajib"},{"id":3,"nama":"Sukarela"}]';
        };

        if(!$cek){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => json_decode($hasil)]);


    }



    /*------------  insert biaya usaha  ----------------*/
    public function insertsimpanan(Request $request)
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
        $input['tanggalbayar']=Carbon::now();
        $input['status']='Belum Diambil';


        $insert = Simpanan::create($input);

        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $insert]);

    }


    /*------------  edit simpanan ----------------*/
    public function editsimpanan (Infokoperasi $infokoperasi,Request $request)
    {
        $session_key = $request->input('session_key');
        $simpanan_id = $request->input('simpanan_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Simpanan::findorfail($simpanan_id);

        if(!$info){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $info]);


    }




    /*------------  Update simpanan  ----------------*/
    public function updatesimpanan(Request $request)
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
        $simpanan_id = $request->input('simpanan_id');
        $input['koperasi_id']=$koperasi_id;
        // $input['tanggalbayar']=Carbon::now();
        // $input['status']='Belum Diambil';


        $update= Simpanan::find($simpanan_id)->update($input);

        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $update]);

    }


    /*------------  delete simpanan  ----------------*/
    public function deletesimpanan (Request $request)
    {
        $session_key = $request->input('session_key');
        $simpanan_id = $request->input('simpanan_id');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $del= Simpanan::find($simpanan_id)->delete();

        if(!$del){
            return Response::json(['status' => 0, 'message' => 'delete gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses delete',
            'session_key' => $session_key, 'data' => $del]);


    }





    /*------------  get tipekomponen shu  ----------------*/
    public function gettipekomponenshu(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $tahunoperasi = Tahunoperasi::where(['koperasi_id' => $koperasi_id,'status'=>'Aktif'])->select('id','status')->first();
        $th=$tahunoperasi->id;

        //  $komponenshu = Komponenshu::where(['koperasi_id' => $koperasi_id])->orderBy('id', 'desc')->get();

        $tipe= DB::table('tipekomponenshu')
            ->select('id','tipekomponenshu')

            ->whereNotIn('id', function($query) use ($koperasi_id,$th)
            {
                $query->select('tipekomponenshu_id')
                    ->from('komponenshu')
                    ->where('koperasi_id',$koperasi_id)
                    ->where('tahunoperasi_id',$th)
                    ->whereIn('tipekomponenshu_id',[1,2,3]);
            })
            ->get();



        if (is_null($tipe)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan','data' => $tipe]);

    }





    /*------------  get komponen shu  ----------------*/
    public function getkomponenshu(Request $request)
    {
        $session_key = $request->input('session_key');



        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $tahunoperasi = Tahunoperasi::where(['koperasi_id' => $koperasi_id,'status'=>'Aktif'])->select('id','status')->first();

        $komponenshu = Komponenshu::where(['koperasi_id' => $koperasi_id,'tahunoperasi_id'=>$tahunoperasi->id])->with('tipekomponenshu')->with('tahunoperasi')
            ->orderBy('komponenshu.id', 'asc')->get();


        if (is_null($komponenshu)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $komponenshu]);

    }



    /*------------  insert komponenshu ----------------*/
    public function insertkomponenshu(Request $request)
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
        $tahunoperasi_id = $request->input('tahunoperasi_id');
        $persentase= $request->input('persentase');
        $input['koperasi_id']=$koperasi_id;
        // dd($input);
        $cekpersen=DB::select('call sp_cekPersentaseKomponenSHU(?,?)', [$tahunoperasi_id,$persentase]);

        if ($cekpersen[0]->status==1){

            $insert = Komponenshu::create($input);

        }
        elseif ($cekpersen[0]->status==0){

            return Response::json(['status' => 0, 'message' => 'Kalkulasi Persentase melebihi 100%']);
        }



        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' =>$session_key,'data' => $insert]);

    }


    /*------------  edit komponenshu ----------------*/
    public function editkomponenshu (Infokoperasi $infokoperasi,Request $request)
    {
        $session_key = $request->input('session_key');
        $komponenshu_id = $request->input('komponenshu_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Komponenshu::findorfail($komponenshu_id);

        if(!$info){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $info]);


    }



    /*------------  Update komponenshu  ----------------*/
    public function updatekomponenshu(Request $request)
    {
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $tahunoperasi_id = $request->input('tahunoperasi_id');
        $persentase= $request->input('persentase');
        $komponenshu_id = $request->input('komponenshu_id');


        $input = $request->all();

        $update= Komponenshu::find($komponenshu_id);
        $newpersentase=$persentase-$update->persentase;


        $cekpersen=DB::select('call sp_cekPersentaseKomponenSHU(?,?)', [$tahunoperasi_id,$newpersentase]);
        //dd($cekpersen);

        if ($cekpersen[0]->status==1){
            //dd($input);

            $update->update($input);


        }
        elseif ($cekpersen[0]->status==0){

            return Response::json(['status' => 0, 'message' => 'Kalkulasi Persentase melebihi 100%']);
        }




        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $update]);

    }


    /*------------  delete komponenshu  ----------------*/
    public function deletekomponenshu(Request $request)
    {
        $session_key = $request->input('session_key');
        $komponenshu_id = $request->input('komponenshu_id');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $del= Komponenshu::find($komponenshu_id)->delete();

        if(!$del){
            return Response::json(['status' => 0, 'message' => 'delete gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses delete',
            'session_key' => $session_key, 'data' => $del]);

    }









    /*------------  get pelanggan  ----------------*/
    public function getpelanggan(Request $request)
    {
        $session_key = $request->input('session_key');



        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $pelanggan = Pelanggan::where(['koperasi_id' => $koperasi_id])->orderBy('id', 'desc')->get();


        if (is_null($pelanggan)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $pelanggan]);

    }



    /*------------  insert pelanggan ----------------*/
    public function insertpelanggan(Request $request)
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

            $path = public_path('images/pelanggan/' . $filename);
            $path_thumb = public_path('images/pelanggan/' . $filename_thumb);

            Image::make($foto->getRealPath())->resize(300, 300)->save($path);
            Image::make($foto->getRealPath())->resize(60, 60)->save($path_thumb);

            $foto->image = $filename;
            $input['foto'] = $foto->image;
        }
        else {

            $input['foto'] ='no_image.png';
        }



        $insert = Pelanggan::create($input);

        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $insert]);

    }


    /*------------  edit pelanggan ----------------*/
    public function editpelanggan (Infokoperasi $infokoperasi,Request $request)
    {
        $session_key = $request->input('session_key');
        $pelanggan_id = $request->input('pelanggan_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Pelanggan::findorfail($pelanggan_id);

        if(!$info){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $info]);


    }



    /*------------  Update pelanggan  ----------------*/
    public function updatepelanggan(Request $request)
    {
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }



        $input = $request->all();
        $pelanggan_id= $request->input('pelanggan_id');

        $info=Pelanggan::find($pelanggan_id);

        $foto = Input::file('foto');

        if(!is_null($foto)) {

            if(file_exists(public_path('images/pelanggan/'.$info->foto))) {

                File::delete(public_path('images/pelanggan/'.$info->foto));
                File::delete(public_path('images/pelanggan/thumb_'.$info->foto));
            }

            $filename = date('YmdHis') . '.' . $foto->getClientOriginalExtension();
            $filename_thumb = 'thumb_'.date('YmdHis') . '.' . $foto->getClientOriginalExtension();

            $path = public_path('images/pelanggan/' . $filename);
            $path_thumb = public_path('images/pelanggan/' . $filename_thumb);

            Image::make($foto->getRealPath())->resize(300, 300)->save($path);
            Image::make($foto->getRealPath())->resize(60, 60)->save($path_thumb);

            $info->foto = $filename;
        }
        $input['foto'] = $info->foto;


        $update=Pelanggan::find($pelanggan_id)->update($input);

        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $update]);

    }


    /*------------  delete pelanggan  ----------------*/
    public function deletepelanggan(Request $request)
    {
        $session_key = $request->input('session_key');
        $pelanggan_id = $request->input('pelanggan_id');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Pelanggan::findorfail($pelanggan_id);

        if(file_exists('public/images/pelanggan/'.$info->foto)) {

            File::delete('public/images/pelanggan/'.$info->foto);
            File::delete('public/images/pelanggan/thumb_'.$info->foto);
        }


        $del= Pelanggan::find($pelanggan_id)->delete();
        if(!$del){
            return Response::json(['status' => 0, 'message' => 'delete gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses delete',
            'session_key' => $session_key, 'data' => $del]);


    }






    /*------------  get suplier  ----------------*/
    public function getsuplier(Request $request)
    {
        $session_key = $request->input('session_key');


        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $suplier = Suplier::with('kelurahan.kecamatan.kabupatenkota.provinsi')->where(['koperasi_id' => $koperasi_id])->orderBy('suplier.id', 'desc')->get();


        if (is_null($suplier)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $suplier]);

    }



    /*------------  insert supliaer ----------------*/
    public function insertsuplier(Request $request)
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

            $path = public_path('images/suplier/' . $filename);
            $path_thumb = public_path('images/suplier/' . $filename_thumb);

            Image::make($foto->getRealPath())->resize(300, 300)->save($path);
            Image::make($foto->getRealPath())->resize(60, 60)->save($path_thumb);

            $foto->image = $filename;
            $input['foto'] = $foto->image;
        }
        else {

            $input['foto'] ='no_image.png';
        }

        // dd($input);
        $insert=Suplier::create($input);

        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $insert]);

    }


    /*------------  edit suplier ----------------*/
    public function editsuplier (Request $request)
    {
        $session_key = $request->input('session_key');
        $suplier_id = $request->input('suplier_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Suplier::where('id',$suplier_id)->with('kelurahan.kecamatan.kabupatenkota.provinsi')->first();

        if(!$info){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $info]);

    }



    /*------------  Update suplier  ----------------*/
    public function updatesuplier(Request $request)
    {
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }


        $input = $request->all();
        $suplier_id = $request->input('suplier_id');
        $info=Suplier::findorfail($suplier_id);


        $foto = Input::file('foto');

        if(!is_null($foto)) {

            if(file_exists(public_path('images/suplier/'.$info->foto))) {

                File::delete(public_path('images/suplier/'.$info->foto));
                File::delete(public_path('images/suplier/thumb_'.$info->foto));
            }

            $filename = date('YmdHis') . '.' . $foto->getClientOriginalExtension();
            $filename_thumb = 'thumb_'.date('YmdHis') . '.' . $foto->getClientOriginalExtension();

            $path = public_path('images/suplier/' . $filename);
            $path_thumb = public_path('images/suplier/' . $filename_thumb);

            Image::make($foto->getRealPath())->resize(300, 300)->save($path);
            Image::make($foto->getRealPath())->resize(60, 60)->save($path_thumb);

            $info->foto = $filename;
        }
        $input['foto'] = $info->foto;


        $update= Suplier::find($suplier_id)->update($input);

        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $update]);

    }


    /*------------  delete suplier  ----------------*/
    public function deletesuplier(Request $request)
    {
        $session_key = $request->input('session_key');
        $suplier_id = $request->input('suplier_id');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Suplier::findorfail($suplier_id);

        if(file_exists('public/images/suplier/'.$info->foto)) {

            File::delete('public/images/suplier/'.$info->foto);
            File::delete('public/images/suplier/thumb_'.$info->foto);
        }

        $del= Suplier::find($suplier_id)->delete();

        if(!$del){
            return Response::json(['status' => 0, 'message' => 'delete gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses delete',
            'session_key' => $session_key, 'data' => $del]);

    }




    /*------------  get pembelian  ----------------*/
    public function getpembelian(Request $request)
    {
        $session_key = $request->input('session_key');


        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $get= Pembelian::where(['koperasi_id' => $koperasi_id])->with('tahunoperasi')->orderBy('id', 'desc')->get();


        if (is_null($get)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $get]);

    }



    /*------------  insert pembelian ----------------*/
    public function insertpembelian(Request $request)
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
        // dd($input);
        $insert = Pembelian::create($input);


        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $insert]);

    }


    /*------------  edit pembelian ----------------*/
    public function editpembelian (Infokoperasi $infokoperasi,Request $request)
    {
        $session_key = $request->input('session_key');
        $pembelian_id = $request->input('pembelian_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Pembelian::findorfail($pembelian_id);

        if(!$info){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $info]);


    }



    /*------------  Update pembelian  ----------------*/
    public function updatepembelian(Request $request)
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
        $pembelian_id = $request->input('pembelian_id');


        $update= Pembelian::find($pembelian_id)->update($input);

        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $update]);

    }


    /*------------  delete pembelian  ----------------*/
    public function deletepembelian(Request $request)
    {
        $session_key = $request->input('session_key');
        $pembelian_id = $request->input('pembelian_id');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $del= Pembelian::find($pembelian_id)->delete();

        if(!$del){
            return Response::json(['status' => 0, 'message' => 'delete gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses delete',
            'session_key' => $session_key, 'data' => $del]);


    }


    /*------------  get pembeliandetail  ----------------*/
    public function getpembeliandetail(Request $request)
    {
        $session_key = $request->input('session_key');
        $pembelian_id = $request->input('pembelian_id');


        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $get= Pembeliandetail::with('produk')->where(['pembelian_id'=>$pembelian_id])->orderBy('id', 'desc')->get();

        //  dd($insert);


        if (is_null($get)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $get]);

    }



    /*------------  insert pembeliandetail ----------------*/
    public function insertpembeliandetail(Request $request)
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


        $insert = Pembeliandetail::create($input);

        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        $ambilproduk=Produk::find($input['produk_id']);
        if (is_null($ambilproduk)) {
            return Response::json(['status' => 0, 'message' => 'Produk Tidak ditemukan']);
        }
        $newkuantitas=$ambilproduk->stok+$input['kuantitas'];

        $updateprodukstok=$ambilproduk->update(['stok'=>$newkuantitas]);

        if (is_null($updateprodukstok)) {
            return Response::json(['status' => 0, 'message' => 'Update stok produk gagal']);
        }


        return Response::json(['status' => 1, 'message' => 'pembelian detail sukses..,stok produk berhasil ditambahkan',
            'data' => $insert]);

    }


    /*------------  edit pembeliandetail ----------------*/
    public function editpembeliandetail (Infokoperasi $infokoperasi,Request $request)
    {
        $session_key = $request->input('session_key');
        $pembeliandetail_id = $request->input('pembeliandetail_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Pembeliandetail::with('produk')->findorfail($pembeliandetail_id);

        if(!$info){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $info]);


    }



    /*------------  Update pembelian detail  ----------------*/
    public function updatepembeliandetail(Request $request)
    {
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }


        $input = $request->all();
        $pembeliandetail_id = $request->input('pembeliandetail_id');


        $update= Pembeliandetail::find($pembeliandetail_id);

        $finalstok=$input['kuantitas']-$update->kuantitas;
        //dd($finalstok);

        $update->update($input);

        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        $ambilproduk=Produk::find($input['produk_id']);
        if (is_null($ambilproduk)) {
            return Response::json(['status' => 0, 'message' => 'Produk Tidak ditemukan']);
        }
        $newkuantitas=$ambilproduk->stok+$finalstok;

        $updateprodukstok=$ambilproduk->update(['stok'=>$newkuantitas]);

        if (is_null($updateprodukstok)) {
            return Response::json(['status' => 0, 'message' => 'Update produk gagal']);
        }




        $updated=Pembeliandetail::find($pembeliandetail_id);

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $updated]);

    }


    /*------------  delete pembelian  detail ----------------*/
    public function deletepembeliandetail(Request $request)
    {
        $session_key = $request->input('session_key');
        $pembeliandetail_id = $request->input('pembeliandetail_id ');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }



        $del= Pembeliandetail::find($pembeliandetail_id);

            $ambilproduk=$del->produk_id;
            $ambilkuantitas=$del->kuantitas;

           $del->delete();

        if(!$del){
            return Response::json(['status' => 0, 'message' => 'delete gagal']);
        }


        $ambilproduk=Produk::find($ambilproduk);
        if (is_null($ambilproduk)) {
            return Response::json(['status' => 0, 'message' => 'Produk Tidak ditemukan']);
        }
        $newkuantitas=$ambilproduk->stok-$ambilkuantitas;

        $updateprodukstok=$ambilproduk->update(['stok'=>$newkuantitas]);

        if (is_null($updateprodukstok)) {
            return Response::json(['status' => 0, 'message' => 'Update stok produk gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses delete',
            'session_key' => $session_key, 'data' => $del]);


    }



    /*------------  get kategori  ----------------*/
    public function getkategori (Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $get= Kategori::where(['koperasi_id' => $koperasi_id])->orderBy('id', 'desc')->get();


        if (is_null($get)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $get]);

    }


    /*------------  insert kategori ----------------*/
    public function insertkategori(Request $request)
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
        $insert = Kategori::create($input);

        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $insert]);

    }


    /*------------  edit kategori ----------------*/
    public function editkategori (Request $request)
    {
        $session_key = $request->input('session_key');
        $kategori_id = $request->input('kategori_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Kategori::findorfail($kategori_id);

        if(!$info){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $info]);

    }



    /*------------  Update kategori ----------------*/
    public function updatekategori(Request $request)
    {
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $input = $request->all();
        $kategori_id = $request->input('kategori_id');

        $update= Kategori::find($kategori_id)->update($input);

        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $update]);

    }


    /*------------  delete kategori ----------------*/
    public function deletekategori(Request $request)
    {
        $session_key = $request->input('session_key');
        $kategori_id = $request->input('kategori_id');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }
        $del= Kategori::find($kategori_id)->delete();
        if(!$del){
            return Response::json(['status' => 0, 'message' => 'delete gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses delete',
            'session_key' => $session_key, 'data' => $del]);

    }





    /*------------  get produk  ----------------*/
    public function getproduk (Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $get= Produk::with('suplier')->where(['koperasi_id' => $koperasi_id])->orderBy('id', 'desc')->get();


        if (is_null($get)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $get]);

    }


    /*------------  insert produk ----------------*/
    public function insertproduk(Request $request)
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

            $path = public_path('images/produk/' . $filename);
            $path_thumb = public_path('images/produk/' . $filename_thumb);

            Image::make($foto->getRealPath())->resize(300, 300)->save($path);
            Image::make($foto->getRealPath())->resize(60, 60)->save($path_thumb);

            $foto->image = $filename;
            $input['foto'] = $foto->image;
        }
        else {

            $input['foto'] ='no_image.png';
        }



        $insert = Produk::create($input);

        if (is_null($insert)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $insert]);

    }



    /*------------  edit produk ----------------*/
    public function editproduk (Infokoperasi $infokoperasi,Request $request)
    {
        $session_key = $request->input('session_key');
        $produk_id = $request->input('produk_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Produk::findorfail($produk_id);

        if(!$info){
            return Response::json(['status' => 0, 'message' => 'Update gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $info]);

    }



    /*------------  Update produk ----------------*/
    public function updateproduk(Request $request)
    {
        $session_key = $request->input('session_key');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $produk_id = $request->input('produk_id');
        $info=Produk::findorfail($produk_id);

        $input = $request->all();

        $foto = Input::file('foto');

        if(!is_null($foto)) {

            if(file_exists(public_path('images/produk/'.$info->foto))) {

                File::delete(public_path('images/produk/'.$info->foto));
                File::delete(public_path('images/produk/thumb_'.$info->foto));
            }

            $filename = date('YmdHis') . '.' . $foto->getClientOriginalExtension();
            $filename_thumb = 'thumb_'.date('YmdHis') . '.' . $foto->getClientOriginalExtension();

            $path = public_path('images/produk/' . $filename);
            $path_thumb = public_path('images/produk/' . $filename_thumb);

            Image::make($foto->getRealPath())->resize(300, 300)->save($path);
            Image::make($foto->getRealPath())->resize(60, 60)->save($path_thumb);

            $info->foto = $filename;
        }
        $input['foto'] = $info->foto;

        $update= Produk::find($produk_id)->update($input);

        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $update]);

    }


    /*------------  delete produk ----------------*/
    public function deleteproduk(Request $request)
    {
        $session_key = $request->input('session_key');
        $produk_id = $request->input('produk_id');
        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $info=Produk::findorfail($produk_id);

        if(file_exists('public/images/produk/'.$info->foto)) {

            File::delete('public/images/produk/'.$info->foto);
            File::delete('public/images/produk/thumb_'.$info->foto);
        }

        $del= Produk::find($produk_id)->delete();

        if(!$del){
            return Response::json(['status' => 0, 'message' => 'delete gagal']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses delete',
            'session_key' => $session_key, 'data' => $del]);

    }




    /*------------  get idnamaanggota  ----------------*/
    public function getidnamaanggota(Request $request)
    {
        $session_key = $request->input('session_key');
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $anggotakoperasi = Anggotakoperasi::where(['koperasi_id' => $koperasi_id])->orderby('nama','asc')->get(['id','nama']);

        if (is_null($anggotakoperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan', 'session_key' => $session_key,
            'data' => $anggotakoperasi]);

    }


    /*------------  get tahun operasi aktif  ----------------*/
    public function gettahunoperasiaktif (Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
        $tahunoperasi = Tahunoperasi::where(['koperasi_id' => $koperasi_id,'status'=>'Aktif'])->select('id','tanggalmulai','tanggalselesai','status')->first();


        if (is_null($tahunoperasi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $tahunoperasi]);

    }



    /*------------ Kalkulasi SHU  ----------------*/
    public function kalkulasishu(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $tahunoperasiaktif          =Tahunoperasi::where(['koperasi_id'=>$koperasi_id,'status'=>'Aktif'])->first();

        $jurnal                     =Jurnalkoperasi::where(['koperasi_id'=>$koperasi_id,'tahunoperasi_id'=>$tahunoperasiaktif->id])->first();
        if (is_null($jurnal)) {
            return Response::json(['status' => 0, 'message' => 'Jurnal Tidak Ditemukan.Apakah anda yakin sudah mengkalkulasi Jurnal?']);
        }
        $komponenshujasaanggota     =Komponenshu::where(['koperasi_id'=>$koperasi_id,'tahunoperasi_id'=>$tahunoperasiaktif->id,'tipekomponenshu_id'=>2])->first();
        $komponenshujasamodal       =Komponenshu::where(['koperasi_id'=>$koperasi_id,'tahunoperasi_id'=>$tahunoperasiaktif->id,'tipekomponenshu_id'=>3])->first();

        $totalsimpanan=$jurnal->totalsimpanan;
        $totaltransaksi=$jurnal->penjualan;

        $jasaanggotanyajadi =$komponenshujasaanggota->persentase/100*$jurnal->lababersih;

        $jasamodaljadi      =$komponenshujasamodal->persentase/100*$jurnal->lababersih;

        $tahunoperasiaktifid=$tahunoperasiaktif->id;

        $cekshutahunini=Shu::where(['koperasi_id'=>$koperasi_id,'tahunoperasi_id'=>$tahunoperasiaktifid])->first();
        if (!is_null($cekshutahunini)) {
            return Response::json(['status' => 0, 'message' => 'SHU sudah di kalkulasi,silahkan reset terlebih dahulu untuk melakukan kalkulasi SHU ']);
        }


        // dd($koperasi_id,$tahunoperasiaktifid,$jasaanggotanyajadi,$totaltransaksi,$jasamodaljadi,$totalsimpanan);

        // $jal=DB::select('CALL sp_getSHU(paramKoperasiId,paramTahunOperasi,paramJasaAnggota,paramTransaksiKoperasi,paramJasaModal,paramTotalSimpanan)',array($koperasi_id,$tahunoperasiaktif,$komponenshujasaanggota,$komponenshujasamodal,$totalsimpanan));

        $jal=DB::select('call sp_getSHU(?,?,?,?,?,?)',array($koperasi_id,$tahunoperasiaktifid,$jasaanggotanyajadi,$totaltransaksi,$jasamodaljadi,$totalsimpanan));

        //$jal=DB::select('call sp_getSHU('.$koperasi_id.','.$tahunoperasiaktif.','.$komponenshujasaanggota.','.$komponenshujasamodal.','.$totalsimpanan.')');

        $shu=Shu::where(['koperasi_id'=>$koperasi_id,'tahunoperasi_id'=>$tahunoperasiaktifid])->get();


        if (is_null($shu)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $shu]);

    }


    /*------------ getshuaktif ----------------*/
    public function getshuaktif(Request $request)
    {
        $session_key = $request->input('session_key');


        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $tahunoperasiaktif  =Tahunoperasi::where(['koperasi_id'=>$koperasi_id,'status'=>'Aktif'])->first();

        $myshu=Shu::with('anggotakoperasi')->where(['koperasi_id'=>$koperasi_id,'tahunoperasi_id'=>$tahunoperasiaktif->id])->get();


        if (is_null($myshu)) {
            return Response::json(['status' => 0, 'message' => 'SHU belum dikalkulasi']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $myshu]);

    }



    /*------------  get shu where  ----------------*/
    public function getshuwhere(Request $request)
    {
        $session_key = $request->input('session_key');
        $tahunoperasi_id = $request->input('tahunoperasi_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        if($tahunoperasi_id!=''){

            $shu = Shu::with('tahunoperasi')->with('anggotakoperasi')->where(['koperasi_id' => $koperasi_id,'tahunoperasi_id'=>$tahunoperasi_id])->get();
        }

        else{

            $shu = Shu::with('tahunoperasi')->with('anggotakoperasi')->where(['koperasi_id' => $koperasi_id])->orderBy('id', 'desc')->get();

        }


        if (is_null($shu)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $shu]);
    }


    /*------------ getshu ----------------*/
    public function resetshu(Request $request)
    {
        $session_key = $request->input('session_key');


        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $tahunoperasiaktif  =Tahunoperasi::where(['koperasi_id'=>$koperasi_id,'status'=>'Aktif'])->first();

        $myshu=Shu::where(['koperasi_id'=>$koperasi_id,'tahunoperasi_id'=>$tahunoperasiaktif->id])->delete();

        if (is_null($myshu)) {
            return Response::json(['status' => 0, 'message' => 'SHU belum dikalkulasi']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $myshu]);

    }





    /*------------  get Pesan Kementerian ----------------*/
    public function getnewpesankementerian (Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
        $pesan = Pesankementeriankoperasi::where(['koperasi_id' => $koperasi_id,'status'=>'Belum Dibaca'])->orderby('id','desc')->get();

        if (is_null($pesan)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $pesan]);
    }



    /*------------  get all Pesan Kementerian ----------------*/
    public function getallpesankementerian (Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
        $pesan = Pesankementeriankoperasi::where(['koperasi_id' => $koperasi_id])->orderby('id','desc')->get();

        if (is_null($pesan)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $pesan]);

    }


    /*------------  get detail Pesan Kementerian ----------------*/
    public function getdetailpesankementerian (Request $request)
    {
        $session_key = $request->input('session_key');
        $pesankementeriankoperasi_id = $request->input('pesankementeriankoperasi_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
        $pesan = Pesankementeriankoperasi::find($pesankementeriankoperasi_id);

        if (is_null($pesan)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $pesan]);

    }



    /*------------  Update status pesan kementerian ----------------*/
    public function updatestatuspesan(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $pesankementeriankoperasi_id = $request->input('pesankementeriankoperasi_id');

        $update= Pesankementeriankoperasi::where(['id'=>$pesankementeriankoperasi_id])->update(['status'=>'Sudah Dibaca']);

        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $update]);
    }


    /*------------  delete pesan kementerian ----------------*/
    public function deletepesankementerian(Request $request)
    {
        $session_key = $request->input('session_key');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $pesankementerian_id = $request->input('pesankementerian_id');

        $del= Pesankementeriankoperasi::find($pesankementerian_id)->delete();

        if (is_null($del)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $del]);
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


    /*------------  get Training kementrian where ----------------*/
    public function gettrainingkementerianwhere(Request $request)
    {
        $session_key = $request->input('session_key');
        $trainingkementerian_id = $request->input('trainingkementerian_id');
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $training = Trainingkementerian::find($trainingkementerian_id);


        if (is_null($training)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $training]);

    }


    /*------------  show peserta trsining kementrian ----------------*/
    public function showpesertatrainingkementerian(Request $request)
    {
        $session_key = $request->input('session_key');
        $trainingkementerian_id = $request->input('trainingkementerian_id');

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $anggota = Bookingtrainingkementerian::with('anggotakoperasi')->where('trainingkementerian_id',$trainingkementerian_id)->orderby('bookingtrainingkementerian.id', 'desc')->get();


        if (is_null($anggota)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $anggota]);

    }

    /*------------  Uupdatepesertatraining ----------------*/
    public function updatepesertatrainingkementerian(Request $request)
    {
        $session_key = $request->input('session_key');
        $trainingkementerian_id = $request->input('trainingkementerian_id');
        $anggotakoperasi_id = $request->input('anggotakoperasi_id');
        $status = $request->input('status');



        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $update= Bookingtrainingkementerian::where(['trainingkementerian_id'=>$trainingkementerian_id,'anggotakoperasi_id'=>$anggotakoperasi_id])->update(['status'=>$status]);

        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $update]);
    }


    /*------------  deletepesertatrainingkementerian ----------------*/
    public function deletepesertatrainingkementerian(Request $request)
    {
        $session_key = $request->input('session_key');
        $bookingtrainingkementerian_id = $request->input('bookingtrainingkementerian_id');
        // $anggotakoperasi_id = $request->input('anggotakoperasi_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $del= Bookingtrainingkementerian::find($bookingtrainingkementerian_id)->delete();

        if (is_null($del)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $del]);
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


    /*------------  get seminar kementerian where ----------------*/

    public function getseminarkementerianwhere(Request $request)
    {
        $session_key = $request->input('session_key');
        $seminarkementerian_id = $request->input('seminarkementerian_id');
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $seminar = Seminarkementerian::find($seminarkementerian_id);


        if (is_null($seminar)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $seminar]);

    }


    /*------------  show peserta seminar kementrian ----------------*/
    public function showpesertaseminarkementerian(Request $request)
    {
        $session_key = $request->input('session_key');
        $seminarkementerian_id = $request->input('seminarkementerian_id');

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $anggota = Bookingseminarkementerian::with('anggotakoperasi')->where('seminarkementerian_id',$seminarkementerian_id)->orderby('bookingseminarkementerian.id', 'desc')->get();


        if (is_null($anggota)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $anggota]);

    }

    /*------------  Uupdatestatuspesertatraining ----------------*/
    public function updatepesertaseminarkementerian(Request $request)
    {
        $session_key = $request->input('session_key');
        $seminarkementerian_id = $request->input('seminarkementerian_id');
        $anggotakoperasi_id = $request->input('anggotakoperasi_id');
        $status = $request->input('status');



        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $update= Bookingseminarkementerian::where(['seminarkementerian_id'=>$seminarkementerian_id,'anggotakoperasi_id'=>$anggotakoperasi_id])->update(['status'=>$status]);

        if (is_null($update)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $update]);
    }


    /*------------  deletepesertatrainingkementerian ----------------*/
    public function deletepesertaseminarkementerian(Request $request)
    {
        $session_key = $request->input('session_key');
        $bookingseminarkementerian_id = $request->input('bookingseminarkementerian_id');


        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $del= Bookingseminarkementerian::find($bookingseminarkementerian_id)->delete();
        if (is_null($del)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $del]);
    }




    /*------------  get report transaksi----------------*/

    public function getreporttransaksi(Request $request)
    {
        $session_key = $request->input('session_key');
        /* $tanggalmulai= $request->input('tanggalmulai');
         $tanggalakhir= $request->input('tanggalakhir'); */

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }


        /*    if($tanggalmulai==''&& $tanggalakhir=='') { */

        $transaksi = Transaksi::with('anggotakoperasi')->where('koperasi_id',$koperasi_id)->orderBy('id', 'desc')->get();

        /*    }


            if($tanggalmulai!='' && $tanggalakhir!='') {

                $transaksi = Transaksi::where('koperasi_id',$koperasi_id)
                    ->whereBetween('tanggal', array($tanggalmulai, $tanggalakhir))
                    ->orderBy('id', 'desc')->get();

            }

          elseif($tanggalmulai!=''&& $tanggalakhir==''){


                $transaksi = Transaksi::where('koperasi_id',$koperasi_id)
                    ->where('tanggal','>=',$tanggalmulai)
                    ->orderBy('id', 'desc')->get();

            } */





        if (is_null($transaksi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $transaksi]);

    }




    /*------------  get report transaksi----------------*/

    public function getreporttransaksitoday(Request $request)
    {
        $session_key = $request->input('session_key');
        /* $tanggalmulai= $request->input('tanggalmulai');
         $tanggalakhir= $request->input('tanggalakhir'); */

        $carbon= Carbon::now();
        $tanggal=$carbon->format('Y-m-d'.' 00:00:00');
        //dd($tanggal);

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }


        /*    if($tanggalmulai==''&& $tanggalakhir=='') { */

        $transaksi = Transaksi::with('anggotakoperasi')->where(['koperasi_id'=>$koperasi_id])->where('tanggal', '>=',$tanggal)->orderBy('id', 'desc')->get();

        /*    }


            if($tanggalmulai!='' && $tanggalakhir!='') {

                $transaksi = Transaksi::where('koperasi_id',$koperasi_id)
                    ->whereBetween('tanggal', array($tanggalmulai, $tanggalakhir))
                    ->orderBy('id', 'desc')->get();

            }

          elseif($tanggalmulai!=''&& $tanggalakhir==''){


                $transaksi = Transaksi::where('koperasi_id',$koperasi_id)
                    ->where('tanggal','>=',$tanggalmulai)
                    ->orderBy('id', 'desc')->get();

            } */





        if (is_null($transaksi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $transaksi]);

    }



    /*------------  get report range----------------*/

    public function getreporttransaksirange(Request $request)
    {
        $session_key = $request->input('session_key');
         $tanggalmulai= $request->input('tanggalmulai');
         $tanggalakhir= $request->input('tanggalakhir');


        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

            if($tanggalmulai!='' && $tanggalakhir!='') {

                $transaksi = Transaksi::with('anggotakoperasi')->where('koperasi_id',$koperasi_id)
                    ->whereBetween('tanggal', array($tanggalmulai, $tanggalakhir))
                    ->orderBy('id', 'desc')->get();
            }

          elseif($tanggalmulai!=''&& $tanggalakhir==''){

                $transaksi = Transaksi::with('anggotakoperasi')->where('koperasi_id',$koperasi_id)
                    ->where('tanggal','>=',$tanggalmulai)
                    ->orderBy('id', 'desc')->get();

            }



        if (is_null($transaksi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $transaksi]);

    }



    /*------------  get report transaksi detail----------------*/

    public function getreporttransaksidetail(Request $request)
    {
        $session_key = $request->input('session_key');
        $transaksi_id = $request->input('transaksi_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $transaksi = Transaksidetail::with('produk')->where('transaksi_id',$transaksi_id)->orderBy('id', 'desc')->get();

        if (is_null($transaksi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $transaksi]);

    }




    /* PENJUALAN VIA KOPERASI*/




    /*------------  Get kategori  ----------------*/
    public function getprodukbykategori(Request $request)
    {
        $session_key = $request->input('session_key');

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $kategori= Kategori::with('produk')->where('koperasi_id', $koperasi_id)->orderby('kategori.id','desc')->get();

        if (is_null($kategori)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key, 'data' => $kategori]);

    }

    /*------------  Get produk where   ----------------*/
    public function getprodukwhere(Request $request)
    {
        $session_key = $request->input('session_key');
        $kategori_id = $request->input('kategori_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $kategori= Produk::where('kategori_id', $kategori_id)->orderby('id','desc')->get();

        if (is_null($kategori)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $kategori]);

    }

    /*------------  Get produk where   ----------------*/
    public function getprodukdetail(Request $request)
    {
        $session_key = $request->input('session_key');
        $produk_id = $request->input('produk_id');

        if ($this->checkIfSessionkoperasiExpired($session_key) == false) {
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $kategori= Produk::find($produk_id);

        if (is_null($kategori)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $kategori]);

    }


    //------------------insert transaksi ------------------------

    public function inserttransaksi(Request $request)
    {
        $session_key = $request->input('session_key');


        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $tahunoperasiaktif  =Tahunoperasi::where(['koperasi_id'=>$koperasi_id,'status'=>'Aktif'])->first();

        $input['koperasi_id'] = $koperasi_id;
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

        $koperasi_id = $this->getKoperasiId($session_key);

        if (is_null($koperasi_id)) {
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
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $transaksidetail_id = $request->input('transaksidetail_id');

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
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }



        $transaksidetail_id = $request->input('transaksidetail_id');
        $input['tanggal'] = Carbon::now();
        $input['produk_id'] = $request->input('produk_id');
        $input['kuantitas']= $request->input('kuantitas');
        $kuantitas=$input['kuantitas'];

        $produk=Produk::find($input['produk_id']);
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




    /*------------   ----------------*/
    public function deletedetailtemp(Request $request)
    {
        $session_key = $request->input('session_key');
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
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




    /*------------ pembayaran  ----------------*/
    public function pembayaran (Request $request)
    {
        $session_key = $request->input('session_key');
        $refnumberssp = $request->input('refnumberssp');
        $transaksi_id = $request->input('transaksi_id');

        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)) {
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }


        $transaksi=Transaksi::find($transaksi_id);
        if (is_null($transaksi)) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        if($refnumberssp=='')
        {
            $transaksi->update(['status'=>'Dibayar','metode'=>'cash']);

        }
        elseif ($refnumberssp !='')
        {
            $transaksi->update(['status'=>'Dibayar','metode'=>'SSP','refnumberssp'=>$refnumberssp]);

        }


        if (!$transaksi) {
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        $newstatustrans=Transaksi::find($transaksi_id);


        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'session_key' => $session_key,'data'=>$newstatustrans]);

    }


















}
