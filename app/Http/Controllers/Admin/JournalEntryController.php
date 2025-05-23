<?php
namespace App\Http\Controllers\Admin;

use App\Models\Journal_Entry as DefaultModel;
use App\Models\Monthwise_Fine;
use App\Models\Entrywise_Fine;
use App\Models\API_Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use URL;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\helpers;
use Illuminate\Support\Facades\Auth;
use PDF;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use App\Jobs\WhatsappAPI;
use App\Models\Report;

class JournalEntryController extends Controller{
    public $module = array(
        'module_view' => 'journal_entry',
        'module_route' => 'journal_entry',
        'permission_group' => 'journal_entry',
        'main_heading' => 'Journal Entries',
        'start_date' => null,
        'default_perpage' => 10,
        'source' => '919041362511',
        'group' => 'whatsapp_settings'
    );

    public $whatsapp_api = array(
        'whatsapp_api_url' => 'https://api.gupshup.io/wa/api/v1/template/msg',
        'channel' => 'whatsapp',
        'src_name' => 'GVTGH9',
        'type' => 'document',
        'template_id' => 'c376f4e4-2743-4eb9-8cdb-2648f7457d22',
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

    public function index(Request $request, DefaultModel $model, helpers $helpers){
        $carbon = new Carbon();
        $module = $this->module;
        // dump($request->input());
        // $api = $this->whatsapp_api;
        // $org_id = '1';
        // // dd(url('upload/pdf_files/14-2024-05-29-09-49-42.pdf'));
        // $destination = '+91 74797 35912';
        // $message = array(
        //         'type' => $api['type'],
        //         $api['type'] => array(
        //             'link' => 'https://www.adobe.com/support/products/enterprise/knowledgecenter/media/c4611_sample_explain.pdf',
        //             'file_name' => 'journal Entry File'
        //         )
        //     );
        // $date = '24-05-2024';
        // $charge = '7000';
        // $month = "2024-08-2024-11";
        // $params = array(
        //         $charge,
        //         $month,
        //         $date
        //     );
        // $model1 = new \App\Models\Organization_Settings();
        // $templ_id = $model1->getVal($module['group'], 'template_id',$org_id);

        // $template_arr = array(
        //     'id' => $templ_id,
        //     'params' => $params
        // );

        // $templ_json = json_encode($template_arr);
        // $message = json_encode($message, true);
        // $this->sendPdfToWhatsapp($destination,$message, $org_id,$params);

        $perpage = $request->perpage ?? $module['default_perpage'];
        $title_showns = 'Add '.$module['main_heading'];
        $method = 'POST';
        $mode = 'insert';
        $action = URL::route($module['main_route'].'.store');
        $act = URL::route($module['main_route'].'.store');

        if(!$request->perpage && !empty($request->cookie('perpage'))) $perpage = $request->cookie('perpage');

        $financial_years = $helpers->get_financial_years($module['start_date'], null);
        
        $model_get = $model;

        $auth_user = Auth::user();
        $roles = $auth_user->roles()->pluck('id')->toArray();
        if(!in_array(1, $roles)){
            $organization_id = $auth_user->organization_id;
            $model_get = $model_get->where('organization_id', $organization_id);
            $series = \App\Models\Series::where([['status','>','0'], ['delstatus','<','1'], ['organization_id', '=',$organization_id]])->latest()->first();
        }

        if($model->getDelStatusColumn()) $model_get = $model_get->where($model->getDelStatusColumn(), '<', '1');
        
        if($model->getSortOrderColumn()) $model_get = $model_get->orderBy($model->getSortOrderColumn(), 'ASC');
        else $model_get = $model_get->latest();
        
        $query = $request->get('query') ?? '';
        $unit_no = $request->get('unit_no') ?? '';
        if($query!=''){
            $model_get = $model_get->where('series_number', 'LIKE', '%'.$query.'%')->orwhere('name', 'LIKE', '%'.$query.'%');
            $model_get = $model_get->orWhere(function($q) use ($query) {
                $q->whereHas('memberSearch', function($q2) use ($query) {
                    $q2->where('unit_number', 'LIKE', '%'.$query.'%');
                });
            });
        }
        if($unit_no !=''){
            $model_get = $model_get->where(function($q) use ($unit_no) {
                $q->whereHas('memberSearch', function($q2) use ($unit_no) {
                    $q2->where('unit_number', 'LIKE', '%'.$unit_no.'%');
                });
            });
        }

        $serial_id = isset($series->id) ? $series->id:'';

        $data = $model_get->paginate($perpage)->onEachSide(2);

        $title_shown = 'Manage '.$module['main_heading'];
        $folder = $this->folder;

        return view($module['main_view'].'.index', compact('data', 'action', 'method', 'act', 'model', 'mode', 'carbon', 'serial_id', 'module', 'perpage', 'folder', 'title_shown', 'title_showns', 'query', 'financial_years', 'unit_no'))->with('i', ($request->input('page', 1) - 1) * $perpage);
    }

    public function create(DefaultModel $model, helpers $helpers){
        $module = $this->module;
        $folder = $this->folder;
        $action = URL::route($module['main_route'].'.store');
        $title_shown = 'Add '.$module['main_heading'];
        $method = 'POST';
        $mode = 'insert';
        $financial_years = $helpers->get_financial_years($module['start_date'], null);

        return view($module['main_view'].'.cred2', compact('module', 'model', 'financial_years', 'action', 'method', 'mode', 'folder', 'title_shown'));
    }

    public function store(Request $request, DefaultModel $model, helpers $helpers){
        // dd($request->input());
        $module = $this->module;
        $auth_user = Auth::user();
        $charge_name = '';
        if(!empty($request->input('charge_type_id'))){
            $charge_mod = \App\Models\ChargeType::find($request->input('charge_type_id'));
            $charge_name = $charge_mod->type;
        }
        $roles = $auth_user->roles()->pluck('name','id')->toArray();
        $rules= [
            'series_id' =>
            [
                'required',
                'numeric',
                function($attribute, $value, $fail){
                    $series_number = \App\Models\Series::where('id',$value)->get();
                    if(empty($series_number->count())){
                        $fail('Choose a valid Series');
                    }
                }
            ],
            'entry_year' => 'required',
            'entry_date' => 'required|date_format:Y-m-d H:i:s',
            // 'form_data.member_mob' => 'required',
            'member_id' => 'required|numeric',
            'payment_mode' => 'required|in:online,cash,cheque',
            'charge_type_id' => 'required',
            'from_month' => 
            [
                empty($request->input('custom_month')) && $charge_name == 'maintenance' ? 'required':'nullable',
                function($attribute, $value, $fail){
                    $request = Request();
                    $helpers = new helpers();
                    $month_arr = $helpers->get_financial_month_year($value, $request->input('to_month','Y-m'));
                    $report_month = Report::where([['member_id','=',$request->input('member_id')], ['delstatus','<', '1'], ['status', '>', '0'], ['money_pending', '=', '0']])->orderBy('month', 'ASC')->pluck('month')->toArray();
                    foreach($month_arr as $mt){
                        if(in_array($mt, $report_month)){
                            $fail("The money of ".$mt."is already paid");
                        }
                    }
                    // dd($report_month);
                    $report_model = Report::where([['member_id','=',$request->input('member_id')], ['delstatus','<', '1'], ['status', '>', '0'], ['money_pending','=', '0']])->orderBy('month', 'DESC')->first();
                    $report_model2 = Report::where([['member_id','=',$request->input('member_id')], ['delstatus','<', '1'], ['status', '>', '0'], ['money_pending','>', '0']])->orwhere([['member_id','=',$request->input('member_id')], ['delstatus','<', '1'], ['status', '>', '0']])->orderBy('month', 'DESC')->first();
                    $last_month = $report_model->month ?? '0000-00';
                    $act_from_mon = $report_model2->month ?? '9999-12';
                    if(empty($report_model2->money_pending)){
                        $date = Carbon::createFromFormat('Y-m', $act_from_mon);
                        $date->addMonth();
                        $act_from_mon = $date->format('Y-m');
                    }
                    $to_month = $request->input('to_month');
                    // if($value <= $last_month){
                    //     $fail('The money for further month is already paid kindly select the next month to pay the money');
                    // }
                    if($value>$act_from_mon){
                        $fail('The money of previous month is not paid please select previous month to pay');
                    }
                    if($to_month < $value){
                        $fail('From Month should not be ahead of To Month');
                    }
                }
            ],
            'to_month' =>
            [
                empty($request->input('custom_month')) && $charge_name == 'maintenance' ? 'required':'nullable',
                function($attribute, $value, $fail){
                    $hlp = new helpers;
                    $request = Request();
                    $member = \App\Models\Members::find($request->input('member_id'));
                    $charge = \App\Models\Charges::find($member->charges_id)->rate;
                    $mon_arr = $hlp->get_financial_month_year($request->from_month, $value,'Y-m');
                    // $mon_arr = $helpers->get_financial_month_year($request->from_month, $value);
                    $report_model2 = Report::where([['member_id','=',$request->input('member_id')], ['delstatus','<', '1'], ['status', '>', '0'], ['money_pending','>', '0']])->orderBy('month', 'ASC')->first();

                    $pending_money = $report_model2->money_pending ?? 0;
                    $count= count($mon_arr);
                    if(!empty($report_model2->money_pending)) $count =$count-1;
                    if(!empty($request->paid_money)){
                        if($count !=ceil(($request->paid_money-$pending_money)/$charge)){
                            $fail('Amount Paid and selected month are not proportional kindly adjust the value or month');
                        }
                    }
                }
            ],

            'custom_month' =>
            [
                empty($request->input('from_month')) && $charge_name == 'maintenance' ? 'required':'nullable',
                function($attribute, $value, $fail){
                    $val_arr = [];
                    $val_arr = explode(',',$value);
                    $request = Request();
                    foreach($val_arr as $vl){
                        $report = Report::where([['member_id', '=', $request->input('member_id')],['status','>', '0'], ['delstatus','<','1'], ['month', '=', $vl]])->count();
                        if($report>0){
                            $fail("The Amount of this month is already paid");
                        }
                    }
                    $report_model2 = Report::where([['member_id','=',$request->input('member_id')], ['delstatus','<', '1'], ['status', '>', '0'], ['money_pending','>', '0']])->orderBy('month', 'ASC')->first();
                    $member = \App\Models\Members::find($request->input('member_id'));
                    $charge = \App\Models\Charges::find($member->charges_id)->rate;
                    $pending_money = $report_model2->money_pending ?? 0;
                    $count = count($val_arr);
                    if(!empty($report_model2->money_pending)) $count =$count-1;
                    if(!empty($request->paid_money)){
                        if($count !=ceil(($request->paid_money-$pending_money)/$charge)){
                            $fail('Amount Paid and selected month are not proportional kindly adjust the value or month');
                        }
                    }
                }
            ]
        ];
        $validator=\Illuminate\Support\Facades\Validator::make([], []);
        $report_model = new \App\Models\Report();
        $report_last_month = $report_model->where([['member_id','=',$request->input('member_id')],['delstatus','<','1'], ['status','>','0']])->orderBy('month', 'DESC')->first();
        if(in_array('1', array_keys($roles))){
            $rules['organization_id'] = 'required|numeric';
        }

        $from_month = $request->input('from_month');
        $to_month = $request->input('to_month');
        $member_je = $model->where([['member_id','=',$request->input('member_id')],['delstatus','<', '1'], ['status', '>', '0']])->get();
        $mon_arr=[];
        $last_month = $report_last_month->month ?? '0000-00';
        foreach($member_je as $je){
            $temp_arr=[];
            $temp_arr = $helpers->get_financial_month_year($je['from_month'], $je['to_month'],'Y-m');
            $mon_arr = empty($temp_arr) ? $temp_arr : array_merge($mon_arr , $temp_arr);
        }
        $month_arr =[];
        if(!empty($request->input('from_month')) && !empty($request->input('to_month'))){
            $month_arr = $helpers->get_financial_month_year($from_month,$to_month,'Y-m');
        } else{
            $month_arr = explode(',', $request->input('custom_month'));
        }

        $check = \App\Models\Members::where('id',$request->input('member_id'))->count();
        
        $series_number = \App\Models\Series::find($request->input('series_id'));
        
        $request->validate($rules);
        $request_data = $request->input();
        $report_data = [];
        if(empty($request_data['organization_id'])) $request_data['organization_id'] = $auth_user->organization_id;

        $member = \App\Models\Members::find($request_data['member_id']);
        $charge = \App\Models\Charges::find($member->charges_id)->rate;
        $charge_type = \App\Models\ChargeType::find($request_data['charge_type_id']);

        if($charge_type->type == 'fine'){

            $entry_charge_arr =[];
            $name = $model->where('organization_id',$request_data['organization_id'])->orderBy('entry_date','DESC')->first();
            $request_data['partial'] ='0';
            $date = $request_data['entry_date'];
            $pre_date = !empty($name)? $name->entry_date : '0000-00-00 00:00:00';
            // if(strtotime($date) > strtotime($pre_date)){
            $series_num =$series_number->name.$series_number->number_separator.str_pad($series_number->next_number,$series_number->min_length,'0', STR_PAD_LEFT);
            $next_number = $series_number->next_number;
            $upd = \App\Models\Series::where('id','=',$series_number->id)->update(['next_number'=>$series_number->next_number+1]);
            $request_data['charge'] = $request_data['fine_amt'];
            $request_data['name'] = $member['name'];
            $request_data['series_next_number'] = $next_number;
            $request_data['series_number'] = $series_num;
            $fetch_data = $model->create($request_data);

            // Entry wise fine...

            $monthwise_fine_model = new Monthwise_Fine();

            $monthwise_model = $monthwise_fine_model->where([['member_id','=',$fetch_data->member_id],['delstatus','<','1'],['status','>','0']])->orderBy('month','DESC')->first();
            $month = '2024-07';
            if(!empty($monthwise_model->month) && $month<$monthwise_model->month){
                $month = $monthwise_model->month;
            }
            $mon_arr = $helpers->get_financial_month_year('2024-07',$month,'Y-m');
            $total_fine =0;
            foreach($mon_arr as $ma){
                $monthwise_mod = $monthwise_fine_model->where([['member_id','=',$fetch_data->member_id],['delstatus','<','1'],['status','>','0'],['month','=',$ma]])->first();
                if(empty($monthwise_mod)){
                    $total_fine += $this->late_fee_calculator($request_data['entry_date'],$ma,$fetch_data->member_id);
                } else{
                    $total_fine += $monthwise_mod->fine_amount;
                }
            }
            $entrywise_model = new Entrywise_Fine();
            $entrywise_arr =[];
            $entrywise_arr['journal_entry_id'] = $fetch_data->id;
            $entrywise_arr['member_id'] = $fetch_data->member_id;
            $entrywise_arr['fine_paid'] = $request_data['fine_amt'];
            $entrywise_arr['total_fine'] = $total_fine;
            $create_data = $entrywise_model->create($entrywise_arr);

            $monthwise_model->where([['entrywise_fine_id', '=',null],['member_id','=',$fetch_data->member_id],['status','>','0'],['delstatus','<','1'],['fine_waveoff','=','0']])->update(['entrywise_fine_id' => $create_data->id, 'fine_waveoff' => '1']);

        } elseif($charge_type->type == 'maintenance'){
            $paid = $request_data['paid_money'];
            // $month_arr = $helpers->get_financial_month_year($request->input('from_month'), $request->input('to_month'));
            $paid_m = empty($paid) ? $charge*count($month_arr) : $paid;
            // $pending_mon = $report_last_month['money_pending'] ?? 0;
            
            if(!empty($paid)){
                $request_data['partial'] = ($paid%$charge > 0)? '1':'0';
                $count=0;
                $count =ceil($paid/$charge);
            } else{
                $report_model_last_mon = $report_model->where([['month', '=', $from_month], ['member_id', '=', $request_data['member_id']], ['delstatus','<','1'], ['status','>', '0']])->orderBy('month','DESC')->first();
                $dedt_amt=0;
                if(!empty($report_model_last_mon)) $dedt_amt = $report_model_last_mon->money_paid;
                $request_data['paid_money'] = count($month_arr)*$charge - $dedt_amt;
            }

            $name = $model->where('organization_id',$request_data['organization_id'])->orderBy('entry_date','DESC')->first();
            $date = $request_data['entry_date'];
            $pre_date = !empty($name)? $name->entry_date : '0000-00-00 00:00:00';
            // if(strtotime($date) > strtotime($pre_date)){
            $series_num =$series_number->name.$series_number->number_separator.str_pad($series_number->next_number,$series_number->min_length,'0', STR_PAD_LEFT);
            $next_number = $series_number->next_number;
            $upd = \App\Models\Series::where('id','=',$series_number->id)->update(['next_number'=>$series_number->next_number+1]);
            $request_data['charge'] = $request_data['paid_money'];
            $request_data['name'] = $member['name'];
            $request_data['series_next_number'] = $next_number;
            $request_data['series_number'] = $series_num;
            $fetch_data = $model->create($request_data);
            // report --

            foreach($month_arr as $mt){
                $this->late_fee_calculator($request_data['entry_date'],$mt,$fetch_data->member_id);
                $report_models = $report_model->where([['month', '=',$mt], ['member_id','=',$request_data['member_id']], ['delstatus','<','1'],['status','>','0']])->first();
                // $tempe_arr[] = $report_models->id;
                $report_data['journal_entry_id'] = $fetch_data->id;
                if(empty($report_models)){
                    $report_data['month'] = $mt;

                    $report_data['member_id'] = $request_data['member_id'];
                    if($charge <= $paid_m){
                        $report_data['money_paid'] = $charge;
                        $report_data['money_pending'] = 0;
                        $paid_m = $paid_m - $charge;
                    } else{
                        $report_data['money_paid'] = $paid_m;
                        $report_data['money_pending'] = $charge - $paid_m;
                        $paid_m = 0;
                    }
                    $upd_rep = $report_model->create($report_data);
                } else{
                    $money = $report_models->money_pending;
                    if(!empty($money)){
                        $report_data['month'] = $mt;
                        if($money <= $paid_m){
                            $report_data['money_paid'] = $charge;
                            $report_data['money_pending'] = 0;
                            $paid_m = $paid_m - $money;
                        } else{
                            $report_data['money_paid'] = $paid_m + $report_models->money_paid;
                            $report_data['money_pending'] = $money - $paid_m;
                            $paid_m = 0;
                        }
                        $report_model->where('id','=',$report_models->id)->update($report_data);
                    }
                }
                $report_data =[];
            }
        } else{
            $entry_charge_arr =[];
            $name = $model->where('organization_id',$request_data['organization_id'])->orderBy('entry_date','DESC')->first();
            $request_data['partial'] ='0';
            $date = $request_data['entry_date'];
            $pre_date = !empty($name)? $name->entry_date : '0000-00-00 00:00:00';
            // if(strtotime($date) > strtotime($pre_date)){
            $series_num =$series_number->name.$series_number->number_separator.str_pad($series_number->next_number,$series_number->min_length,'0', STR_PAD_LEFT);
            $next_number = $series_number->next_number;
            $upd = \App\Models\Series::where('id','=',$series_number->id)->update(['next_number'=>$series_number->next_number+1]);
            $request_data['charge'] = $request_data['paid_money'];
            $request_data['name'] = $member['name'];
            $request_data['series_next_number'] = $next_number;
            $request_data['series_number'] = $series_num;
            $fetch_data = $model->create($request_data);
        }

        $this->generate_pdf_file($fetch_data->id);
        // $setting_model = new \App\Models\Settings();
        if(!empty($request_data['send'])){
            $this->whatsapp_msg($fetch_data->id);
            // dispatch( new WhatsappAPI($destination,$message, $org_id,$templ_json) )->onConnection('sync');
        // }
         
        }
        return redirect()->route($module['main_route'].'.index')->with('success', $module['main_heading'].' created successfully.');
    }

    public function show(Request $request, $id, DefaultModel $model, helpers $helpers){
        $module = $this->module;
        $folder = $this->folder;
        $form_data = $model->find($id);
        $form_data['member_mob'] = $form_data['member_id'];
        $member_data = \App\Models\Members::find($form_data['member_id']);
        $form_data['member_val'] = 'Name:'.$member_data['name'].' Unit Number:'.$member_data['unit_number'];
        $title_shown = 'Show '.$module['main_heading'];
        $mode = 'show';
        $series_title = $form_data->series_number;
        $financial_years = $helpers->get_financial_years($module['start_date'], null);
        if($request->ajax()) {
            $html_data = view($module['main_view'].'.form_include', compact(['form_data','id', 'module', 'mode', 'financial_years']))->render();
            $response = response()->json(['html'=>$html_data,'series_title'=>$series_title, 'title_shown'=>$title_shown, 'mode'=>$mode, 'id'=>$id]);
            return $response;
        } else{
            return view($module['main_view'].'.cred2', compact('form_data', 'financial_years', 'model', 'module', 'folder', 'title_shown', 'mode', 'id'));
        }
    }

    public function edit(Request $request, $id, DefaultModel $model, helpers $helpers){
        $module = $this->module;
        $folder = $this->folder;
        $form_data = $model->find($id);
        $form_data['member_mob'] = $form_data['member_id'];
        $member_data = \App\Models\Members::find($form_data['member_id']);
        $form_data['member_val'] = 'Name:'.$member_data['name'].' Unit Number:'.$member_data['unit_number'];
        $org_id = $form_data['organization_id'];
        $action = URL::route($module['main_route'].'.update', $id);
        $title_shown = 'Edit '.$module['main_heading'];
        $series_title = $form_data->series_number;
        $method = 'PUT';
        $mode = 'edit';
        $financial_years = $helpers->get_financial_years($module['start_date'], null);
        if($request->ajax()) {
            $html_data = view($module['main_view'].'.form_include', compact(['form_data','id', 'mode', 'financial_years', 'module', 'org_id']))->render();
            $response = response()->json(['html'=>$html_data, 'series_title'=>$series_title, 'title_shown'=>$title_shown, 'action'=>$action,'org_id'=>$org_id, 'method'=>$method, 'mode'=>$mode, 'id'=>$id]);
            return $response;
        } else{
            return view($module['main_view'].'.cred2')->with(compact('form_data', 'financial_years', 'model', '-', 'action', 'method', 'mode', 'folder', 'title_shown', 'id'));
        }
    }

    public function update(Request $request, $id, DefaultModel $model, helpers $helpers){
        // dd($request->input());
        $modelfind = $model->find($id);
        $module = $this->module;
        $charge_name = '';
        if(!empty($request->input('charge_type_id'))){
            $charge_mod = \App\Models\ChargeType::find($request->input('charge_type_id'));
            $charge_name = $charge_mod->type;
        }
        $request->validate([
            'entry_date' => 'required|date_format:Y-m-d H:i:s',
            'from_month' => 
            [
                empty($request->input('custom_month')) && $charge_name == 'maintenance' ? 'required':'nullable',
                function($attribute, $value, $fail) use($request, $id) {
                    $request = Request();
                    $helpers = new helpers();
                    $month_arr = $helpers->get_financial_month_year($value, $request->input('to_month','Y-m'));
                    $report_month = Report::where([['journal_entry_id','!=',$id], ['member_id','=',$request->input('member_id')], ['delstatus','<', '1'], ['status', '>', '0'], ['money_pending', '=', '0']])->orderBy('month', 'ASC')->pluck('month')->toArray();
                    foreach($month_arr as $mt){
                        if(in_array($mt, $report_month)){
                            $fail("The money of ".$mt."is already paid");
                        }
                    }
                    $report_model = Report::where([['journal_entry_id','!=',$id], ['delstatus','<', '1'], ['status', '>', '0']])->orderBy('month', 'DESC')->get()->toArray();
                    $report_model2 = Report::where([['member_id','=',$request->input('member_id')], ['delstatus','<', '1'], ['status', '>', '0'], ['money_pending','>', '0']])->orwhere([['member_id','=',$request->input('member_id')], ['delstatus','<', '1'], ['status', '>', '0']])->orderBy('month', 'DESC')->first();
                    $last_month = $report_model->month ?? '0000-00';
                    $act_from_mon = $report_model2->month ?? '9999-12';
                    if(empty($report_model2->money_pending)){
                        $date = Carbon::createFromFormat('Y-m', $act_from_mon);
                        $date->addMonth();
                        $act_from_mon = $date->format('Y-m');
                    }
                    $to_month = $request->input('to_month');
                    // if($value <= $last_month){
                    //     $fail('The money for further month is already paid kindly select the next month to pay the money');
                    // }
                    if($value>$act_from_mon){
                        $fail('The money of previous month is not paid please select previous month to pay');
                    }
                    if($to_month < $value){
                        $fail('From Month should not be ahead of To Month');
                    }
                }
            ],
            'to_month' => 
            [
                empty($request->input('custom_month')) && $charge_name == 'maintenance' ? 'required':'nullable',
                function($attribute, $value, $fail) use($id) {
                    $hlp = new helpers;
                    $request = Request();
                    $member = \App\Models\Members::find($request->input('member_id'));
                    $charge = \App\Models\Charges::find($member->charges_id)->rate;
                    $mon_arr = $hlp->get_financial_month_year($request->from_month, $value,'Y-m');
                    // $mon_arr = $helpers->get_financial_month_year($request->from_month, $value);
                    $report_model2 = Report::where([['member_id','=',$request->input('member_id')],['journal_entry_id','<>' ,$id], ['delstatus','<', '1'], ['status', '>', '0'], ['money_pending','>', '0']])->orderBy('month', 'ASC')->first();
                    $pending_money = $report_model2->money_pending ?? 0;
                    $count= count($mon_arr);
                    if(!empty($report_model2->money_pending)) $count =$count-1;
                    if(!empty($request->paid_money)){
                        if($count !=ceil(($request->paid_money-$pending_money)/$charge)){
                            $fail('Amount Paid and selected month are not proportional kindly adjust the value or month');
                        }
                    }
                }
            ],
            'custom_month' => 
            [
                empty($request->input('from_month')) && $charge_name == 'maintenance' ? 'required':'nullable',
                function($attribute, $value, $fail){
                    $val_arr = [];
                    $val_arr = explode(',',$value);
                    $request = Request();
                    foreach($val_arr as $vl){
                        $report = Report::where([['member_id', '=', $request->input('member_id')],['status','>', '0'], ['delstatus','<','1'], ['month', '=', $vl]])->count();
                        if($report>0){
                            $fail("The Amount of this month is already paid");
                        }
                    }
                    $report_model2 = Report::where([['member_id','=',$request->input('member_id')], ['delstatus','<', '1'], ['status', '>', '0'], ['money_pending','>', '0']])->orderBy('month', 'ASC')->first();
                    $member = \App\Models\Members::find($request->input('member_id'));
                    $charge = \App\Models\Charges::find($member->charges_id)->rate;
                    $pending_money = $report_model2->money_pending ?? 0;
                    $count = count($val_arr);
                    if(!empty($report_model2->money_pending)) $count =$count-1;
                    if(!empty($request->paid_money)){
                        if($count !=ceil(($request->paid_money-$pending_money)/$charge)){
                            $fail('Amount Paid and selected month are not proportional kindly adjust the value or month');
                        }
                    }
                }
            ],
            'payment_mode' => 'required',
            'member_id' => 'required|numeric'
        ]);

        $request_data = $request->input();
        $report_data = [];
        $member = \App\Models\Members::find($request_data['member_id']);
        $charge = \App\Models\Charges::find($member->charges_id)->rate;
        $charge_type = \App\Models\ChargeType::find($request_data['charge_type_id']);
        if($charge_type->type == 'fine'){
            $entrywise_model = Entrywise_Fine::where([['member_id','=',$request_data['member_id']],['status','>','0'],['delstatus','<','0']])->orderBy('id','DESC')->first();
            if($entrywise_model->journal_entry_id == $id){
                // $request_data['partial'] = 0;
                $name = $model->where('organization_id',$request_data['organization_id'])->orderBy('entry_date','DESC')->first();
                $date = $request_data['entry_date'];
                $request_data['charge'] = $request_data['fine_amt'];
                $request_data['name'] = $member['name'];
                $fetch_data = $modelfind->update($request_data);

                // Entry wise fine...

                $monthwise_fine_model = new Monthwise_Fine();

                $monthwise_model = $monthwise_fine_model->where([['member_id','=',$fetch_data->member_id],['delstatus','<','1'],['status','>','0']])->orderBy('month','DESC')->first();
                $month = '2024-07';
                if(!empty($monthwise_model->month) && $month<$monthwise_model->month){
                    $month = $monthwise_model->month;
                }
                $mon_arr = $helpers->get_financial_month_year('2024-07',$month,'Y-m');
                $total_fine =0;
                $monthwise_fine_model = where([['entrywise_fine_id', '=',$entrywise_model->id]])->delete();
                foreach($mon_arr as $ma){
                    $monthwise_mod = $monthwise_fine_model->where([['member_id','=',$fetch_data->member_id],['delstatus','<','1'],['status','>','0'],['month','=',$ma]])->first();
                    if(empty($monthwise_mod)){
                        $total_fine += $this->late_fee_calculator($request_data['entry_date'],$ma,$fetch_data->member_id);
                    } else{
                        $total_fine += $monthwise_mod->fine_amount;
                    }
                }
                $entrywise_model = Entrywise_Fine::where('journal_entry_id','=',$id)->first();
                $entrywise_arr =[];
                $entrywise_arr['member_id'] = $fetch_data->member_id;
                $entrywise_arr['fine_paid'] = $request_data['fine_amt'];
                $entrywise_arr['total_fine'] = $total_fine;
                $create_data = $entrywise_model->update($entrywise_arr);

                // $monthwise_model->where([['entrywise_fine_id', '=',null],['status','>','0'],['delstatus','<','1'],['fine_waveoff','=','0']])->update(['entrywise_fine_id','=',$create_data->id],['fine_waveoff','=','1']);
            }
            else{
                return redirect()->route($module['main_route'].'.index')->with('info', 'Only update last fine of a particular member');
            }
        } else if($charge_type->type == 'maintenance'){
            $from_month = $request->input('from_month');
            $to_month = $request->input('to_month');
            $report_model = new Report();

            $month_arr =[];
            if(!empty($request->input('from_month')) && !empty($request->input('to_month'))){
                $month_arr = $helpers->get_financial_month_year($from_month,$to_month,'Y-m');
            } else{
                $month_arr = explode(',', $request->input('custom_month'));
            }

            $paid = empty($request->input('paid_money')) ? $charge*count($month_arr) : $request->input('paid_money');

            if(!empty($paid)){
                $request_data['partial'] = ($paid%$charge > 0)? '1':'0';
                $count=0;
                $count =ceil($paid/$charge);
            } else{
                $report_model_last_mon = $report_model->where([['month', '=', $from_month], ['member_id', '=', $request_data['member_id']], ['delstatus','<','1'], ['status','>', '0']])->orderBy('month','DESC')->first();
                $dedt_amt=0;
                if(!empty($report_model_last_mon)) $dedt_amt = $report_model_last_mon->money_paid;
                $request_data['paid_money'] = count($month_arr)*$charge - $dedt_amt;
            }

            $report_find = Report::where([['journal_entry_id','=',$id],['status','>','0'],['delstatus','<','1']])->orderBy('month', 'DESC')->get()->toArray();
            $prev_paid_money = $modelfind->charge;
            foreach(array_reverse($report_find) as $rf){
                if($prev_paid_money >= $rf['money_paid']){
                    $prev_paid_money = $prev_paid_money-$rf['money_paid'];
                    $rep = Report::where('id','=',$rf['id'])->delete();
                } else{
                    $journal_entry = $model->where([['to_month', '=', $rf['month']],['member_id','=', $rf['member_id']]])->orderBy('id','DESC')->first();
                    $chr = $rf['money_paid'] - $prev_paid_money;
                    $rep =Report::where('id', '=', $rf['id'])->update(['money_paid'=> $chr, 'journal_entry_id'=> $journal_entry->id]);
                }
            }

            $request_data['charge'] = $request_data['paid_money'];
            $request_data['name'] = $member['name'];
            $modelfind->update($request_data);


            foreach($month_arr as $mt){
                $this->late_fee_calculator($request_data['entry_date'],$mt,$request_data['member_id']);
                $report_models = $report_model->where([['month', '=',$mt], ['member_id','=',$request_data['member_id']], ['delstatus','<','1'],['status','>','0']])->first();
                // $tempe_arr[] = $report_models->id;
                $report_data['journal_entry_id'] = $id;
                if(empty($report_models)){
                    $report_data['month'] = $mt;

                    $report_data['member_id'] = $request_data['member_id'];
                    if($charge <= $paid){
                        $report_data['money_paid'] = $charge;
                        $report_data['money_pending'] = 0;
                        $paid = $paid - $charge;
                    } else{
                        $report_data['money_paid'] = $paid;
                        $report_data['money_pending'] = $charge - $paid;
                        $paid = 0;
                    }
                    $upd_rep = $report_model->create($report_data);
                } else{
                    $money = $report_models->money_pending;
                    if(!empty($money)){
                        $report_data['month'] = $mt;
                        if($money <= $paid){
                            $report_data['money_paid'] = $charge;
                            $report_data['money_pending'] = 0;
                            $paid = $paid - $money;
                        } else{
                            $report_data['money_paid'] = $paid + $report_models->money_paid;
                            $report_data['money_pending'] = $money - $paid;
                            $paid = 0;
                        }
                        $report_model->where('id','=',$report_models->id)->update($report_data);
                    }
                }
                $report_data =[];
            }
        } else{
            $entry_charge_arr =[];
            // $name = $modelfind->orderBy('entry_date','DESC')->first();
            $request_data['partial'] ='0';
            $date = $request_data['entry_date'];
            // $pre_date = !empty($name)? $name->entry_date : '0000-00-00 00:00:00';
            // if(strtotime($date) > strtotime($pre_date)){
            
            $request_data['charge'] = $request_data['paid_money'];
            $request_data['name'] = $member['name'];
            // $request_data['series_next_number'] = $next_number;
            // $request_data['series_number'] = $series_num;
            $fetch_data = $modelfind->update($request_data);
        }

        $this->generate_pdf_file($id);

        //  api message function
        if(!empty($request_data['send'])){
            $this->whatsapp_msg($id);
        }

        dump(1);
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

    public function generate_pdf_file($je_id){
        $module = $this->module;
        $request = Request();
        $journal_model = new \App\Models\Journal_Entry();
        $journal_entry = $journal_model->where([['id', '=', $je_id]])->first();
        $mem_id = $journal_entry->member_id;
        $member = \App\Models\Members::find($mem_id);
        $setting_model = new \App\Models\Settings();
        $organization = \App\Models\Organization::find($member->organization_id);
        $name = $organization->name;
        if($journal_entry->file_name && file_exists(public_path('upload/pdf_files/'.$journal_entry->file_name.'.pdf'))) {
             unlink(public_path('upload/pdf_files/' . $journal_entry->file_name.'.pdf'));
             $file_name = $journal_entry->file_name;
        }
        else{
            $now=Carbon::now();
            $file_name = $je_id.'-'.$now->format('Y-m-d-H-i-s');
        }
        $charge_type_id = $journal_entry->charge_type_id;
        $charge = \App\Models\ChargeType::where([['status','>','0'],['delstatus','<','1'],['id','=',$charge_type_id]])->first();
        $rec_name = $journal_entry->name;
        $data = [
            'charge_type_type'=>$charge->type,
            'org_name' => $name,
            'note' => $setting_model->getVal('pdf', 'pdf_note'),
            'line1' => $setting_model->getVal('pdf', 'line1'),
            'address' => $organization->address,
            'name' =>$rec_name,
            'mobile_number' => $member->mobile_number,
            'charge' => $journal_entry->charge,
            'series' => $journal_entry->series_number,
            'from_month' => $journal_entry->from_month,
            'to_month' => $journal_entry->to_month,
            'mode' => $journal_entry->payment_mode,
            'date' => $journal_entry->entry_date,
            'reciept_date' => $journal_entry->reciept_date,
            'year' => $journal_entry->entry_year,
            'charge_type_id' => $journal_entry->charge_type_id
        ];
        // dd($data);
        if($charge->type == 'fine'){
            $entrywise_model = \App\Models\Entrywise_Fine::where('journal_entry_id','=',$je_id)->first();
            $data['from_month'] ='';
            $data['to_month'] ='';
            $data['charge'] = $entrywise_model->fine_paid;

            $data['fine_days'] = $this->calculate_fine_days($entrywise_model->id);
            $this->calculate_fine_days($entrywise_model->id);
        } else if($charge->type == 'maintenance'){
            $data['from_month'] =$journal_entry->from_month;
            $data['to_month'] =$journal_entry->to_month;
            $data['fine_days'] = '';
        } else if($charge->type == 'others'){
            $data['from_month'] = '';
            $data['to_month'] = '';
            $data['fine_days'] = '';
        }
        $pdf = PDF::loadView('include.make_pdf', $data);

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
        $mpdf->Output(public_path("upload/pdf_files/{$file_name}.pdf"), \Mpdf\Output\Destination::FILE);
        $journal_model->where('id', '=', $je_id)->update(['file_name'=> $file_name]);
        if(!empty($request->input('redirect'))){
            return redirect()->route($module['main_route'].'.show_pdf', $je_id);
        }
        if(!empty($request->input('redirect_index'))){
            return redirect()->route($module['main_route'].'.index')->with('success', 'File regenerated Successfully');
        }
    }

    public function calculate_fine_days($fine_entry_id){
        $monthwise_model = Monthwise_Fine::where('entrywise_fine_id',$fine_entry_id)->get()->toArray();
        $fine_days = 0;
        if(!empty($monthwise_model)){
            foreach($monthwise_model as $mt){
                if($mt['fine_amount'] != 1000){
                    $fine_days += $mt['fine_amount']/50;
                } else{
                    $fine_days += 30;
                }
            }
        }
        return $fine_days;
    }

    public function view_pdf($je_id){
        $journal_entry = \App\Models\Journal_Entry::find($je_id);
        $mem_id = $journal_entry->member_id;
        $member = \App\Models\Members::find($mem_id);
        $setting_model = new \App\Models\Settings();
        $rec_name = !empty($member->sublet_name) ? $member->sublet_name : $member->name;
        $data = [
            'note' => $setting_model->getVal('pdf', 'pdf_note'),
            'line1' => $setting_model->getVal('pdf', 'line1'),
            'address' => $setting_model->getVal('pdf', 'address'),
            'name' => $rec_name,
            'mobile_number' => $member->mobile_number,
            'charge' => $journal_entry->charge,
            'series' => $journal_entry->series_number,
            'from_month' => $journal_entry->from_month,
            'to_month' => $journal_entry->to_month,
            'mode' => $journal_entry->payment_mode,
            'date' => $journal_entry->entry_date,
            'reciept_date' => $journal_entry->reciept_date,
            'year' => $journal_entry->entry_year
        ];
        $pdf = PDF::loadView('include.make_pdf',$data);
        $pdf->stream();
    }

    public function show_pdf($je_id){

        $journal_entry = \App\Models\Journal_Entry::find($je_id);
        $name = $journal_entry->file_name;
        return view('include.show_pdf',compact('name'));
    }

    public function send_msg(Request $request, helpers $helpers){
        $module = $this->module;
        $je_id = $request->input('je_id');
        $api = $this->whatsapp_api;
        $model = \App\Models\Journal_Entry::find($je_id);
        $org_id = $model->organization_id;
        $category = 'journal_entry';
        $member = \App\Models\Members::find($model->member_id);
        $dest_mob_no = $member->mobile_number;
        $file_name = $model->file_name;
        $mem_id = $model->member_id;

        $message = array(
            'type' => $api['type'],
            $api['type'] => array(
                'link' => url('/upload/pdf_files/'.$file_name.'.pdf'),
                // 'link' => 'https://gvtapp.netpyx.org/supanel/journal_entry/921/show',

                'filename' => 'Reciept'
            )
        );
        $date_arr = explode(' ', $model->entry_date);
        $date = Carbon::parse($date_arr[0])->format('d-M-Y');
        if($model->from_month == $model->to_month){
            $month = Carbon::parse($model->from_month)->format('M Y');
        } else{
            $month = Carbon::parse($model->from_month)->format('M Y')."-".Carbon::parse($model->to_month)->format('M Y');
        }
        // $a = 'charge';
        $data = [
            'name'=> $model->name,
            'date'=> $date,
            'year'=> $model->entry_year,
            'mobile_number' => $member->mobile_number,
            'charge' => $model->charge,
            'month' => $month,
            'serial_no' => $model->series_number,
            'mode' =>$model->payment_mode,
            'unit_no'=> $member->unit_number
        ];

        $entrywise_model = \App\Models\Entrywise_Fine::where([['journal_entry_id', '=', $je_id], ['status','>','0'],['delstatus','<','1']])->first();
        if(!empty($entrywise_model)){
            $data['fine_days'] = $this->calculate_fine_days($entrywise_model->id);
            $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','fine'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();
            $templ_json = $helpers->make_temp_json($temp->id, $data);
            $message = json_encode($message, true);

            if(!empty($member->sublet_message) && $member->sublet_message!='null'){
               $sublet_msg_arr =json_decode($member->sublet_message);
            }else{
               $sublet_msg_arr =[];
            }

            if(!empty($member->mobile_message) && $member->mobile_message!='null'){
               $mobile_msg_arr =json_decode($member->mobile_message);
            }else{
               $mobile_msg_arr =[];
            }


            if(in_array('reciept',$mobile_msg_arr)){
                dispatch( new WhatsappAPI($dest_mob_no,$message, $org_id,$templ_json, $category, $mem_id, $je_id) )->onConnection('sync');

                // return redirect()->route($module['main_route'].'.index')->with('success', 'Message send Successfully');
                return '';
                
            }
            if(in_array('reciept',$sublet_msg_arr)){
                $dest_mob_no = $member->sublet_number;
                if(!empty($dest_mob_no)){
                    dispatch( new WhatsappAPI($dest_mob_no,$message, $org_id,$templ_json, $category, $mem_id, $je_id) )->onConnection('sync');
                    // return redirect()->route($module['main_route'].'.index')->with('success', 'Message send Successfully');
                    return '';
                }

            }

        } else{
            $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','reciept'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();
            $templ_json = $helpers->make_temp_json($temp->id, $data);
            $message = json_encode($message, true);
            if(!empty($member->sublet_message) && $member->sublet_message!='null'){
               $sublet_msg_arr =json_decode($member->sublet_message);
            }else{
               $sublet_msg_arr =[];
            }

            if(!empty($member->mobile_message) && $member->mobile_message!='null'){
               $mobile_msg_arr =json_decode($member->mobile_message);
            }else{
               $mobile_msg_arr =[];
            }

            if(in_array('reciept',$mobile_msg_arr)){
                dispatch( new WhatsappAPI($dest_mob_no,$message, $org_id,$templ_json, $category, $mem_id, $je_id) )->onConnection('sync');

                // return redirect()->route($module['main_route'].'.index')->with('success', 'Message send Successfully');
                return '';
                
            }
            if(in_array('reciept',$sublet_msg_arr)){
                $dest_mob_no = $member->sublet_number;
                if(!empty($dest_mob_no)){
                    dispatch( new WhatsappAPI($dest_mob_no,$message, $org_id,$templ_json, $category, $mem_id, $je_id) )->onConnection('sync');
                    // return redirect()->route($module['main_route'].'.index')->with('success', 'Message send Successfully');
                    return '';
                }
            }
        }

        // return redirect()->route($module['main_route'].'.index')->with('info', "Message can't be sent");
        return 'fail';

        // dispatch( new WhatsappAPI($dest_mob_no,$message, $org_id,$templ_json) )->onConnection('sync');

    }

    public function ajax_member(Request $request) {
        $input=$request->input('q');
        // dd($request->input());
        
        $org_id = $request->input('org_id');
        $arr=[];
        $models =new \App\Models\Members();
        $name = $models->select('id', 'name', 'unit_number', 'mobile_number')->where([['delstatus', '<', '1'],['status', '>', '0'], ['organization_id', '=', $org_id]])->where(DB::raw("CONCAT_WS(' ', name, unit_number, mobile_number, alternate_name_1, alternate_name_2, alternate_number, sublet_name)"), 'like', '%'.$input.'%')->get()->toArray();
        $count=0;
        foreach($name as $nm){
            $arr[$count]['id']= $nm['id'];
            $arr[$count]['text']="Name : ".$nm['name']."; Unit No : ".$nm['unit_number']."; Mob No : ".$nm['mobile_number'];
            // $arr[$count]['name'] = $nm['name'];
            // $arr[$count]['desc']= $nm['unit_number'].$nm['name'].$nm['mobile_number'];
            $count++;
        }
        return $arr;
    }

    public function series_select(Request $request) {
        $module = $this->module;
        $input=$request->input('org_id');
        $models =new \App\Models\Series();
        $data = $models->select('id', 'name')->where('organization_id',$input)->orderBy('id','DESC')->get()->toArray();
        $row_data =[];
        foreach($data as $ds){
            $row_data[$ds['id']] = $ds['name'];
        }
        $html_data = view($module['main_view'].'.series_select', compact(['row_data']))->render();

        $response = response()->json(['html'=>$html_data]);
        return $response;
    }

    public function series_data(Request $request) {
        $module = $this->module;
        $input=$request->input('ser_id');
        $models = \App\Models\Series::find($input);
        $series_num =$models->name.$models->number_separator.str_pad($models->next_number,$models->min_length,'0', STR_PAD_LEFT);
        $next_num = $models->next_number;
        $response = response()->json(['serial_no'=>$series_num, 'next_num' =>$next_num]);
        return $response;
    }

    public function late_fee_calculator($date, $month,$member_id){
        $now = Carbon::now();
        $date = Carbon::parse($date);
        $day = $date->day;
        $curr_month = $now->format('Y-m');
        $day_dif = $day - 12;
        $late_fee =0;
        $mon_dif = Carbon::parse($month)->diffInMonths($now);

        if($month>'2024-06'){
            if($mon_dif == 0){
                if($day_dif>0 ){
                    $late_fee = ($day-12)*50;
                }
            } else{
                $late_fee =1000;
            }
            $monthwise_model = new Monthwise_Fine();
            $check_model = $monthwise_model->where([['member_id', '=', $member_id], ['month','=',$month], ['status','>','0'], ['delstatus', '<', '1']])->first();
            $fine_arr = [];
            if(empty($check_model)){
                $fine_arr['month'] = $month;
                $fine_arr['member_id'] = $member_id;
                $fine_arr['fine_amount'] = $late_fee;
                $monthwise_model->create($fine_arr);
            } else{
                $fine_arr['month'] = $month;
                $fine_arr['member_id'] = $member_id;
                $fine_arr['fine_amount'] = $late_fee;
                $monthwise_model->where('id',$check_model->id)->update($fine_arr);
            }
        }

        return $late_fee;
    }

    public function whatsapp_msg($je_id){
        $model = new DefaultModel();
        $modl_find = $model->find($je_id);
        $file_name = $modl_find->file_name;
        $org_id = $modl_find->organization_id;
        $api = $this->whatsapp_api;
        $category = 'journal_entry';
        $helpers = new helpers();
        $member = \App\Models\Members::find($modl_find->member_id);
        $mem_id = $modl_find->member_id;
        $message = array(
            'type' => $api['type'],
            $api['type'] => array(
                'link' => url('/upload/pdf_files/'.$file_name.'.pdf'),
                'filename' => 'Reciept'
            )
        );
        $date_arr = explode(' ', $modl_find->entry_date);
        $date = Carbon::parse($date_arr[0])->format('d-M-Y');
        if(empty($modl_find->from_month) && empty($modl_find->to_month)){
            if($modl_find->from_month != $modl_find->to_month){
                $month = Carbon::parse($modl_find->from_month)->format('M Y')."-".Carbon::parse($modl_find->to_month)->format('M Y');
            } else{
                $month = Carbon::parse($modl_find->from_month)->format('M Y');
            }
        } else{
            $month = '';
            $val_ar = explode(',',$modl_find->custom_data);
            $count = 0;
            foreach($val_ar as $vl){
                $count++;
                if($count == count($val_ar)){
                    $month = $month.Carbon::parse($vl)->format('M Y');
                } else{
                    $month = $month.Carbon::parse($vl)->format('M Y').',';
                }
            }
        }
        $data = [
            'name'=> $modl_find->name,
            'date'=> $date,
            'year'=> $modl_find->entry_year,
            'mobile_number' => $member->mobile_number,
            'charge' => $modl_find->charge,
            'month' => $month,
            'serial_no' => $modl_find->series_number,
            'mode' =>$modl_find->payment_mode,
            'unit_no'=> $member->unit_number
        ];
        $entrywise_model = \App\Models\Entrywise_Fine::where([['journal_entry_id', '=', $je_id], ['status','>','0'],['delstatus','<','1']])->first();
        if(!empty($entrywise_model)){
            $data['fine_days'] = $this->calculate_fine_days($entrywise_model->id);
            $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','fine'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();
            $destination = $member->mobile_number;
            $message = json_encode($message,true);

            if(!empty($member->sublet_message) && $member->sublet_message!='null'){
               $sublet_msg_arr =json_decode($member->sublet_message);
            } else{
               $sublet_msg_arr =[];
            }

            if(!empty($member->mobile_message) && $member->mobile_message!='null'){
               $mobile_msg_arr =json_decode($member->mobile_message);
            } else{
               $mobile_msg_arr =[];
            }
            if(in_array('reciept',$mobile_msg_arr)){
                dispatch( new WhatsappAPI($destination,$message, $org_id,$templ_json, $category, $mem_id, $je_id) )->onConnection('sync');
            }
            if(in_array('reciept',$sublet_msg_arr)){
                $destination = $member->sublet_number;
                if(!empty($destination)){
                    dispatch( new WhatsappAPI($destination,$message, $org_id,$templ_json, $category, $mem_id, $je_id) )->onConnection('sync');
                }
            }

        } else{
            $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','reciept'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();
            $templ_json = $helpers->make_temp_json($temp->id, $data);
            $member_id = $modl_find->member_id;
            $member = \App\Models\Members::find($member_id);
            $destination = $member->mobile_number;
            $message = json_encode($message,true);
            if(!empty($member->sublet_message) && $member->sublet_message!='null'){
               $sublet_msg_arr =json_decode($member->sublet_message);
            }else{
               $sublet_msg_arr =[];
            }

            if(!empty($member->mobile_message) && $member->mobile_message!='null'){
               $mobile_msg_arr =json_decode($member->mobile_message);
            }else{
               $mobile_msg_arr =[];
            }
            if(in_array('reciept',$mobile_msg_arr)){
                dispatch( new WhatsappAPI($destination,$message, $org_id,$templ_json, $category, $mem_id, $je_id) )->onConnection('sync');
            }
            if(in_array('reciept',$sublet_msg_arr)){
                $destination = $member->sublet_number;
                if(!empty($destination)){
                    dispatch( new WhatsappAPI($destination,$message, $org_id,$templ_json, $category, $mem_id, $je_id) )->onConnection('sync');
                }
            }
        }
    }

    public function fine_month_store($fr_month, $to_month, $pay_date, $je_id){
        $now = Carbon::now();
        $month_arr = helpers::get_financial_month_year($fr_month, $to_month, 'Y-m');
        $fine_arr = [];
        $month_fine = new Monthwise_Fine();
        $je_model = DefaultModel::find($je_id);
        foreach($month_arr as $mn){
            $late_fee = 0;
            $fine_arr['month'] = $mn;
            $fine_arr['journal_entry_id'] = $je_id;
            $fine_arr['member_id'] = $je_model->member_id;
            $fine_arr['fine_amount'] = $je_model->member_id;
            $fine_arr['fine_waveoff'] = 0;
        }
    }

    public function fine_ajax(Request $request){
        $member_id = $request->input('member_id');
        $fine_model = Monthwise_Fine::where([['member_id','=',$member_id],['fine_waveoff','=','0'],['fine_amount','>','0'],['status','>','0'],['delstatus','<','1']])->get();

        $total_fine =0;
        if(!empty($fine_model)){
            foreach($fine_model as $fm){
                $total_fine = $total_fine+$fm->fine_amount;
            }
        }
        return $total_fine;
    }

    public function get_table(Request $request){
        $member_id = $request->input('member_id');
        $je_model = DefaultModel::where([['member_id','=',$member_id],['status','>','0'],['charge_type_id','=','7'],['delstatus','<','1']])->latest()->take(3)->get()->toArray();
        $data_arr =[];
        foreach($je_model as $jm){
            $temp_arr =[];
            $temp_arr['name'] = $jm['name'] ?? '';
            $temp_arr['series_number'] = $jm['series_number'];
            $temp_arr['entry_date'] = $jm['entry_date'];
            $temp_arr['charge'] = $jm['charge'];
            if(empty($jm['custom_month'])){
                $temp_arr['month'] = $jm['from_month']!=$jm['to_month']? $jm['from_month'].' - '.$jm['to_month'] : $jm['from_month'];
            } else{
                $temp_arr['month'] = $jm['custom_month'];
            }
            $data_arr[] =$temp_arr;
        }
        return $data_arr;
    }

    function get_charge(Request $request){
        $id = $request->input('id');
        $charge = \App\Models\ChargeType::where([['status','>','0'],['delstatus', '<', '1'],['id','=',$id]])->first();
        return $charge->type;
    }

//     public function sendPdfToWhatsapp($destination,$message, $org_id, $template){
//         $module = $this->module;
//         $api = $this->whatsapp_api;
//         $model = new \App\Models\Organization_Settings();
//         $src_no = $model->getVal($module['group'], 'source_number',$org_id);
//         $api_key = $model->getVal($module['group'], 'api_key',$org_id);
//         $curl = curl_init();

// // curl_setopt_array($curl, array(
// //   CURLOPT_URL => 'https://api.gupshup.io/wa/api/v1/template/msg',
// //   CURLOPT_RETURNTRANSFER => true,
// //   CURLOPT_ENCODING => '',
// //   CURLOPT_MAXREDIRS => 10,
// //   CURLOPT_TIMEOUT => 0,
// //   CURLOPT_FOLLOWLOCATION => true,
// //   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
// //   CURLOPT_CUSTOMREQUEST => 'POST',
// //   CURLOPT_POSTFIELDS => 'source='.$src_no.'&destination='.$destination.'&message='.$message,
// //   CURLOPT_HTTPHEADER => array(
// //     'Content-Type: application/x-www-form-urlencoded',
// //     'Apikey: '.$api_key
// //   ),
// // ));
//         // $post_field = 'channel='.$api['channel'].'&source='.$src_no.'&destination='.$destination.'&src.name='.$api['src_name'].'&template={"id":"'.$templ_id.'","params":'.$params.'}&message='.$message;
//         // $post_field_encode = urlencode($post_field);
//         // dd($post_field_encode);
//
//         $post_data = [];

//         // $template_arr = [
//         //     'id'=>$templ_id,
//         //     'params'=>$params
//         // ];

//         $post_data['channel'] = $api['channel'];
//         $post_data['source'] = $src_no;
//         $post_data['destination'] = $destination;
//         $post_data['src.name'] = $api['src_name'];
//         $post_data['template'] = $template;
//         $post_data['message'] = $message;

//         // dd($post_data);
//         // dd($api['whatsapp_api_url']);
//         // dd($api_key);

//         curl_setopt_array($curl, array(
//             CURLOPT_URL => $api['whatsapp_api_url'],
//             CURLOPT_RETURNTRANSFER => true,
//             CURLOPT_ENCODING => '',
//             CURLOPT_MAXREDIRS => 10,
//             CURLOPT_TIMEOUT => 0,
//             CURLOPT_FOLLOWLOCATION => true,
//             CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//             CURLOPT_CUSTOMREQUEST => 'POST',
//             CURLOPT_POSTFIELDS => Arr::query($post_data),
//             CURLOPT_HTTPHEADER => array(
//                 'Content-Type: application/x-www-form-urlencoded',
//                 'Apikey:'.$api_key
//             ),
//         ));
//         $response = curl_exec($curl);
//         curl_close($curl);
//         echo $response;
//     }
}