<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function login(){
        if(Auth::check()) {           
            return redirect()->route('supanel.dashboard');
        }
    	return view('admin.user.login');
    }

    public function loginPost(Request $request){
    	$request->validate([
    		'email'=>'required',
    		'password'=>'required',
    	]);
    	$credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            return redirect()->route('supanel.dashboard' );
        }
        return redirect()->route('supanel.login')->with('error','Invalid Credential'); 
    }

    public function logout(){            
        Auth::logout();
        return redirect()->route('supanel.login');        
    }
}
