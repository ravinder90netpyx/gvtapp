<?php
namespace App\Http\Controllers\Admin;

use App\Models\Tenant_Variant as DefaultModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\SUpport\Facades\Auth;
use URL;
use Carbon\Carbon;
use App\Jobs\WhatsappAPI;
use Nnjeim\World\World;
use Nnjeim\World\Models\Country;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TenantController extends Controller{
    public $module = array(
        'module_view' => 'tenant',
        'module_route' => 'tenant',
        'permission_group' => 'user_roles',
        'main_heading' => 'Tenant',
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
        $perpage = $request->perpage ?? $module['default_perpage'];
        if(!$request->perpage && !empty($request->cookie('perpage'))) $perpage = $request->cookie('perpage');
        
        $model_get = $model->where('isfamily','0');
        $auth_user = Auth::user();
        $roles = $auth_user->roles()->pluck('id')->toArray();
        if(!in_array(1, $roles)){
            $organization_id = $auth_user->organization_id;
            $model_get = $model_get->where('organization_id', $organization_id);
        }
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

    public function create(DefaultModel $model){
        $module = $this->module;
        $folder = $this->folder;
        $action = URL::route($module['main_route'].'.store');
        $title_shown = 'Add '.$module['main_heading'];
        $method = 'POST';
        $mode = 'insert';
        $indiaStates = DB::connection('mysqlw')
        ->table('city')->select('District')->where('CountryCode','IND')->distinct()
        ->pluck('District');

        return view($module['main_view'].'.cred2', compact('module', 'model', 'indiaStates', 'action', 'method', 'mode', 'folder', 'title_shown'));
    }

    public function store(Request $request, DefaultModel $model){
        $module = $this->module;
        $auth_user = Auth::user();
        $roles = $auth_user->roles()->pluck('id')->toArray();
        $request->validate([
            'organization_id' =>in_array(1,$roles)? 'required':'nullable',
            'name' => 'required',
            'age' => 'required|numeric',
            'gender' => 'required|in:male,female,other',
            'mobile_number' => 'required',
            'photo' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'document.*' => 'required|file|mimes:pdf,txt,doc,docx|max:2048',
            'locality' => 'required',
            'city' => 'required',
            'state' => 'required',
            'pincode' => 'required|numeric'
        ]);

        $variant= [];
        if(in_array(1,$roles)){
            $variant['organization_id'] = $request->input('organization_id');
        } else{
            $variant['organization_id'] = $auth_user->organization_id;
        }
        $variant['name'] = $request->input('name');
        $variant['age'] = $request->input('age');
        $variant['gender'] = $request->input('gender');
        $variant['mobile_number'] = $request->input('mobile_number');
        $variant['email'] = $request->input('email') ?? null;
        $variant['locality'] = $request->input('locality');
        $variant['city'] = $request->input('city');
        $variant['state'] = $request->input('state');
        $variant['pincode'] = $request->input('pincode');

        if($request->hasFile("photo")){
            $image = $request->file("photo");
            $imageName= pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME).time().'.'.$request->file("photo")->getClientOriginalExtension();
            $image->move(public_path('upload/tenant'), $imageName);
            $variant['photo'] = $imageName;
            $variant['photo_name'] = $image->getClientOriginalName();
        }
        if($request->hasFile("document")){
            $imageDocName =[];
            $image_name = [];
            foreach($request->file('document') as $image){
                // $image = $request->file("document");
                $imageName= pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME).time().'.'.$image->getClientOriginalExtension();
                $image->move(public_path('upload/tenant'), $imageName);
                $imageDocName[] = $imageName;
                $image_name[] = $image->getClientOriginalName();
            }
            $variant['document'] = implode(',',$image_name);
            $variant['document_name'] = implode(',',$imageDocName);
        }
        $model->create($variant);

        return redirect()->route($module['main_route'].'.index')->with('success', $module['main_heading'].' created successfully.');
    }

    public function show($id, DefaultModel $model){
        $module = $this->module;
        $folder = $this->folder;
        $form_data = $model->find($id);
        $title_shown = 'Show '.$module['main_heading'];
        $mode = 'show';
        
        return view($module['main_view'].'.show', compact('form_data', 'model', 'module', 'folder', 'title_shown', 'mode', 'id'));
    }

    public function edit($id, DefaultModel $model){
        $module = $this->module;
        $folder = $this->folder;
        $form_data = $model->find($id);
        $action = URL::route($module['main_route'].'.update', $id);
        $title_shown = 'Edit '.$module['main_heading'];
        $method = 'PUT';
        $mode = 'edit';
        $indiaStates = DB::connection('mysqlw')
        ->table('city')->select('District')->where('CountryCode','IND')->distinct()
        ->pluck('District');

        return view($module['main_view'].'.cred2')->with(compact('form_data', 'model', 'module', 'action', 'method', 'indiaStates', 'mode', 'folder', 'title_shown', 'id'));
    }

    public function update(Request $request, $id, DefaultModel $model){
        $module = $this->module;
        $request->validate([
            'name' => 'required',
            'age' => 'required|numeric',
            'gender' => 'required|in:male,female,other',
            'mobile_number' => 'required',
            'locality' => 'required',
            'city' => 'required',
            'state' => 'required',
            'pincode' => 'required|numeric'
        ]);
        $variant=[];
        $variant['name'] = $request->input('name');
        $variant['age'] = $request->input('age');
        $variant['gender'] = $request->input('gender');
        $variant['mobile_number'] = $request->input('mobile_number');
        $variant['email'] = $request->input('email');
        $variant['locality'] = $request->input('locality');
        $variant['city'] = $request->input('city');
        $variant['state'] = $request->input('state');
        $variant['pincode'] = $request->input('pincode');
        $modelfind = $model->find($id);
        if($request->hasFile("photo")){
            $image = $request->file("photo");
            if ($modelfind->photo && file_exists(public_path('upload/tenant/' . $modelfind->photo))) {
                unlink(public_path('upload/tenant/' . $modelfind->photo));
            }
            $imageName= pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME).time().'.'.$request->file("photo")->getClientOriginalExtension();
            $image->move(public_path('upload/tenant'), $imageName);
            $variant['photo'] = $imageName;
            $variant['photo_name'] = $image->getClientOriginalName();
        }
        if($request->hasFile("document")){
            $doc = explode(',',$modelfind->document);
            foreach($doc as $d){
                if ($d && file_exists(public_path('upload/tenant/' . $d))) {
                    unlink(public_path('upload/tenant/' . $d));
                }
            }
            $imageDocName =[];
            $image_name = [];
            foreach($request->file('document') as $image){
                $imageName= pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME).time().'.'.$image->getClientOriginalExtension();
                $image->move(public_path('upload/tenant'), $imageName);
                $imageDocName[] = $imageName;
                $image_name[] = $image->getClientOriginalName();
            }
            $variant['document'] = implode(',',$imageDocName);
            $variant['document_name'] = implode(',',$image_name);
        }
        // dd($request->input());
        $modelfind->update($variant);
    
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

    public function send_msg(Request $request){
        $id = $request->input('id');
        $model = \App\Models\Tenant_Variant::find($id);
        if($model->isfamily == '0'){
            $destination = $model->mobile_number;
            $message ='';
            $helpers = new \App\Helpers\helpers();
            $org_id = $model->organization_id;
            $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','tenant_family'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();
            $global_var = new \App\Models\Custom_Global_Variable();
            $data = [
                'name' =>$model->name,
                'security' => $global_var->where([['variable_name','=','security'], ['status', '>','0'], ['delstatus', '<', '1'], ['organization_id','=',$org_id]])->first()?->value ?? '',
                'pvtatime' => $global_var->where([['variable_name','=','pvtatime'], ['status', '>','0'], ['delstatus', '<', '1'], ['organization_id','=',$org_id]])->first()?->value ?? '',
                'shiftingcharge' => $global_var->where([['variable_name','=','shiftingcharge'], ['status', '>','0'], ['delstatus', '<', '1'], ['organization_id','=',$org_id]])->first()?->value ?? '',
            ];
            
            $templ_json = $helpers->make_temp_json($temp->id, $data);

            dispatch( new WhatsappAPI($destination,$message, $org_id,$templ_json) )->onConnection('sync');
        } else{
            $destination = $model->mobile_number;
            $message ='';
            $helpers = new \App\Helpers\helpers();
            $org_id = $model->organization_id;
            $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','tenant_family'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();
            $global_var = new \App\Models\Custom_Global_Variable();
            $data = [
                'name' =>$model->name,
                'security' => $global_var->where([['variable_name','=','security'], ['status', '>','0'], ['delstatus', '<', '1'], ['organization_id','=',$org_id]])->first()?->value ?? '',
                'pvtatime' => $global_var->where([['variable_name','=','pvtatime'], ['status', '>','0'], ['delstatus', '<', '1'], ['organization_id','=',$org_id]])->first()?->value ?? '',
                'shiftingcharge' => $global_var->where([['variable_name','=','shiftingcharge'], ['status', '>','0'], ['delstatus', '<', '1'], ['organization_id','=',$org_id]])->first()?->value ?? '',
            ];
            
            $templ_json = $helpers->make_temp_json($temp->id, $data);
            dd($temp_json);

            dispatch( new WhatsappAPI($destination,$message, $org_id,$templ_json) )->onConnection('sync');
            return '';
        }
    }
}
