<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\User;

use App\Http\Requests\UserRequest;

use Auth;
use Input;
use File;
use Hash;

class UserController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function index()
    {

        $user=User::get();

        return view('admin.user.index', compact('user'));
    }



    public function destroy(User $user)
    {

        if(file_exists('public/images/user/'.$user->img)) {

            File::delete('public/images/user/'.$user->img);
            File::delete('public/images/user/thumb_'.$user->img);
        }
        $user->delete();

        return redirect('admin/user')->with('status', 'Data berhasil di hapus');

    }
}