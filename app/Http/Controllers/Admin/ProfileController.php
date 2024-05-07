<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RequestQuote;

class ProfileController extends Controller
{
    public function getProfile(){
        return view('admin.profile'); 
    }
}