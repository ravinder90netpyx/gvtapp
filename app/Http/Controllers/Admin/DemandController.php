<?php
namespace App\Http\Controllers\Admin;

use App\Models\Demand as DefaultModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use URL;
use PDF;
use App\Helpers\helpers;
use Carbon\Carbon;
use App\Jobs\WhatsappAPI;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class DemandController extends Controller{
    public $module = array(
        'module_view' => 'demand',
        'module_route' => 'demand',
        'permission_group' => 'demand',
        'main_heading' => 'Demand',
        'default_perpage' => 10
    );

    public $whatsapp_api = array(
        'whatsapp_api_url' => 'https://api.gupshup.io/wa/api/v1/template/msg',
        'channel' => 'whatsapp',
        'src_name' => 'GVTGH9',
        'type' => 'document',
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
        
        $model_get = $model;
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

        return view($module['main_view'].'.cred2', compact('module', 'model', 'action', 'method', 'mode', 'folder', 'title_shown'));
    }

    public function store(Request $request, DefaultModel $model){
        $module = $this->module;
        $auth_user = Auth::user();
        $roles = $auth_user->roles()->pluck('id')->toArray();
        $date = Carbon::now();
        $rules = [
            'organization_id' => in_array(1,$roles)?'required':'',
            'series_id' => 'required',
            'member_id' => 'required',
            'charge_type_id' => 'required',
            'due_date' => 'required',
            'remark' => 'nullable',
            'charge_type.*.amount' => 'required',
            'charge_type.*.remark' => 'required'
        ];
        $charge_type = \App\Models\ChargeType::find($request->input('charge_type_id'));
        // if($charge_type->type == 'maintenance'){
        //     $rules['from_date'] = 'required';
        //     $rules['to_date'] = 'required';
        // }
        $request->validate($rules);
        $additional_data = [];
        //org id
        $org_id = in_array(1,$roles)? $request->input('organization_id') : $auth_user->organization_id;
        $additional_data['organization_id'] = $org_id;
        //demand series
        $series_number = \App\Models\Series::find($request->input('series_id'));
        $series_num =$series_number->name.$series_number->number_separator.str_pad($series_number->next_number,$series_number->min_length,'0', STR_PAD_LEFT);
        $next_number = $series_number->next_number;
        $upd = \App\Models\Series::where('id','=',$series_number->id)->update(['next_number'=>$series_number->next_number+1]);
        $additional_data['demand_reciept_number'] = $series_num;
        $additional_data['series_next_number'] = $next_number;
        //member
        $member = \App\Models\Members::find($request->input('member_id'));
        $additional_data['member_name'] = $member->name;
        $additional_data['demand_status'] = 'unpaid';
        $additional_data['current_date'] = $date;
        $additional_data['charge_type_id'] = implode(',',$request->input('charge_type_id'));
        $request->merge($additional_data);

        $demand = $model->create($request->all());
        $demand_charge = [];
        $demand_charge['demand_id'] = $demand->id;
        $demand_charge['organization_id'] = $org_id;
        $demand_charge['member_id'] = $request->input('member_id');
        $demand_charge['member_name'] = $member->name;
        $demand_charge['charge_status'] = 'unpaid';
        $demand_charge['partial'] = '0';
        $demand_charge['paid_amount'] = 0;
        $demand_charge['times_paid'] = 0;
        $demand_charge['partial'] = $request->input('partial') ?? '0';
        foreach($request->input('charge_type') as $ind=>$ch){
            $demand_charge['charge_type_id'] = $ind;
            $demand_charge['pending_amount'] = $ch['amount'];
            $demand_charge['remark'] = $ch['remark'];
            \App\Models\Demand_Charge::create($demand_charge);
        }

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
        $dataArray = \App\Models\Demand_Charge::where([['demand_id','=',$id],['delstatus','<','1'],['status','>','0']])->get()->toArray();

        return view($module['main_view'].'.cred2')->with(compact('form_data', 'model', 'module', 'action', 'method', 'mode', 'folder', 'dataArray', 'title_shown', 'id'));
    }

    public function update(Request $request, $id, DefaultModel $model){
        // dump(implode(',',$request->input('charge_type_id')));
        // dd($request->all());

        $module = $this->module;
        $rules = [
            'member_id' => 'required',
            'charge_type_id' => 'required',
            'due_date' => 'required',
            'remark' => 'nullable',
            'charge_type.*.amount' => 'required',
            'charge_type.*.remark' => 'required'
        ];
        // $charge_type = \App\Models\ChargeType::find($request->input('charge_type_id'));
        // if($charge_type->type == 'maintenance'){
        //     $rules['from_date'] = 'required';
        //     $rules['to_date'] = 'required';
        // }
        $request->validate($rules);
        $additional_data = [];
        $member = \App\Models\Members::find($request->input('member_id'));
        $additional_data['member_name'] = $member->name;
        $additional_data['charge_type_id'] = implode(',',$request->input('charge_type_id'));
        $request->merge($additional_data);
        $modelfind = $model->find($id);
        $modelfind->update($request->all());
        // Saving to Demand Charge
        $demand_charge =[];
        $demand_charge['demand_id'] = $id;
        $demand_charge['organization_id'] = $modelfind->organization_id;
        $demand_charge['member_id'] = $request->input('member_id');
        $demand_charge['member_name'] = $member->name;
        $demand_charge['charge_status'] = 'unpaid';
        $demand_charge['paid_amount'] = 0;
        $demand_charge['times_paid'] = 0;
        $demand_charge['partial'] = $request->input('partial');
        $change_status = \App\Models\Demand_Charge::where([['demand_id','=',$id]])->update(['status'=>'0']);
        foreach($request->input('charge_type') as $ind=>$ch){
            $check_demand = \App\Models\Demand_Charge::where([['id','=',$ch['demand_charge_id']],['charge_type_id','=',$ind]])->first();
            if(!empty($check_demand)){
                $demand_charge['pending_amount'] = $ch['amount'];
                $demand_charge['remark'] = $ch['remark'];
                $demand_charge['status'] = '1';
                $dem_model = \App\Models\Demand_Charge::find($check_demand->id);
                // dd($demand_charge);
                $dem_model->update($demand_charge);
            } else{
                $demand_charge['charge_type_id'] = $ind;
                $demand_charge['pending_amount'] = $ch['amount'];
                $demand_charge['remark'] = $ch['remark'];
                \App\Models\Demand_Charge::create($demand_charge);
            }
        }
    
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

    public function generate_pdf($dem_id){
        $module = $this->module;
        $request = Request();
        $setting_model = new \App\Models\Settings();
        $demand = \App\Models\Demand::find($dem_id);
        $demand_charge = \App\Models\Demand_Charge::where([['demand_id','=',$dem_id],['status','>','0'],['delstatus','<','1']])->get()->toArray();
        $organization = \App\Models\Organization::find($demand->organization_id);
        $member = \App\Models\Members::find($demand->member_id);
        $demand_pdf=[];
        if($demand->file_name && file_exists(public_path('upload/demand/'.$demand->file_name.'.pdf'))) {
             unlink(public_path('upload/demand/' . $demand->file_name.'.pdf'));
             $file_name = $demand->file_name;
        }
        else{
            $now=Carbon::now();
            $file_name = $dem_id.'-'.$now->format('Y-m-d-H-i-s');
        }
        $total=0;
        foreach($demand_charge as $dc){
            $temp_arr=[];
            $charge_type = \App\Models\ChargeType::find($dc['charge_type_id']);
            $temp_arr['name'] = $charge_type->name;
            $temp_arr['money'] = $dc['pending_amount']+$dc['paid_amount'];
            $total = $total+$temp_arr['money'];
            $demand_pdf[]=$temp_arr;
        }
        $data = [
            'organization_name'=>$organization->name,
            'address'=>$organization->address,
            'note' => $setting_model->getVal('pdf', 'pdf_note'),
            'line1' => $setting_model->getVal('pdf', 'line1'),
            'unit_number'=>$member->unit_number,
            'reciept_number'=>$demand->demand_reciept_number,
            'name'=>$member->name,
            'due_date'=>$demand->due_date,
            'demand_pdf'=>$demand_pdf,
            'number'=>$member->mobile_number,
            'total'=>$total
        ];
        $pdf = PDF::loadView('include.make_demand_pdf', $data);

        $mpdf = $pdf->getMpdf();
        $width = 10; // Adjust the width of the watermark image
        $height = 10; // Adjust the height of the watermark image

        // Get the dimensions of the page
        $pageWidth = $mpdf->w;
        $pageHeight = $mpdf->h;

        // Calculate the position to center the image
        $x = ($pageWidth - $width) / 8;
        $y = ($pageHeight - $height) / 8;

        $mpdf->SetWatermarkImage(public_path('dashboard/img/Logo-GVT.png'), 0.1, '', array($x, $y), true);
        
        $mpdf->showWatermarkImage = true;
        // $mpdf->save(public_path("upload/pdf_files/{$file_name}.pdf"));
        // $models=$journal_entry->find($je_id);
        $mpdf->Output(public_path("upload/demand/{$file_name}.pdf"), \Mpdf\Output\Destination::FILE);
        \App\Models\Demand::where('id','=',$dem_id)->update(['file_name'=>$file_name]);

        $this->show_pdf($dem_id);
        if(!empty($request->input('redirect'))){
            return redirect()->route($module['main_route'].'.show_pdf', $dem_id);
        }
        if(!empty($request->input('redirect_index'))){
            return redirect()->route($module['main_route'].'.index')->with('success', 'File regenerated Successfully');
        }
    }

    public function show_pdf($dem_id){
        $demand = \App\Models\Demand::find($dem_id);
        $name = $demand->file_name;
        // dump($name);
        return view('include.show_demand',compact('name'));
    }

    public function send_msg(Request $request, helpers $helpers){
        $module = $this->module;
        $id = $request->input('id');
        $api = $this->whatsapp_api;
        $model = \App\Models\Demand::find($id);
        $demand_charge = \App\Models\Demand_Charge::where([['status','>','0'],['delstatus','<','1'], ['demand_id', '=', $id]])->get()->toArray();
        $amount = 0;
        foreach($demand_charge as $dc){
            $amount = $amount + $dc['pending_amount']+$dc['paid_amount'];
        }
        $org_id = $model->organization_id;
        $category = 'send_demand';
        $member = \App\Models\Members::find($model->member_id);
        $dest_mob_no = $member->mobile_number;
        $file_name = $model->file_name;
        $mem_id = $model->member_id;

        $message = array(
            'type' => $api['type'],
            $api['type'] => array(
                'link' => url('/upload/demand/'.$file_name.'.pdf'),
                // 'link' => 'https://gvtapp.netpyx.org/supanel/journal_entry/921/show',

                'filename' => 'Demand'
            )
        );
        // $a = 'charge';
        $data = [
            'amount'=> $amount,
            'reciept_number'=> $model->demand_reciept_number
        ];
        $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','demand'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();
        $templ_json = $helpers->make_temp_json($temp->id, $data);
        $message = json_encode($message, true);

        dispatch( new WhatsappAPI($dest_mob_no,$message, $org_id,$templ_json, $category, $mem_id) )->onConnection('sync');
        $model->count++;
        $model->save();
        return '';

        // return redirect()->route($module['main_route'].'.index')->with('info', "Message can't be sent");

        // dispatch( new WhatsappAPI($dest_mob_no,$message, $org_id,$templ_json) )->onConnection('sync');
    }

    public function send_reminder($id){
        $helpers =new helpers();
        $module = $this->module;
        $category = 'demand_reminder';
        $demand = \App\Models\Demand::find($id);
        $member = \App\Models\Members::find($demand->member_id);
        $org_id = $demand->organization_id;
        $dest_mob_no = $member->mobile_number;
        $charge_arr = explode(',', $demand->charge_type_id);
        $charge_name =[];
        foreach($charge_arr as $cha){
            $charge = \App\Models\ChargeType::find($cha);
            $charge_name[] = $charge->name;
        }

        $charge_name = implode('|', $charge_name);
        $charge = \App\Models\ChargeType::find($member->charges_id);
        $now = Carbon::now();
        // $month = $je_model->to_month;
        $dest_mob_no = '+917479735912';
        $day = $now->day;

        // if($day>12){
        //     $now = $now->addMonth();
        // }
        $curr_month = $now->format('Y-m');
        $data = [
            'charge_name'=> $charge_name,
            'reciept_number' => $demand->demand_reciept_number
        ];
        $demand_charge = \App\Models\Demand_Charge::where([['status','>','0'], ['delstatus','<','1'], ['demand_id', '=', $id]])->get()->toArray();
        $pending_amount =0;
        foreach($demand_charge as $dc){
            $pending_amount += $dc['pending_amount'];
        }
        if($pending_amount>0){
            $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','demand_reminder'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();

            $templ_json = $helpers->make_temp_json($temp->id, $data);
            $message = '';
            $message = json_encode($message, true);
            dispatch( new WhatsappAPI($dest_mob_no,$message, $org_id,$templ_json, $category,$member->id) )->onConnection('sync');
            return redirect()->route($module['main_route'].'.index')->with('success', 'Reminder Message send Successfully');
        } else{
            return redirect()->route($module['main_route'].'.index')->with('info', 'Amount Already paid');
        }
    }
}
