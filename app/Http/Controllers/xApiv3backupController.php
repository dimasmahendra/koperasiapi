<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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
use App\Adminkoementerian;


class Apiv3Controller extends Controller
{

    private function createOrUpdateSessionkoperasi($adminkoperasi_id = null){
        $session_key = null;
        if(!is_null($adminkoperasi_id)){
           // dd($adminkoperasi_id);
            $expired = Carbon::now()->addMinutes(30);
            $session = Sessionkoperasi::where(['adminkoperasi_id' => $adminkoperasi_id])->first();
            if(is_null($session)){
                Sessionkoperasi::create(['adminkoperasi_id' => $adminkoperasi_id,'status'=>1,'session_key'=>str_random(16)]);
                $session = Sessionkoperasi::where(['adminkoperasi_id' => $adminkoperasi_id])->first();
            }
            $session->update(['status' =>1,'expired_at' =>$expired]);
            if(!is_null($session)){
                $session_key = $session->session_key;
            }
        }
        return $session_key;
    }


    private function getAdminkoperasiId($session_key = null)
    {
        $adminkoperasi_id = null;
        if(!is_null($session_key)){
            $session = Sessionkoperasi::where(['session_key' => $session_key])->first();
            if(!is_null($session)) {
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
        if(!is_null($session_key)){
            $session = Sessionkoperasi::where(['session_key' => $session_key])->where('expired_at', '>=', Carbon::now())->first();
            if(!is_null($session)){
                $boolean = true;
                $this->createOrUpdateSessionkoperasi($session->adminkoperasi_id);
            }
        }
        return $boolean;
    }


    private function getKoperasiId($session_key = null)
    {
        $koperasi_id = null;
        if(!is_null($session_key)){
            $session = Sessionkoperasi::where(['session_key' => $session_key])->where('expired_at', '>=', Carbon::now())->first();
            if(!is_null($session)) {
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
        $ip_address=$request->input('ip_address');

        $adminkoperasi = Adminkoperasi::where(
            'username',$username)->first();

        if(is_null($adminkoperasi)) {

                return Response::json(['status' => 0, 'message' => 'Inputan Salah']);
            }



        $cekaktifuser=Sessionkoperasi::where(['adminkoperasi_id'=>$adminkoperasi->id])->first();

       if(!is_null($adminkoperasi)) {

           if($adminkoperasi->status=='Blocked') {

               return Response::json(['status' => 0, 'message' => 'Your account is blocked']);
           }
           else {

               if (Hash::check($password, $adminkoperasi->password)) {

                    if (is_null($cekaktifuser)) {

                       $session_key = $this->createOrUpdateSessionkoperasi($adminkoperasi->id);
                        $recallcekaktifuser=Sessionkoperasi::where(['adminkoperasi_id'=>$adminkoperasi->id])->update(['ip_address'=>$ip_address]);
                       Adminkoperasi::find($adminkoperasi->id)->update(['logingagal' => '0']);

                       return Response::json(['status' => 1, 'message' => 'adminkoperasi ditemukan', 'statuslogin'=>'1', 'session_key' => $session_key, 'data' => $adminkoperasi]);
                   }

                   elseif($cekaktifuser->status==1&&$ip_address!=$cekaktifuser->ip_address){
                       $cekexpired = Sessionkoperasi::where(['adminkoperasi_id' => $adminkoperasi->id])->where('expired_at', '>=', Carbon::now())->first();
                       if(!is_null($cekexpired)) {
                           return Response::json(['status' => 0,'statuslogin'=>'1',  'message' => 'lg ada yg onlen coy']);
                       }
                       else {
                           $session_key = $this->createOrUpdateSessionkoperasi($adminkoperasi->id);
                           $recallcekaktifuser=Sessionkoperasi::where(['adminkoperasi_id'=>$adminkoperasi->id])->update(['ip_address'=>$ip_address]);
                           Adminkoperasi::find($adminkoperasi->id)->update(['logingagal' => '0']);
                           return Response::json(['status' => 1, 'message' => 'adminkoperasi ditemukan', 'statuslogin'=>'1', 'session_key' => $session_key, 'data' => $adminkoperasi]);
                       }


                   }

                   elseif($cekaktifuser->status==1&&$ip_address==$cekaktifuser->ip_address) {
                       $session_key = $this->createOrUpdateSessionkoperasi($adminkoperasi->id);

                       $cekaktifuser->update(['ip_address'=>$ip_address]);
               
                       Adminkoperasi::find($adminkoperasi->id)->update(['logingagal' => '0']);
                       return Response::json(['status' => 1, 'message' => 'adminkoperasi ditemukan', 'statuslogin'=>'1', 'session_key' => $session_key, 'data' => $adminkoperasi]);

                   }
                   elseif ($cekaktifuser->status==0) {

                       $session_key = $this->createOrUpdateSessionkoperasi($adminkoperasi->id);

                       $cekaktifuser->update(['ip_address'=>$ip_address]);
                       //dd($cekaktifuser);
                       Adminkoperasi::find($adminkoperasi->id)->update(['logingagal' => '0']);

                       return Response::json(['status' => 1, 'message' => 'adminkoperasi ditemukan', 'statuslogin'=>'1', 'session_key' => $session_key, 'data' => $adminkoperasi]);
                   }

               }
               else {

                   if ($adminkoperasi->logingagal > 4 ) {
                       Adminkoperasi::find($adminkoperasi->id)->update(['status' => 'Blocked']);
                       return Response::json(['status' => 0, 'message' => 'You have 5 times incorrectly entering data,Your account is blocked']);

                   }

                   else {

                   $addgagal = $adminkoperasi->logingagal + 1;
                   Adminkoperasi::find($adminkoperasi->id)->update(['logingagal' => $addgagal]);

                   $countgagal = Adminkoperasi::find($adminkoperasi->id);

                   if ($countgagal->logingagal==5 ) {
                       return Response::json(['status' => 0,'message' =>'You have 5 times incorrectly entering data,Your account is blocked', 'countgagal' => $countgagal->logingagal]);
                   }
                       else {
                           return Response::json(['status' => 0, 'message' => 'erorr..email or password salah', 'countgagal' => $countgagal->logingagal]);

                       }
                       }
               }
           }


      }
       else if (empty($username)||  empty($password)) {

           return Response::json(['status' => 0, 'message' => 'eror...kosong']);
       }

        else {

            return Response::json(['status' => 0, 'message' => 'adminkoperasi tidak ditemukan']);
        }


    }


    /*------ Logoutkoperasi ---------- */

    public function logout(Request $request)
    {

        $session_key = $request->input('session_key');
        if($this->checkIfSessionkoperasiExpired($session_key) == false){
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        Sessionkoperasi::where(['session_key'=>$session_key])->first()->update(['status'=>0]);
        return Response::json(['status' => 1, 'message' => 'berhasil logout']);

    }



    /*------------  Get Profile  ----------------*/
    public function getprofile(Request $request)
    {
        $session_key = $request->input('session_key');

        if($this->checkIfSessionkoperasiExpired($session_key) == false){
            return Response::json(['status' => 0, 'message' => 'session key tidak ditemukan']);
        }

        $adminkoperasi_id = $this->getAdminkoperasiId($session_key);
        if (is_null($adminkoperasi_id)){
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }


        $profile = Adminkoperasi::where(['id' => $adminkoperasi_id])->with('koperasi')->first();
       // dd($profile);


        if(is_null($profile)){
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'Profile ditemukan',
            'session_key' => $session_key, 'data' => $profile]);

    }

    /*------------  Update Profile  ----------------*/
    public function updateprofile (Adminkoperasi $adminkoperasi,Request $request)
    {
        $session_key = $request->input('session_key');


        $adminkoperasi_id = $this->getAdminkoperasiId($session_key);
        if (is_null($adminkoperasi_id)){
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
        $adminkoperasi=Adminkoperasi::findorfail($adminkoperasi_id);
        $input = $request->all();
        $passwordlama=$request->input('passwordlama');
        $password=$request->input('password');


        if($passwordlama==''){

            $adminkoperasi->password;

        }
        elseif($passwordlama!='') {


            if(Hash::check($passwordlama, $adminkoperasi->password)) {
                if($password==''){
                    return Response::json(['status' => 0, 'message' => 'Password baru tdk boleh kosong']);
                }
                else {

                    $adminkoperasi->password = Hash::make($request->input('password'));

                }
            }


            else{

                $adminkoperasi->password;
                return Response::json(['status' => 0, 'message' => 'Password lama tdk cocok']);

            }
        }

        $input['foto'] = $adminkoperasi->foto;
        $input['password']=$adminkoperasi->password;

        $update= Adminkoperasi::find($adminkoperasi_id)->update($input);

        if(!$update){
            return Response::json(['status' => 0, 'message' => 'Update profile gagal']);
        }
        $updated= Adminkoperasi::find($adminkoperasi_id);
        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $updated]);
    }



    /*------------  Update image  ----------------*/
    public function updateimage (Adminkoperasi $adminkoperasi,Request $request)
    {
        $session_key = $request->input('session_key');

        $adminkoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($adminkoperasi_id)){
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }
        $adminkoperasi=Adminkoperasi::findorfail($adminkoperasi_id);

        $image = Input::file('foto');

        if(!is_null($image)) {

            if(file_exists(public_path('images/adminkoperasi/'.$adminkoperasi->foto))) {

                File::delete(public_path('images/adminkoperasi/'.$adminkoperasi->foto));
                File::delete(public_path('images/adminkoperasi/thumb_'.$adminkoperasi->foto));
            }


            $filename = date('YmdHis') . '.' . $image->getClientOriginalExtension();
            $filename_thumb = 'thumb_'.date('YmdHis') . '.' . $image->getClientOriginalExtension();

            $path = public_path('images/adminkoperasi/' . $filename);
            $path_thumb = public_path('images/adminkoperasi/' . $filename_thumb);

            Image::make($image->getRealPath())->resize(250, 250)->save($path);
            Image::make($image->getRealPath())->resize(60, 60)->save($path_thumb);
            $adminkoperasi->foto = $filename;


        }

        $input['foto'] = $adminkoperasi->foto;
        $update= Adminkoperasi::find($adminkoperasi_id)->update($input);


        if(!$update){
            return Response::json(['status' => 0, 'message' => 'Update profile gagal']);
        }
        $updated= Adminkoperasi::find($adminkoperasi_id);
        return Response::json(['status' => 1, 'message' => 'sukses update',
            'session_key' => $session_key, 'data' => $updated]);
    }




    /*------------  forgotpassword  ----------------*/
    public function forgotpassword (Password_resets $password_resets,Request $request)
    {
        $input['email']=$request->input('email');
        $input['token']= str_random(64);
        $input['expired_at'] = Carbon::now()->addMinutes(10);

        $findtoken= Password_resets::where(['email'=>$input['email'], 'status'=>'New'])->where('expired_at', '>=', Carbon::now())->first();

        if (!is_null($findtoken)){
            return Response::json(['status' => 0, 'message' => 'Anda sudah merequest password reset, tunggu 10 mnt']);
        }



        //dd($idlastinsert);

        $findemail = Adminkoperasi::where(['email' => $input['email']])->first();
        if (is_null($findemail)){
            return Response::json(['status' => 0, 'message' => 'User tdk ditemukan']);
        }
        $password_resets->create($input);
        $idlastinsert= DB::getPdo()->lastInsertId();

        $ambiltoken=Password_resets::findorfail($idlastinsert);

        $data = [
            'nama' => $findemail->nama,
            'email' => $findemail->email,
            'token' => $ambiltoken->token
        ];

        $sentmail= Mail::send('sentmail.maill',$data, function($message){
            $message->to(Input::get('email'))->subject('Lupa Password::Koperasi Modern');
        });

        if(!$sentmail){
            return Response::json(['status' => 0, 'message' => 'eror']);
        }

        return Response::json(['status' => 1, 'message' => 'sukses',
             ]);

    }



    /*------------  Reset Pssword  ----------------*/
    public function resetpassword (Adminkoperasi $adminkoperasi,Request $request)
    {
        $email=$request->input('email');

        $token=$request->input('token');

        $findtoken= Password_resets::where(['email'=>$email, 'token'=>$token,'status'=>'New'])->where('expired_at', '>=', Carbon::now())->first();

        if (is_null($findtoken)){
            return Response::json(['status' => 0, 'message' => 'Request tdk ditemukan atau token expired']);
        }


        $newpass= Hash::make($request->input('password'));

        $update= Adminkoperasi::where(['email'=>$findtoken->email])->update(['password'=>$newpass]);

        if(!$update){
            return Response::json(['status' => 0, 'message' => 'Update password  gagal']);
        }

        $findtoken->update(['status'=>'Success']);

        return Response::json(['status' => 1, 'message' => 'sukses update password']);
    }




    /*------------  Get Provinsi  ----------------*/
    public function getprovinsi(Request $request)
    {
        /*$session_key = $request->input('session_key');
        $adminkoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($adminkoperasi_id)){
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        } */

        $provinsi = Provinsi::get();

        if(is_null($provinsi)){
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

       /* $adminkoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($adminkoperasi_id)){
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        } */

        $kabupatenkota= Kabupatenkota::where('provinsi_id',$provinsi_id)->get();

        if(is_null($kabupatenkota)){
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $kabupatenkota]);

    }


    /*------------  Get kecamatan  ----------------*/
    public function getkecamatan(Request $request)
    {
        $kabupatenkota_id = $request->input('kabupatenkota_id');

        $kabupatenkota= Kecamatan::where('kabupatenkota_id',$kabupatenkota_id)->get();

        if(is_null($kabupatenkota)){
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
           'data' => $kabupatenkota]);

    }


    /*------------  Get kelurahan  ----------------*/
    public function getkelurahan(Request $request)
    {
        $kecamatan_id = $request->input('kecamatan_id');
        $kelurahan= Kelurahan::where('kecamatan_id',$kecamatan_id)->get(['id','kecamatan_id','nama']);

        if(is_null($kelurahan)){
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

        $adminkoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($adminkoperasi_id)){
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $kelurahan= Kelurahan::where('id',$kelurahan_id)->with('kecamatan.kabupatenkota.provinsi')->get();

        if(is_null($kelurahan)){
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
        $adminkoperasi_id = $this->getAngggotakoperasiId($session_key);
        if (is_null($adminkoperasi_id)){
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }


        $kementerian= Infokementerian::get();

        if(is_null($kementerian)){
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
        if (is_null($koperasi_id)){
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $infokoperasi= Infokoperasi::where(['koperasi_id'=>$koperasi_id])->get();


        if(is_null($infokoperasi)){
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $infokoperasi]);

    }


    /*------------  get Training  ----------------*/
    public function gettrainingkoperasi(Request $request)
    {
        $session_key = $request->input('session_key');
        $koperasi_id = $this->getKoperasiId($session_key);
        if (is_null($koperasi_id)){
            return Response::json(['status' => 0, 'message' => 'session key not found']);
        }

        $training= Trainingkoperasi::where(['koperasi_id'=>$koperasi_id])->get();


        if(is_null($training)){
            return Response::json(['status' => 0, 'message' => 'data tidak ditemukan']);
        }

        return Response::json(['status' => 1, 'message' => 'data ditemukan',
            'data' => $training]);

    }

































}
