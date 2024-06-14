<?php
namespace App\Http\Controllers\Admin;

use App\Models\Roles;
use App\Models\Role_Has_Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use URL;
use Artisan;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\Auth;

class SiteuserroleController extends Controller{
    public $module = array(
        'module_view' => 'user_roles',
        'module_route' => 'user_roles',
        'permission_group' => 'user_roles',
        'main_heading' => 'User Role',
        'table_name' => 'roles',
        'table_column_prefix' => '',
        'not_deleteable' => [1],
        'not_deactivateable' => [1],
        'default_perpage' => 10
    );

    public function __construct(Request $request){
        $module = $this->module;
        $folder = $this->folder;

        $module['main_view'] = $folder['folder_name'].'.'.$module['module_view'];
        $module['main_route'] = $folder['route_folder_name'].'.'.$module['module_route'];

        $module['table_column_index'] = $module['table_column_prefix'].'id';
        $module['table_column_created'] = $module['table_column_prefix'].'created_at';
        $module['table_column_updated'] = $module['table_column_prefix'].'updated_at';

        $this->module = $module;

        $this->middleware('permission:'.$module['permission_group'].'.manage', ['only' => ['index']]);
        $this->middleware('permission:'.$module['permission_group'].'.add', ['only' => ['create','store']]);
        $this->middleware('permission:'.$module['permission_group'].'.edit', ['only' => ['edit','update']]);
        $this->middleware('permission:'.$module['permission_group'].'.delete', ['only' => ['destroy']]);

        $mode_construct = '';
        if(!empty($request->post('combined_action'))) $mode_construct = $request->post('combined_action');
        else if(!empty($request->segment(3))) $mode_construct = $request->segment(3);

        if(in_array($mode_construct, array('delete'))) $this->middleware('permission:'.$module['permission_group'].'.delete', ['only' => ['action', 'bulk']]);
    }

    public function index(Request $request, Roles $model){
        $carbon = new Carbon();
        $module = $this->module;
        $perpage = $request->perpage ?? $module['default_perpage'];
        if(!$request->perpage && !empty($request->cookie('perpage'))) $perpage = $request->cookie('perpage');

        $current_user_id = Auth::user()->id;
        $roles = Auth::user()->roles()->pluck('id')->toArray();
        if(!in_array('1', $roles)){
            $model_get = $model->where([ ['created_by', '=', $current_user_id] ])->latest();
        } else{
            $model_get = $model->latest();
        }
        
        $query = $request->get('query') ?? '';
        if($query!='') $model_get = $model_get->where($module['table_column_prefix'].'name', 'LIKE', '%'.$query.'%'); 

        $data = $model_get->paginate($perpage)->onEachSide(2);
        
        $title_shown = 'Manage User Roles';
        $folder = $this->folder;

        return view($module['main_view'].'.index', compact('data', 'carbon', 'module', 'perpage', 'folder', 'title_shown', 'query'))->with('i', ($request->input('page', 1) - 1) * $perpage);
    }

    public function create(){
        $module = $this->module;
        $folder = $this->folder;
        $action = URL::route($module['main_route'].'.index');
        $title_shown = 'Add Role';
        $method = 'POST';
        $mode = 'insert';

        return view($module['main_view'].'.cred', compact('module', 'action', 'method', 'mode', 'folder', 'title_shown'));
    }

    public function store(Request $request){
        $module = $this->module;
        $request->validate([
            $module['table_column_prefix'].'name' => 'required',
            $module['table_column_prefix'].'prev' => 'required',
        ]);

        $current_user_id = Auth::user()->id;
        $req_fields = $request->only(['name']);
        $req_fields = $req_fields+['created_by'=>$current_user_id];
        $role = Roles::create($req_fields);
        $id = $role->id;

        if(!empty($request->input('prev'))){
            foreach($request->input('prev') as $prek=>$prev){
                Role_Has_Permissions::create([ 'permission_id' => $prev, 'role_id' => $id ]);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        #Artisan::call('cache:clear');

        return redirect()->route($module['main_route'].'.index')->with('success', $module['main_heading'].' created successfully.');
    }

    public function edit($id){
        $module = $this->module;
        $folder = $this->folder;
        $form_data = Roles::find($id);
        $action = URL::route($module['main_route'].'.update', $id);
        $title_shown = 'Edit Role';
        $method = 'PUT';
        $mode = 'edit';

        return view($module['main_view'].'.cred')->with(compact('form_data', 'module', 'action', 'method', 'mode', 'folder', 'title_shown', 'id'));
    }

    public function update(Request $request, $id){
        $module = $this->module;
        $request->validate([
            $module['table_column_prefix'].'name' => 'required',
            $module['table_column_prefix'].'prev' => 'required',
        ]);
        $d=$request->all();
        $roles = Roles::find($id);

        
        $req_fields = $request->only(['name']);

        $roles->update($req_fields);

        Role_Has_Permissions::where([ ['role_id', '=', $id] ])->delete();
        
        $request_prev = $request->input('prev');
        if($id=='1'){
            // $merge_perm = array(
            //     'user_roles.manage' => '6',
            //     'user_roles.edit' => '8',
            //     'users.manage' => '11',
            //     'users.edit' => '13'
            // );
            $merge_perm=[];
            $request_prev = array_merge($request_prev, $merge_perm);
        }
        //echo '<pre>';print_R($request_prev);exit;

        if(!empty($request_prev)){
            foreach($request_prev as $prek=>$prev){
                Role_Has_Permissions::create([ 'permission_id' => $prev, 'role_id' => $id ]);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        #Artisan::call('cache:clear');
    
        return redirect()->route($module['main_route'].'.index')->with('success', $module['main_heading'].' updated successfully');
    }

    public function action($mode, $id, Roles $model){
        $module = $this->module;
        $err_type = $mode=='delete' ? 'info' : 'success';

        switch($mode){
            case 'delete':
                if(!in_array($id, $module['not_deleteable'])) $model->postDelete($id);
            break;
        }

        return redirect()->route($module['main_route'].'.index')->with($err_type, $module['main_heading'].' '.$mode.'d successfully');
    }

    public function bulk(Request $request, Roles $model){
        $module = $this->module;
        $token = $request->session()->token();
        $post = $request->post();

        if(!empty($post['btn_apply'])){
            if($token==$post['_token']){
                if(empty($post['combined_action'])){
                    return redirect()->route($module['main_route'].'.index')->with('error', 'Please select one action to perform.');
                } else if(is_null($request->post('row_check'))){
                    return redirect()->route($module['main_route'].'.index')->with('error', 'Please select atleast one checkbox to perform action.');
                } else{
                    $msg_type = $post['combined_action']=='delete' ? 'info' : 'success';

                    foreach($request->post('row_check') as $check_id){
                        switch($post['combined_action']){
                            case 'delete':
                                if(!in_array($check_id, $module['not_deleteable'])) $model->postDelete($check_id);
                            break;
                        }
                    }

                    return redirect()->route($module['main_route'].'.index')->with($msg_type, $module['main_heading']."s ".ucfirst(strtolower($post['combined_action'])).( (substr($post['combined_action'], -1)=='e') ? 'd' : 'ed' )." Successfully.");
                }
            }
        }
    }
}
