<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class ResetpasswordController extends Controller
{
    public function index()
    {

        return view('resetpass.reset');
    }


    public function resetpass(Request $request)
    {

        $input = $request->all();

        return redirect('admin/promotion')->with('status', 'Data berhasil di tambahkan');

        //  return redirect()->route('promotion.index');
    }
}
