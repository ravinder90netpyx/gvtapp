<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $folder = array(
        #'role_id' => [1],
        #'current_role' => 'admin',
        'route_folder_name' => 'supanel',
        'folder_name' => 'admin',
        'module_name' => 'Admin',
        'permission_guard' => 'web'
    );

    public function __construct(){
    }
}