<?php
namespace App\Http\Controllers\Admin;

use App\Models\Event_Txn as DefaultModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use URL;
use PDF;
use Carbon\Carbon;
use App\Helpers\helpers;
use App\Jobs\WhatsappAPI;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class EventTransactionController extends Controller{
    public $module = array(
        'module_view' => 'event_txn',
        'module_route' => 'event_transaction',
        'permission_group' => 'event_transaction',
        'main_heading' => 'Event Transaction',
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
        // dd($request->input());
        $module = $this->module;
        $auth_user= Auth::user();
        $roles = $auth_user->roles()->pluck('id')->toArray();
        $request->validate([
            'organization_id'=>in_array(1,$roles)?'required':'nullable',
            'event_id' => 'required',
            'series_id' => 'required',
            'payment_date' => 'required',
            'mobile_number' => 'required',
            'mode' => 'required|in:cash,online,personal_online',
            'attendes.*.number' => 'required'
        ]);
        $additional_data =[];
        $series = \App\Models\Series::find($request->input('series_id'));
        $additional_data['series_number'] = $series->name.$series->number_separator.str_pad($series->next_number,$series->min_length,'0', STR_PAD_LEFT);
        $additional_data['series_next_number'] = $series->next_number;
        \App\Models\Series::where('id',$request->input('series_id'))->update(['next_number'=>$additional_data['series_next_number']+1]);
        $additional_data['organization_id']=in_array(1,$roles) ? $request->input('organization_id'):$auth_user->organization_id;
        if(!empty($request->input('member_id')) && $request->input('member_id') != 'N/A'){
            $member = \App\Models\Members::find($request->input('member_id'));
            $additional_data['name'] = $member->name;
        } elseif($request->input('member_id') == 'N/A'){
            $additional_data['member_id'] = null;
            $additional_data['name'] = $request->input('name');
        }
        $additional_data['payment_status'] ='paid';
        $additional_data['amount'] = 0;
        foreach($request->input('attendes') as $att){
            $additional_data['amount'] += ($att['amount']*$att['number']) ?? 0;
        }
        $request->merge($additional_data);

        $fetch_data = $model->create($request->all());
        $attende = [];
        foreach($request->input('attendes') as $ind=>$att){
            $attende['organization_id'] = $additional_data['organization_id'];
            $attende['event_id'] = $request->input('event_id');
            $attende['attendes_id'] = $att['attendes_id'];
            $attend = \App\Models\Attendes::find($att['attendes_id']);
            $attende['member_id'] = $additional_data['member_id'] ?? $request->input('member_id');
            $attende['event_txn_id'] = $fetch_data->id;
            $attende['amount'] = $att['amount'];
            $attende['number'] = $att['number'];
            $attende['name'] = $attend->name;
            \App\Models\Event_Attendes_Relation::create($attende);
        }
        $this->generate_pdf($fetch_data->id);
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
        $dataArray=[];
        $event_attendes = \App\Models\Event_Attendes_Relation::where([['status','>','0'], ['delstatus','<','1'], ['event_txn_id','=',$id]])->get()->toArray();
        foreach($event_attendes as $ea){
            $temp_ar = [];
            $temp_ar['attendes_id'] = $ea['attendes_id'];
            $temp_ar['id'] = $ea['id'];
            $temp_ar['name'] = $ea['name'];
            $temp_ar['amount'] = $ea['amount'];
            $temp_ar['number'] = $ea['number'];
            $dataArray[] = $temp_ar;
        }

        return view($module['main_view'].'.cred2')->with(compact('form_data', 'model', 'module', 'action', 'method', 'mode', 'dataArray', 'folder', 'title_shown', 'id'));
    }

    public function update(Request $request, $id, DefaultModel $model){
        $module = $this->module;
        // dd($request->all());
        $request->validate([
            'payment_date' => 'required',
            'mode' => 'required|in:cash,online,personal_online',
            'attendes.*.number' => 'required',
            'mobile_number' => 'required'
        ]);

        $additional_data =[];
        $additional_data['payment_status'] ='paid';
        $additional_data['amount'] = 0;
        if(!empty($request->input('member_id')) && $request->input('member_id') != 'N/A'){
            $member = \App\Models\Members::find($request->input('member_id'));
            $additional_data['name'] = $member->name;
        } elseif($request->input('member_id') == 'N/A'){
            $additional_data['member_id'] = null;
            $additional_data['name'] = $request->input('name');
        }
        foreach($request->input('attendes') as $att){
            $additional_data['amount'] += ($att['amount']*$att['number']) ?? 0;
        }
        $request->merge($additional_data);
        $attende = [];
        $modelfind = $model->find($id);
        $fetch_data = $modelfind->update($request->all());
        $evnt_rel = \App\Models\Event_Attendes_Relation::where([['event_txn_id','=',$id]])->update(['status'=>'0']);
        foreach($request->input('attendes') as $ind=>$att){
            $event_attendes_relation = \App\Models\Event_Attendes_Relation::where([['delstatus','<','1'],['attendes_id', $att['attendes_id']], ['event_txn_id','=',$id]])->first();
            if(!empty($event_attendes_relation)){
                $attende['attendes_id'] = $att['attendes_id'];
                $attend = \App\Models\Attendes::find($att['attendes_id']);
                $attende['member_id'] = $additional_data['member_id'] ?? $request->input('member_id');
                $attende['amount'] = $att['amount'];
                $attende['number'] = $att['number'];
                $attende['name'] = $attend->name;
                $attende['status'] = '1';
                $event_attendes_relation->update($attende);    
            } else{
                $attende['organization_id'] = $modelfind->organization_id;
                $attende['event_id'] = $modelfind->event_id;
                $attende['attendes_id'] = $att['attendes_id'];
                $attend = \App\Models\Attendes::find($att['attendes_id']);
                $attende['member_id'] = $additional_data['member_id'] ?? $request->input('member_id');
                $attende['event_txn_id'] = $id;
                $attende['amount'] = $att['amount'];
                $attende['number'] = $att['number'];
                $attende['name'] = $attend->name;
                \App\Models\Event_Attendes_Relation::create($attende);
            }
            // $attende['attendes_id'] = $att['attendes_id'];
            // $attende['member_id'] = $additional_data['member_id'] ?? $request->input('member_id');
            // $attende['amount'] = $att['amount'];
            // $attende['number'] = $att['number'];
            // $attende['name'] = $att['name'];
            // $event_attendes_relation->update($attende);
        }
        $this->generate_pdf($id);
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

    public function get_attendes(Request $request){
        $id = $request->input('id');
        $no = $request->input('no');
        $sel_id = $request->input('sel_id') ?? 0;
        $event = \App\Models\Event::find($id);
        $attendes = json_decode($event->collection_slab);
        $data =[];
        $count=0;
        // $temp['select'] = '<div class="col-sm"><select class="custom-select" required name="attendes['.$count.'][gender]"<option value="" selected="selected"> --Select Attendes-- </option>';<option value="male"> Adult </option><option value="female"> Child </option><option value="other"> Free </option></select></div>';
        // dump($attendes);
        $select = '<div class="col-sm"><select class="custom-select attendes_id" required data-toggle="select" name="attendes['.$no.'][attendes_id]"><option value=""> --Select Attendes-- </option> ';
        foreach($attendes as $at){
            $atten = \App\Models\Attendes::find($at->attendes_id);
            $select = $select.'<option value="'.$at->attendes_id.'" data-amount="'.$at->amount.'">'.$atten->name.'</option>';
            // }
        }
        $select = $select.'</select></div>';
        return $select;
    }

    public function series_select(Request $request) {
        $module = $this->module;
        $input=$request->input('org_id');
        $models =new \App\Models\Series();
        $data = $models->select('id', 'name')->where([['organization_id','=',$input], ['status','>','0'], ['delstatus','<','1'], ['type','=', 'journal_entry']])->orderBy('id','DESC')->get()->toArray();
        $row_data =[];
        foreach($data as $ds){
            $row_data[$ds['id']] = $ds['name'];
        }
        $html_data = view($module['main_view'].'.series_select', compact(['row_data']))->render();

        $response = response()->json(['html'=>$html_data]);
        return $response;
    }

    public function generate_pdf($id){
        $module = $this->module;
        $request = Request();
        $setting_model = new \App\Models\Settings();
        $event_txn = \App\Models\Event_Txn::find($id);
        $event = \App\Models\Event::find($event_txn->event_id);
        $event_transaction_relation = \App\Models\Event_Attendes_Relation::where([['event_txn_id','=',$id],['status','>','0'],['delstatus','<','1']])->get()->toArray();
        $organization = \App\Models\Organization::find($event_txn->organization_id);
        $member = \App\Models\Members::find($event_txn->member_id);
        $relation_pdf=[];
        if($event_txn->file_name && file_exists(public_path('upload/event_transaction/'.$event_txn->file_name.'.pdf'))) {
             unlink(public_path('upload/event_transaction/' . $event_txn->file_name.'.pdf'));
             $file_name = $event_txn->file_name;
        }
        else{
            $now=Carbon::now();
            $file_name = $id.'-'.$now->format('Y-m-d-H-i-s');
        }
        $total=0;
        foreach($event_transaction_relation as $etr){
            $temp_arr=[];
            $temp_arr['name'] = $etr['name'];
            $temp_arr['money'] = $etr['amount'];
            $temp_arr['number'] = $etr['number'];
            $relation_pdf[]=$temp_arr;
        }
        $data = [
            'event_name'=>$event->name,
            'organization_name'=>$organization->name,
            'address'=>$organization->address,
            'note' => $setting_model->getVal('pdf', 'pdf_note'),
            'line1' => $setting_model->getVal('pdf', 'line1'),
            'unit_number'=>$member->unit_number ?? 'N/A',
            'reciept_number'=>$event_txn->series_number,
            'name'=>$event_txn->name,
            'payment_date'=>$event_txn->payment_date,
            'relation_pdf'=>$relation_pdf,
            'number'=>$event_txn->mobile_number,
            'total'=>$event_txn->amount
        ];
        $pdf = PDF::loadView('include.make_event_transaction_pdf', $data);

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
        $mpdf->Output(public_path("upload/event_transaction/{$file_name}.pdf"), \Mpdf\Output\Destination::FILE);
        \App\Models\Event_Txn::where('id','=',$id)->update(['file_name'=>$file_name]);

        $this->show_pdf($id);
        if(!empty($request->input('redirect'))){
            return redirect()->route($module['main_route'].'.show_pdf', $id);
        }
        if(!empty($request->input('redirect_index'))){
            return redirect()->route($module['main_route'].'.index')->with('success', 'File regenerated Successfully');
        }
    }

    public function show_pdf($id){
        $event_txn = \App\Models\Event_Txn::find($id);
        $name = $event_txn->file_name;
        return view('include.show_event_transaction',compact('name'));
    }

    public function send_msg(Request $request, helpers $helpers){
        $module = $this->module;
        $id = $request->input('id');
        $api = $this->whatsapp_api;
        $model = \App\Models\Event_Txn::find($id);
        $org_id = $model->organization_id;
        $category = 'event_transaction';
        $member = \App\Models\Members::find($model->member_id);
        $dest_mob_no = $model->mobile_number;
        $event = \App\Models\Event::find($model->event_id);
        $file_name = $model->file_name;
        $mem_id = $model->member_id;
        $organisation = \App\Models\Organization::find($org_id);

        $message = array(
            'type' => $api['type'],
            $api['type'] => array(
                'link' => url('/upload/event_transaction/'.$file_name.'.pdf'),
                // 'link' => 'https://gvtapp.netpyx.org/supanel/journal_entry/921/show',

                'filename' => 'Reciept'
            )
        );
        $date_arr = explode(' ', $event->date);
        $date = Carbon::parse($date_arr[0])->format('d-M-Y');
        $time = $date_arr[1];
        if($model->from_month == $model->to_month){
            $month = Carbon::parse($model->from_month)->format('M Y');
        } else{
            $month = Carbon::parse($model->from_month)->format('M Y')."-".Carbon::parse($model->to_month)->format('M Y');
        }
        // $a = 'charge';
        $data = [
            'event_name' => $event->name,
            'amount'=> $model->amount,
            'date'=> $date,
            'organization_name'=> $organisation->name,
            'time' => $time
        ];
        $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','event_reciept'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();
        $templ_json = $helpers->make_temp_json($temp->id, $data);
        $message = json_encode($message, true);
        dump($message);
        dump($templ_json);
        dd('end');

        dispatch( new WhatsappAPI($dest_mob_no,$message, $org_id,$templ_json, $category, $mem_id) )->onConnection('sync');

        $model->count++;
        $model->save();

        // return redirect()->route($module['main_route'].'.index')->with('success', 'Message send Successfully');
        return '';
        // return redirect()->route($module['main_route'].'.index')->with('info', "Message can't be sent");
        // return 'fail';

        // dispatch( new WhatsappAPI($dest_mob_no,$message, $org_id,$templ_json) )->onConnection('sync');

    }
}
