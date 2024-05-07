<?php
namespace App\Http\Controllers\Admin;

use App\Models\Journal_Entry as DefaultModel;
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

class JournalEntryController extends Controller{
    public $module = array(
        'module_view' => 'journal_entry',
        'module_route' => 'journal_entry',
        'permission_group' => 'journal_entry',
        'main_heading' => 'Journal Entries',
        'start_date' => null,
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

    public function index(Request $request, DefaultModel $model, helpers $helpers){
        $carbon = new Carbon();
        $module = $this->module;
        $perpage = $request->perpage ?? $module['default_perpage'];
        $title_showns = 'Add '.$module['main_heading'];
        $method = 'POST';
        $mode = 'insert';
        $action = URL::route($module['main_route'].'.store');
        $act = URL::route($module['main_route'].'.store');
        // dd($action);
        if(!$request->perpage && !empty($request->cookie('perpage'))) $perpage = $request->cookie('perpage');

        $financial_years = $helpers->get_financial_years($module['start_date'], null);
        
        $model_get = $model;
        if($model->getDelStatusColumn()) $model_get = $model_get->where($model->getDelStatusColumn(), '<', '1');
        
        if($model->getSortOrderColumn()) $model_get = $model_get->orderBy($model->getSortOrderColumn(), 'ASC');
        else $model_get = $model_get->latest();
        
        $query = $request->get('query') ?? '';
        if($query!='') $model_get = $model_get->where('name', 'LIKE', '%'.$query.'%');

        $data = $model_get->paginate($perpage)->onEachSide(2);

        $title_shown = 'Manage '.$module['main_heading'];
        $folder = $this->folder;

        return view($module['main_view'].'.index', compact('data', 'action', 'method', 'act', 'model', 'mode', 'carbon', 'module', 'perpage', 'folder', 'title_shown', 'title_showns', 'query', 'financial_years'))->with('i', ($request->input('page', 1) - 1) * $perpage);
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
        $module = $this->module;
        $auth_user = Auth::user();  
        $roles = $auth_user->roles()->pluck('name','id')->toArray();
        // dd($request->input());
        $rules= [
            'series_id' => 'required|numeric',
            'entry_year' => 'required',
            'entry_date' => 'required|date_format:Y-m-d H:i:s',
            // 'form_data.member_mob' => 'required',
            'member_id' => 'required|numeric',
            'payment_mode' => 'required|in:online,cash',
            'from_month' => 'required',
            'to_month' => 'required'
        ];
        $validator=\Illuminate\Support\Facades\Validator::make([], []);
        if(in_array('1', array_keys($roles))){
            $rules['organization_id'] = 'required|numeric';
        }
        $from_month = $request->input('from_month');
        $to_month = $request->input('to_month');
        if($to_month > $from_month){
            $month_no = count($helpers->get_financial_month_year($from_month, $to_month ,'Y M'));
            if($month_no>3) $validator->errors()->add('to_month',"Interval should be within the 3 month between From and To Month");
        } else $validator->errors()->add('from_month',"From Month should not be ahead of To Month");
        $check = \App\Models\Members::where('id',$request->input('member_id'))->count();
        
        $series_number = \App\Models\Series::find($request->input('series_id'));
        if(empty($check)) $validator->errors()->add('member_id',"Choose a valid Member");
        if(empty($series_number->count)) $validator->errors()->add('series_id',"Choose a valid Series");
        $request->validate($rules);
        $member = \App\Models\Members::find($request->input('member_id'));
        $charge = \App\Models\Charges::find($member->charges_id)->rate;
        $name = $model->where('organization_id',$request->input('organization_id'))->orderBy('entry_date','DESC')->first();
        $date = $request->input('entry_date');
        $pre_date = !empty($name)? $name->entry_date : '0000-00-00 00:00:00';
        if(strtotime($date) > strtotime($pre_date)){
            $series_num =$series_number->name.$series_number->number_separator.str_pad($series_number->next_number,$series_number->min_length,'0', STR_PAD_LEFT);
            $charge = empty($request->input('paid_money')) ? $charge : $request->input('paid_money');
            $next_number = $series_number->next_number;
            $upd = \App\Models\Series::where('id','=',$series_number->id)->update(['next_number'=>$series_number->next_number+1]);
            $request->merge([ 'series_number' => $series_num, 'series_next_number' => $next_number, 'charge' => $charge ]);
            $fetch_data = $model->create($request->all());
            $now=Carbon::now();
            $store_path = "upload/pdf_files/";
            $pdfFilePath = "upload/pdf_files/";
            $file_name = $fetch_data->id.'-'.$now->format('Y-m-d-H-i-s');
            
            $data = [
                'name' => $member->name,
                'mobile_number' => $member->mobile_number,
                'charge' => $charge,
                'series' => $series_num,
                'date' => $request->input('entry_date'),
                'year' => $request->input('entry_year')
            ];
            $pdf = PDF::loadView('include.make_pdf', $data);
            // Storage::put('upload/pdf_files/'.$file_name.'.pdf');
            $pdf->save(public_path("upload/pdf_files/{$file_name}.pdf"));
            $models=$model->find($fetch_data->id);
            $models->update(['file_name'=> $file_name]);
            return redirect()->route($module['main_route'].'.index')->with('success', $module['main_heading'].' created successfully.');
        }
    }

     public function show(Request $request, $id, DefaultModel $model, helpers $helpers){
        $module = $this->module;
        $folder = $this->folder;
        $form_data = $model->find($id);
        $form_data['member_mob'] = \App\Models\Members::find($form_data->member_id)->mobile_number;
        $title_shown = 'Show '.$module['main_heading'];
        $mode = 'show';
        $financial_years = $helpers->get_financial_years($module['start_date'], null);
        
        if($request->ajax()) {
            $html_data = view($module['main_view'].'.form_include', compact(['form_data','id', 'module', 'mode', 'financial_years']))->render();
            $response = response()->json(['html'=>$html_data, 'title_shown'=>$title_shown, 'mode'=>$mode, 'id'=>$id]);
            return $response;
        } else{
            return view($module['main_view'].'.cred2', compact('form_data', 'financial_years', 'model', 'module', 'folder', 'title_shown', 'mode', 'id'));
        }
    }

    // public function edit(Request $request, $id, DefaultModel $model, helpers $helpers){
    //     $module = $this->module;
    //     $folder = $this->folder;
    //     $form_data = $model->find($id);
    //     $form_data['member_mob'] = \App\Models\Members::find($form_data->member_id)->mobile_number;
    //     $action = URL::route($module['main_route'].'.update', $id);
    //     $title_shown = 'Edit '.$module['main_heading'];
    //     $method = 'PUT';
    //     $mode = 'edit';
    //     $financial_years = $helpers->get_financial_years($module['start_date'], null);
    //     if($request->ajax()) {
    //         $html_data = view($module['main_view'].'.form_include', compact(['form_data','id', 'mode', 'financial_years']))->render();
    //         $response = response()->json(['html'=>$html_data, 'title_shown'=>$title_shown, 'action'=>$action, 'method'=>$method, 'mode'=>$mode, 'id'=>$id]);
    //         return $response;
    //     } else{
    //         return view($module['main_view'].'.cred2')->with(compact('form_data', 'financial_years', 'model', 'module', 'action', 'method', 'mode', 'folder', 'title_shown', 'id'));
    //     }
    // }

    // public function update(Request $request, $id, DefaultModel $model){
    //     $module = $this->module;
    //     dd($request);
    //     $request->validate([
    //         'series_id' => 'required|numeric',
    //         'entry_year' => 'required',
    //         'entry_date' => 'required|date_format:Y-m-d H:i:s',
    //         // 'form_data.member_mob' => 'required',
    //         'member_id' => 'required|numeric'
    //     ]);

    //     $modelfind = $model->find($id);
    //     $modelfind->update($request->all());
    
    //     return redirect()->route($module['main_route'].'.index')->with('success', $module['main_heading'].' updated successfully');
    // } 

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

    public function ajax_member(Request $request) {
        // dd($request->input());
        $input=$request->input('term');
        $org_id = $request->input('org_id');
        $arr=[];
        $models =new \App\Models\Members();
        $name = $models->select('id', 'name', 'unit_number', 'mobile_number')->where([['delstatus', '<', '1'],['status', '>', '0'], ['organization_id', '=', $org_id]])->where(DB::raw("CONCAT_WS(' ', name, unit_number, mobile_number)"), 'like', '%'.$input.'%')
            ->get()->toArray();
        $count=0;
        foreach($name as $nm){
            $arr[$count]['id']= $nm['id'];
            $arr[$count]['label']="Name : ".$nm['name']."; Unit No : ".$nm['unit_number']."; Mob No : ".$nm['mobile_number'];
            $arr[$count]['value'] = $nm['mobile_number'];
            // $arr[$count]['name'] = $nm['name'];
            // $arr[$count]['desc']= $nm['unit_number'].$nm['name'].$nm['mobile_number'];
            $count++;
        }
        return $arr;
    }

    public function series_select(Request $request) {
        // dd($request->input());
        $module = $this->module;
        $input=$request->input('org_id');
        $models =new \App\Models\Series();
        $data = $models->select('id', 'name')->where('organization_id',$input)->get()->toArray();
        $row_data =[];
        foreach($data as $ds){
            $row_data[$ds['id']] = $ds['name'];
        }
        $html_data = view($module['main_view'].'.series_select', compact(['row_data']))->render();

        $response = response()->json(['html'=>$html_data]);
        return $response;
    }

    public function series_data(Request $request) {
        // dd($request->input());
        $module = $this->module;
        $input=$request->input('ser_id');
        $models = \App\Models\Series::find($input);
        $series_num =$models->name.$models->number_separator.str_pad($models->next_number,$models->min_length,'0', STR_PAD_LEFT);
        $next_num = $models->next_number;
        $response = response()->json(['serial_no'=>$series_num, 'next_num' =>$next_num]);
        return $response;
    }

}
