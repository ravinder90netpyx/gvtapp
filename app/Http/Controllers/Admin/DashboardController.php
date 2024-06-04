<?php
namespace App\Http\Controllers\Admin;

#use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RequestQuote;
class DashboardController extends Controller{
	public function __construct(){
        #$this->middleware('auth:web');
    }

    public function index(){  
        $folder = $this->folder; 	
    	return view($folder['folder_name'].'.dashboard', compact('folder'));
    }
}
