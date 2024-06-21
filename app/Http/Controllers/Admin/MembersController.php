<?php
namespace App\Http\Controllers\Admin;

use App\Models\Members as DefaultModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use URL;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use App\Jobs\WhatsappAPI;
use App\Helpers\helpers;

class MembersController extends Controller{
    public $module = array(
        'module_view' => 'members',
        'module_route' => 'members',
        'permission_group' => 'members',
        'main_heading' => 'Member',
        'default_perpage' => 10,
        'group' => 'whatsapp_settings'
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
        if(in_array($mode_construct, array('reminder'))) $this->middleware('permission:'.$module['permission_group'].'.reminder', ['only' => ['action', 'bulk']]);
    }

    public function index(Request $request, DefaultModel $model){
        $carbon = new Carbon();
        $module = $this->module;
        $perpage = $request->perpage ?? $module['default_perpage'];
        if(!$request->perpage && !empty($request->cookie('perpage'))) $perpage = $request->cookie('perpage');
        
        $model_get = $model;
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
        if($query!='') $model_get = $model_get->where('name', 'LIKE', '%'.$query.'%')->orwhere('unit_number','LIKE','%'.$query.'%' )->orwhere('mobile_number','LIKE','%'.$query.'%' );

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

        return view($module['main_view'].'.cred2', compact('module', 'model', 'action', 'method', 'mode', 'folder', 'title_shown'));
    }

    public function store(Request $request, DefaultModel $model){
        $auth_user = \Illuminate\Support\Facades\Auth::user();
        $roles = $auth_user->roles()->pluck('id')->toArray();
        $module = $this->module;
        $request->validate([
            'name' => 'required|regex:/^[a-zA-Z\s]+$/',
            'unit_number' => 'required|integer|between:1,9999|unique:members,unit_number',
            'mobile_number' => 'required|unique:members,mobile_number',
            'charges_id' => 'required',
            'organization_id' =>in_array(1,$roles)? 'required':'nullable',
            'alternate_name_1'=> 'required',
            // 'alternate_name_2'=> 'required',
            'sublet_name'=> 'required',
            'alternate_number'=> 'required|unique:members,alternate_number'
        ]);

        if(!in_array(1,$roles)){
            $request->organization_id = $auth_user->organization_id;
        }
        if(!in_array(1, $roles)){
            $request->merge([ 'organization_id' => $auth_user->organization_id ]);
        }
        $request->merge([['mobile_number', trim($request->input('mobile_number'))], ['alternate_number', trim($request->input('alternate_number'))]]);
        $model->create($request->all());

        return redirect()->route($module['main_route'].'.index')->with('success', $module['main_heading'].' created successfully.');
    }

    public function edit($id, DefaultModel $model){
        $module = $this->module;
        $folder = $this->folder;
        $form_data = $model->find($id);
        $action = URL::route($module['main_route'].'.update', $id);
        $title_shown = 'Edit '.$module['main_heading'];
        $method = 'PUT';
        $mode = 'edit';

        return view($module['main_view'].'.cred2')->with(compact('form_data', 'model', 'module', 'action', 'method', 'mode', 'folder', 'title_shown', 'id'));
    }

    public function update(Request $request, $id, DefaultModel $model){
        $module = $this->module;
        $modelfind = $model->find($id);
        $request->validate([
            'name' => 'required',
            'unit_number' => 'required|integer|between:1,9999|unique:members,unit_number,'.$id,
            'mobile_number' => 'required|unique:members,mobile_number,'.$id,
            'charges_id' => 'required',
            'alternate_name_1'=> 'required',
            'sublet_name'=> 'required',
            'alternate_number'=> 'required|unique:members,alternate_number,'.$id
        ]);
        // dd($request->input());
        $request->merge([['mobile_number', trim($request->input('mobile_number'))], ['alternate_number', trim($request->input('alternate_number'))]]);
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
        // dd($request->input());
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

                            case 'reminder':
                                // dd(12);
                                $this->send_reminder_bulk($check_id);
                            break;
                        }
                    }

                    return redirect()->route($module['main_route'].'.index')->with($msg_type, $module['main_heading']."s ".ucfirst(strtolower($post['combined_action'])).( (substr($post['combined_action'], -1)=='e') ? 'd' : 'ed' )." Successfully.");
                }
            }
        }
    }

    public function send_reminder($mem_id){
        $helpers =new helpers();
        $module = $this->module;
        $member = \App\Models\Members::find($mem_id);
        $org_id = $member->organization_id;
        $dest_mob_no = $member->mobile_number;
        $charge = \App\Models\Charges::find($member->charges_id);
        $now = Carbon::now();
        // $month = $je_model->to_month;
        $day = $now->day;
        // if($day>12){
        //     $now = $now->addMonth();
        // }
        $curr_month = $now->format('Y-m');
        $je_model = \App\Models\Report::where([['member_id', '=',$mem_id],['month','=',$curr_month],['status','>','0'],['delstatus','<', '1']])->orderBy('id','DESC')->first();
        $now_date = $now->day(12);
        $date = Carbon::parse($now_date)->format('d-M-Y');
        // $day = $now->day;
        if(empty($je_model)){
            $data = [
                'name'=> $member->name,
                'mobile_number' => $member->mobile_number,
                'unit_no'=> $member->unit_number,
                'charge' => $charge->rate,
                'date' => $date
            ];
            $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','reminder'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();
            if($day>12){
                $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','overdue'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();
            }
            $templ_json = $helpers->make_temp_json($temp->id, $data);
            $message = '';
            $message = json_encode($message, true);
            dispatch( new WhatsappAPI($dest_mob_no,$message, $org_id,$templ_json) )->onConnection('sync');
            return redirect()->route($module['main_route'].'.index')->with('success', 'Message send Successfully');
        } else{
            return redirect()->route($module['main_route'].'.index')->with('success', 'Amount Already paid');
        }
    }

    public function send_reminder_bulk($mem_id){
        $helpers =new helpers();
        $module = $this->module;
        $member = \App\Models\Members::find($mem_id);
        $org_id = $member->organization_id;
        $dest_mob_no = $member->mobile_number;
        $charge = \App\Models\Charges::find($member->charges_id);
        $now = Carbon::now();

        $day = $now->day;
        $curr_month = $now->format('Y-m');
        $je_model = \App\Models\Report::where([['member_id', '=',$mem_id],['month','=',$curr_month],['status','>','0'],['delstatus','<', '1']])->orderBy('id','DESC')->first();
        $now_date = $now->day(12);
        $date = Carbon::parse($now_date)->format('d-M-Y');
        if(empty($je_model)){
            $data = [
                'name'=> $member->name,
                'mobile_number' => $member->mobile_number,
                'unit_no'=> $member->unit_number,
                'charge' => $charge->rate,
                'date' => $date
            ];
            $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','reminder'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();
            if($day>12){
                $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','overdue'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();
            }
            $templ_json = $helpers->make_temp_json($temp->id, $data);
            $message = '';
            $message = json_encode($message, true);
            dispatch( new WhatsappAPI($dest_mob_no,$message, $org_id,$templ_json) )->onConnection('sync');
        }
    }
}