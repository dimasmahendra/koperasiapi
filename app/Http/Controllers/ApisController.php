<?php

namespace App\Http\Controllers;



use Illuminate\Http\Request;
use Illuminate\Contracts\Hashing\Hasher;
use Carbon\Carbon;
use App\Http\Requests;

use Response;
use Hash;
use DB;


class ApisController extends Controller
{

    private function diencrypt($text = null)
    {
       // $text = 'SSP-Server-SecretKey';
        if (!is_null($text)) {

            $keypublic = "2d2d2d2d2d424547494e205055424c4943204b45592d2d2d2d2d0a4d4677774451594a4b6f5a496876634e41514542425141445377417753414a42414d396571572f546f595971697765692b316461626f396b44424771343338530a4b583930705958447633396148514c752b395036793232594735544a4962317a7475334c6367753068545057484a53726c394a32743830434177454141513d3d0a2d2d2d2d2d454e44205055424c4943204b45592d2d2d2d2d0a";

            $keypublic = pack("H*", $keypublic);

            openssl_public_encrypt($text, $newsource, $keypublic, OPENSSL_PKCS1_OAEP_PADDING);

            $chiper_hex = unpack("H*", $newsource);
        }
        return $chiper_hex[1];
    }



    private function didecrypt($chiper = null)
    {
        //$chiper =  $chiper_hex[1];
        if (!is_null($chiper)) {

            $text = pack("H*", $chiper);

            $keyprivate = "2d2d2d2d2d424547494e205253412050524956415445204b45592d2d2d2d2d0a4d4949424f51494241414a42414d396571572f546f595971697765692b316461626f396b44424771343338534b583930705958447633396148514c752b3950360a793232594735544a4962317a7475334c6367753068545057484a53726c394a32743830434177454141514a4152513078674b4a4a46414d516e7139663448756c0a65797747644d2b687834754674414a6b70384643414361704b6c532f44734851657258566c67517a7476636b544441494d4b695969684335617a5942586b52680a34514968414f7766472b6274597137496a36652b4a6d444248384a61467661475973526a354342574e6a68556b72336c41694541344e506c4c586944723562710a305447454c4d6f4b4f58334176356b516b436736544c653231654d504d386b43494243585777474e2b706f6e6335696f7a732b4c6f6d796f6f655a75714f2f390a794f44517a667946544d35564169426176717639306d7130634b474f6c6748585969554756323934356672396448387a662b4e594b5446316f5149675a7061390a3446437a477436556a726b434230414539614e665a6d452b36536a31727265627a36624d4d356b3d0a2d2d2d2d2d454e44205253412050524956415445204b45592d2d2d2d2d0a";

            $keyprivate = pack("H*", $keyprivate);

            $res = openssl_get_privatekey($keyprivate);

            openssl_private_decrypt($text, $newsource, $res, OPENSSL_PKCS1_OAEP_PADDING);

        }

        return $newsource;
    }


    private function diencryptoneway($text = null)
    {

        if (!is_null($text)) {

            $hex_key = '049d2dd14623b0a5cd69537f6feaee31897304f245f12fe58c8db8364fa0af68';
            $key = pack("H*", $hex_key);
            $iv = '';
            $chiper = mcrypt_encrypt(MCRYPT_ARCFOUR, $key, $text, MCRYPT_MODE_STREAM, $iv);
            $chiper_hex = unpack("H*", $chiper);

        }
        return $chiper_hex[1];
    }


    private function didecryptoneway($chiper = null)
    {

        if (!is_null($chiper)) {

            $hex_key = '049d2dd14623b0a5cd69537f6feaee31897304f245f12fe58c8db8364fa0af68';
            $text = pack("H*", $chiper);
            $key = pack("H*", $hex_key);
            $iv = '';
            $newsource=mcrypt_decrypt(MCRYPT_ARCFOUR, $key, $text, MCRYPT_MODE_STREAM, $iv);

        }

        return $newsource;
    }




    public function gettransaksixyz(Request $request)
    {
       $text1 = 'SSP-Server-SecretKey';

        $chiper= $request->input('key');
        $nofaktur= $request->input('nofaktur');

        if (is_null($chiper)||is_null($nofaktur)) {
            return Response::json(['status' => 0, 'message' => 'eror']);
        }

        $newsource= $this->didecrypt($chiper);

        if ($newsource==$text1)

        {

            $resultsp=DB::select('call sp_getTransaksiSSP(?)',[$nofaktur]);

             $text=json_encode($resultsp);

            $transaksi=$this->diencryptoneway($text);

            //$transaksi=$this->didecryptoneway($transaksi);

        }

        else if ($newsource!=$text1) {

            return Response::json(['status' => 0, 'message' => 'eror parsing data coy']);
        }

        if (is_null($transaksi)) {
            return Response::json(['status' => 0, 'message' => 'eror']);
        }

        return Response::json(['data' => $transaksi]);

    }


    /*-------------------decrypt-----------------------*/
    // $chiper_hex[1]= $this->diencrypt();
    //   dd($chiper_hex[1]);

    /*--------------decrypt----------------*/


    public function updatetransaksixyz(Request $request)
    {
        $text1 = 'SSP-Server-SecretKey';

        $chiper= $request->input('key');
        $nofaktur= $request->input('nofaktur');

        if (is_null($chiper)||is_null($nofaktur)) {
            return Response::json(['status' => 0, 'message' => 'eror']);
        }

        $newsource= $this->didecrypt($chiper);

        if ($newsource==$text1)

        {

            $resultsp=DB::select('call sp_updateTransaksiSSP(?)',[$nofaktur]);

            $text=json_encode($resultsp);

            $transaksi=$this->diencryptoneway($text);

            //$transaksi=$this->didecryptoneway($transaksi);

        }

        else if ($newsource!=$text1) {

            return Response::json(['status' => 0, 'message' => 'eror parsing data coy']);
        }

        if (is_null($transaksi)) {
            return Response::json(['status' => 0, 'message' => 'eror']);
        }

        return Response::json(['data' => $transaksi]);

    }








}