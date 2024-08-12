<?php
namespace App\Http\Controllers\Admin;

use App\Models\Templates as DefaultModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use URL;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class TemplatesController extends Controller{
    public $module = array(
        'module_view' => 'templates',
        'module_route' => 'templates',
        'permission_group' => 'templates',
        'main_heading' => 'Template',
        'default_perpage' => 10
    );

    public function __construct(Request $request){
        $module = $this->module;
        $folder = $this->folder;

        $module['main_view'] = $folder['folder_name'].'.'.$module['module_view'];
        $module['main_route'] = $folder['route_folder_name'].'.'.$module['module_route'];

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

    public function index(Request $request, DefaultModel $model){
        $carbon = new Carbon();
        $module = $this->module;
        $auth_user = Auth::user();
        $roles = $auth_user->roles()->pluck('id')->toArray();
        if(in_array(1, $roles)){
            return view($folder['folder_name'].'.dashboard', compact('folder'));
        } else{
            $perpage = $request->perpage ?? $module['default_perpage'];
            if(!$request->perpage && !empty($request->cookie('perpage'))) $perpage = $request->cookie('perpage');
            
            $model_get = $model;
            $organization_id = $auth_user->organization_id;
            $model_get = $model_get->where('organization_id', $organization_id);
            if($model->getDelStatusColumn()) $model_get = $model_get->where($model->getDelStatusColumn(), '<', '1');
            
            if($model->getSortOrderColumn()) $model_get = $model_get->orderBy($model->getSortOrderColumn(), 'ASC');
            else $model_get = $model_get->latest();
            
            $query = $request->get('query') ?? '';
            if($query!='') $model_get = $model_get->where('name', 'LIKE', '%'.$query.'%'); 

            $data = $model_get->paginate($perpage)->onEachSide(2);

            $title_shown = 'Manage '.$module['main_heading'].'s';
            $folder = $this->folder;

            return view($module['main_view'].'.index', compact('data', 'model', 'carbon', 'module', 'perpage', 'folder', 'title_shown', 'query'))->with('i', ($request->input('page', 1) - 1) * $perpage);
        }
    }

    public function create(DefaultModel $model){
        $module = $this->module;
        $folder = $this->folder;
        $auth_user = Auth::user();
        $roles = $auth_user->roles()->pluck('id')->toArray();
        if(in_array(1, $roles)){
            return view($folder['folder_name'].'.dashboard', compact('folder'));
        } else{
            $action = URL::route($module['main_route'].'.store');
            $title_shown = 'Add '.$module['main_heading'];
            $method = 'POST';
            $mode = 'insert';

            return view($module['main_view'].'.cred', compact('module', 'model', 'action', 'method', 'mode', 'folder', 'title_shown'));
        }
    }

    public function store(Request $request, DefaultModel $model){
        $auth_user = Auth::user();
        $roles = $auth_user->roles()->pluck('id')->toArray();
        $module = $this->module;
        $request->validate([
            'name' => 
            [
                'required',
                function($attribute, $value, $fail){
                    $request = Request();
                    $auth_user = Auth::user();
                    $roles = $auth_user->roles()->pluck('name','id')->toArray();
                    $org_id = empty($request->input('organization_id')) ? $auth_user->organization_id : $request->input('organization_id');
                    $models1 = \App\Models\Templates::where([['organization_id','=',$org_id], ['name', '=', $value], ['status', '>','0'], ['delstatus', '<', '1']])->first();

                }
            ],
            'template_id' => 'required',
            'organization_id' =>in_array(1,$roles)? 'required':'nullable'
        ]);

        if(!in_array(1, $roles)){
            $request->merge([ 'organization_id' => $auth_user->organization_id ] );
        }
        $params = json_encode($request->input('params'));
        $request->merge( ['params' => $params ]);

        $model->create($request->all());

        return redirect()->route($module['main_route'].'.index')->with('success', $module['main_heading'].' created successfully.');
    }

    /*public function show($id, DefaultModel $model){
        $module = $this->module;
        $folder = $this->folder;
        $form_data = $model->find($id);
        $title_shown = 'Show '.$module['main_heading'];
        $mode = 'show';
        
        return view($module['main_view'].'.show', compact('form_data', 'model', 'module', 'folder', 'title_shown', 'mode', 'id'));
    }*/

    public function edit($id, DefaultModel $model){
        $module = $this->module;
        $folder = $this->folder;
        $auth_user = Auth::user();
        $roles = $auth_user->roles()->pluck('id')->toArray();
        if(in_array(1, $roles)){
            return view($folder['folder_name'].'.dashboard', compact('folder'));
        } else{
            $form_data = $model->find($id);
            $action = URL::route($module['main_route'].'.update', $id);
            $title_shown = 'Edit '.$module['main_heading'];
            $dataArrays = json_decode($form_data->params);
            $dataArray = [];
            $count = 1;
            $dataArray =[];
            foreach($dataArrays as $dt){
                $dataArray[] = [ 
                    'id' => $count,
                    'name' => $dt->name,
                    'position' => $dt->position
                ];
                $count++;
            }
            $method = 'PUT';
            $mode = 'edit';

            return view($module['main_view'].'.cred')->with(compact('form_data', 'model', 'dataArray', 'module', 'action', 'method', 'mode', 'folder', 'title_shown', 'id'));
        }
    }

    public function update(Request $request, $id, DefaultModel $model){
        $module = $this->module;
        $request->validate([
            'name' => 'required',
            'template_id' => 'required'
        ]);
        $params = json_encode($request->input('params'));
        // dd($request->input('params'));
        $request->merge( ['params' => $params ]);
        $modelfind = $model->find($id);
        $modelfind->update($request->all());
    
        return redirect()->route($module['main_route'].'.index')->with('success', $module['main_heading'].' updated successfully');
    }

    public function action($mode, $id, DefaultModel $model){
        $module = $this->module;
        $err_type = $mode=='delete' ? 'info' : 'success';

        switch($mode){
            case 'activate':
                $model->postActivate($id);
            break;

            case 'deactivate':
                $model->postDeactivate($id);
            break;

            case 'delete':
                $model->postDelete($id);
            break;
        }

        return redirect()->route($module['main_route'].'.index')->with($err_type, $module['main_heading'].' '.$mode.'d successfully');
    }

    public function bulk(Request $request, DefaultModel $model){
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
                                $model->postActivate($check_id);
                            break;

                            case 'deactivate':
                                $model->postDeactivate($check_id);
                            break;

                            case 'delete':
                                $model->postDelete($check_id);
                            break;
                        }
                    }

                    return redirect()->route($module['main_route'].'.index')->with($msg_type, $module['main_heading']."s ".ucfirst(strtolower($post['combined_action'])).( (substr($post['combined_action'], -1)=='e') ? 'd' : 'ed' )." Successfully.");
                }
            }
        }
    }
}
