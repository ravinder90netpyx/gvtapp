<?php
namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Organization;
use App\Models\Model_Has_Roles;
use App\Models\Model_Has_Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use URL;
use Artisan;
use Mail;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SiteuserController extends Controller{
    public $module = array(
        'module_view' => 'users',
        'module_route' => 'users',
        'permission_group' => 'users',
        'main_heading' => 'User',
        'table_name' => 'users',
        'table_column_prefix' => '',
        'not_deleteable' => ['1'],
        'not_deactivateable' => ['1'],
        'default_perpage' => 10
    );

    public function __construct(Request $request){
        $module = $this->module;
        $folder = $this->folder;

        $module['main_view'] = $folder['folder_name'].'.'.$module['module_view'];
        $module['main_route'] = $folder['route_folder_name'].'.'.$module['module_route'];

        $module['table_column_index'] = $module['table_column_prefix'].'id';
        $module['table_column_status'] = $module['table_column_prefix'].'status';
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

        if(in_array($mode_construct, array('activate', 'deactivate'))) $this->middleware('permission:'.$module['permission_group'].'.status', ['only' => ['action', 'bulk']]);
        if(in_array($mode_construct, array('delete'))) $this->middleware('permission:'.$module['permission_group'].'.delete', ['only' => ['action', 'bulk']]);
    }

    public function index(Request $request, User $model){
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
        if($query!='') $model_get = $model_get->where($module['table_column_prefix'].'first_name', 'LIKE', '%'.$query.'%')->orWhere($module['table_column_prefix'].'last_name', 'LIKE', '%'.$query.'%')->orWhere($module['table_column_prefix'].'email', 'LIKE', '%'.$query.'%')->orWhere($module['table_column_prefix'].'phone', 'LIKE', '%'.$query.'%');

        $data = $model_get->paginate($perpage)->onEachSide(2);
        $title_shown = 'Manage '.$module['main_heading'].'s';
        $folder = $this->folder;

        return view($module['main_view'].'.index', compact('data', 'carbon', 'module', 'perpage', 'folder', 'title_shown', 'query'))->with('i', ($request->input('page', 1) - 1) * $perpage);
    }

    public function create(User $model, Organization $orgModel){
        $module = $this->module;
        $folder = $this->folder;
        $action = URL::route($module['main_route'].'.index');
        $title_shown = 'Add '.$module['main_heading'];
        $method = 'POST';
        $mode = 'insert';

        $roles = Auth::user()->roles()->pluck('id')->toArray();
        $allow_add = true;
        if(!in_array('1', $roles)){
            $auth_org_id = Auth::user()->organization_id ?? '0';
            $usrs_added = $model->organization_user_count($auth_org_id);
            $max_allowed = $orgModel->find($auth_org_id)->users_allowed;
            if($usrs_added>=$max_allowed){
                $allow_add = false;
            }
        }

        if(!$allow_add){
            return redirect()->route($module['main_route'].'.index');
        }

        return view($module['main_view'].'.cred', compact('module', 'action', 'method', 'mode', 'folder', 'title_shown'));
    }

    public function store(Request $request, User $model, Organization $orgModel){
        $module = $this->module;
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'uname' => 'required|unique:'.$module['table_name'].','.$module['table_column_prefix'].'uname',
            'email' => 'required|unique:'.$module['table_name'].','.$module['table_column_prefix'].'email',
            'phone' => 'required|unique:'.$module['table_name'].','.$module['table_column_prefix'].'phone',
            'role-type' => 'required',
            'password' => 'required'
        ]);

        $current_user_id = Auth::user()->id;
        $rqst = $request->except(['role-type', 'password']);
        $rqst['password'] = Hash::make($request->input('password'));
        if(empty($request->input('channels'))) $rqst['channels'] = NULL;
        $rqst['uuid'] = Str::uuid();
        $rqst['created_by'] = $current_user_id;
        

        $roles = Auth::user()->roles()->pluck('id')->toArray();
        if(in_array('1', $roles)) $rqst['organization_id'] = $request->input('organization_id');
        else $rqst['organization_id'] = Auth::user()->organization_id;

        $allow_add = true;
        if(in_array('1', $roles)){
            $org_id = $rqst['organization_id'] ?? '0';
            $usrs_added = $model->organization_user_count($org_id);
            $max_allowed = $orgModel->find($org_id)->users_allowed;
            if($usrs_added>=$max_allowed){
                $allow_add = false;
            }
        }
        if(!$allow_add){
            return redirect()->route($module['main_route'].'.index')->with('error', 'Max users already created. Please increase limit.');
        }

        $ret = User::create($rqst);
        $iroll = $request->input('role-type');

        Model_Has_Roles::where([ ['model_id', '=', $ret->id], ['model_type', '=', 'App\Models\User'] ])->delete();
        foreach($iroll as $irl) Model_Has_Roles::create(['model_id' => $ret->id, 'role_id' => $irl, 'model_type' => 'App\Models\User']);

        if(!empty($request->input('prev'))){
            foreach($request->input('prev') as $prek=>$prev){
                Model_Has_Permissions::create([ 'permission_id' => $prev, 'model_type'=>'App\Models\User', 'model_id' => $ret->id ]);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        #Artisan::call('cache:clear');

        $crloginurl = URL::route('supanel.login');
        $eusername = $request->input('first_name').' '.$request->input('last_name');
        $euseremail = $request->input('email');
        $crpassword = $request->input('password');
        $mail_replace_data = array('name'=>$eusername, 'login_url'=>$crloginurl, 'email'=>$euseremail, 'password'=>$crpassword);
        $mail_params = array('name'=>$eusername, 'email'=>$euseremail);
        Mail::send('emails.userCreated', $mail_replace_data, function($message) use ($mail_params){
            $message->to($mail_params['email'], $mail_params['name'])->subject('Account is created ON API Integration Project');
        });

        return redirect()->route($module['main_route'].'.index')->with('success', $module['main_heading'].' created successfully.');
    }

    public function edit($id){
        $module = $this->module;
        $folder = $this->folder;
        $form_data = User::find($id);

        $current_user_id = Auth::user()->id;
        $roles = Auth::user()->roles()->pluck('id')->toArray();
        if(!in_array('1', $roles)){
            if($form_data->created_by!=$current_user_id) return redirect()->route($module['main_route'].'.index');
        }
        
        $action = URL::route($module['main_route'].'.update', $id);
        $title_shown = 'Edit '.$module['main_heading'];
        $method = 'PUT';
        $mode = 'edit';

        return view($module['main_view'].'.cred')->with(compact('form_data', 'module', 'action', 'method', 'mode', 'folder', 'title_shown', 'id'));
    }

    public function update(Request $request, $id){
        $module = $this->module;
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'uname' => 'required|unique:'.$module['table_name'].','.$module['table_column_prefix'].'uname,'.$id.','.$module['table_column_index'],
            'email' => 'required|unique:'.$module['table_name'].','.$module['table_column_prefix'].'email,'.$id.','.$module['table_column_index'],
            'phone' => 'required|unique:'.$module['table_name'].','.$module['table_column_prefix'].'phone,'.$id.','.$module['table_column_index'],
            'role-type' => 'required'
        ]);

        /*$request->input('channels')

        echo "<pre>";
        print_r($request->input('channels'));
        echo "</pre>";
        exit;*/

        $model = User::find($id);
        $rqst = $request->except(['role-type', 'password']);
        $ipass = $request->input('password');
        $iroll = $request->input('role-type');
        if(!empty($ipass)) $rqst['password'] = Hash::make($request->input('password'));
        if(empty($request->input('channels'))) $rqst['channels'] = NULL;
        $model->update($rqst);

        Model_Has_Roles::where([ ['model_id', '=', $id], ['model_type', '=', 'App\Models\User'] ])->delete();
        foreach($iroll as $irl) Model_Has_Roles::create(['model_id' => $id, 'role_id' => $irl, 'model_type' => 'App\Models\User']);

        Model_Has_Permissions::where([ ['model_type', '=', 'App\Models\User'], ['model_id', '=', $id] ])->delete();
        if(!empty($request->input('prev'))){
            foreach($request->input('prev') as $prek=>$prev){
                Model_Has_Permissions::create([ 'permission_id' => $prev, 'model_type'=>'App\Models\User', 'model_id' => $id ]);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        //Artisan::call('cache:clear');
    
        return redirect()->route($module['main_route'].'.index')->with('success', $module['main_heading'].' updated successfully');
    }

    public function action($mode, $id, User $model){
        $module = $this->module;
        $err_type = $mode=='delete' ? 'info' : 'success';

        switch($mode){
            case 'activate':
                if(!in_array($id, $module['not_deactivateable'])) $model->postActivate($id);
            break;

            case 'deactivate':
                if(!in_array($id, $module['not_deactivateable'])) $model->postDeactivate($id);
            break;

            case 'delete':
                if(!in_array($id, $module['not_deleteable'])) $model->postDelete($id);
            break;
        }

        return redirect()->route($module['main_route'].'.index')->with($err_type, $module['main_heading'].' '.$mode.'d successfully');
    }

    public function bulk(Request $request, User $model){
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
                            case 'activate':
                                if(!in_array($check_id, $module['not_deactivateable'])) $model->postActivate($check_id);
                            break;

                            case 'deactivate':
                                if(!in_array($check_id, $module['not_deactivateable'])) $model->postDeactivate($check_id);
                            break;

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
