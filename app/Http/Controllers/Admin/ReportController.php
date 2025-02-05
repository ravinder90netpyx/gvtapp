<?php
namespace App\Http\Controllers\Admin;

use App\Models\Members as DefaultModel;
use App\Models\Journal_Entry as JournalEntryModel;
use App\Models\Report as ReportModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use URL;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\helpers;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller{
    public $module = array(
        'module_view' => 'report',
        'module_route' => 'report',
        'permission_group' => 'journal_entry',
        'main_heading' => 'Report',
        'default_perpage' => 15
    );

    public function __construct(Request $request){
        $module = $this->module;
        $folder = $this->folder;

        $module['main_view'] = $folder['folder_name'].'.'.$module['module_view'];
        $module['main_route'] = $folder['route_folder_name'].'.'.$module['module_route'];

        $this->module = $module;

        // $this->middleware('permission:'.$module['permission_group'].'.manage', ['only' => ['index']]); // .report after migration

        $this->middleware('permission:'.$module['permission_group'].'.report', ['only' => ['index', 'getReportByDate', 'getPendingReport', 'getPersonalReport']]);
    }

    public function index(Request $request, DefaultModel $model, helpers $helpers,JournalEntryModel $journalEntryModel){

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
        if($query!='') $model_get = $model_get->where('name', 'LIKE', '%'.$query.'%');

        // $model_get = $model_get->whereExists(function ($query) {
            // $query->select(DB::raw(1))->from('journal_entry')->whereRaw('journal_entry.member_id = members.id');
        // })->get();

        // dump($model_get->get()->toArray());
        // $data = $model_get->paginate($perpage)->onEachSide(2);
        $data = $model_get->has('report')->paginate($perpage);
        // dump($data->toArray());  

        $format = "Y-m";
        $month_arr = [];
        $end_month = Carbon::now()->format('Y-m');
        $start_month = Carbon::now()->subMonth()->format('Y-m');

        $month_arr = $helpers->get_financial_month_year($start_month, $end_month, $format);
        $form_data = $journalEntryModel->where('from_month',$start_month)->get()->toArray();

        // $month_arr = $helpers->get_financial_month_year($start_month, $end_month, $format);

        $title_shown = 'Month-Wise Txn '.$module['main_heading'].'s';
        $folder = $this->folder;

        return view($module['main_view'].'.index', compact('data', 'model','month_arr' ,'carbon', 'module', 'perpage', 'folder', 'title_shown', 'query'))->with('i', ($request->input('page', 1) - 1) * $perpage);
    }

    public function getReportByDate(Request $request, DefaultModel $memberModel ,helpers $helpers,ReportModel $reportModel){
        $carbon = new Carbon();
        $module = $this->module;
        $title_shown = 'Manage '.$module['main_heading'].'s';
        $from_date = $request->formData['from_date'];
        $to_date = $request->formData['to_date'];
        $memberIds = $request->formData['memberIds'];
        $report_type = $request->formData['report_type'];
        
        $member_modal = new \App\Models\Members(); 
        $format = "Y-m";
        $month_arr = $helpers->get_financial_month_year($from_date, $to_date, $format);
        $report = $reportModel->whereIn('member_id',$memberIds)->get();
        $members = $memberModel->whereIn('id',$memberIds)->get();
        
       if($request->ajax()) {
            if($report_type == 'report_pending'){
                $html_data = view($module['main_view'].'.ajax_pending_reports', compact(['report','members','module','month_arr','carbon','helpers']))->render();
                $response = response()->json(['html'=>$html_data, 'title_shown'=>$title_shown, ]);
            } else{
                $html_data = view($module['main_view'].'.ajax_reports', compact(['report','members','module','month_arr','carbon','helpers']))->render();
                $response = response()->json(['html'=>$html_data, 'title_shown'=>$title_shown, ]);
            }
            return $response;
        } else{
            return view($module['main_view'].'.cred2', compact('form_data', 'financial_years', 'model', 'module', 'folder', 'title_shown', 'mode', 'id'));
        }
    }

    public function getPendingReport(Request $request, DefaultModel $model ,helpers $helpers,JournalEntryModel $journalEntryModel){
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
        if($query!='') $model_get = $model_get->where('name', 'LIKE', '%'.$query.'%');

        // $data = $model_get->paginate($perpage)->onEachSide(2);
        $data = $model_get->has('report')->paginate($perpage)->onEachSide(2);

        $format = "Y-m";
        $month_arr = [];
        $end_month = Carbon::now()->format('Y-m');
        $start_month = Carbon::now()->subMonth()->format('Y-m');

        $month_arr = $helpers->get_financial_month_year($start_month, $end_month, $format);
        $form_data = $journalEntryModel->where('from_month',$start_month)->get()->toArray();

        // $month_arr = $helpers->get_financial_month_year($start_month, $end_month, $format);

        $title_shown = 'Consolidated Txn '.$module['main_heading'].'s';
        $folder = $this->folder;

        return view($module['main_view'].'.index_pending', compact('data', 'model','month_arr' ,'carbon', 'module', 'perpage', 'folder', 'title_shown', 'query'))->with('i', ($request->input('page', 1) - 1) * $perpage);
    }

    public function getTransactionDetails(Request $request, DefaultModel $model, JournalEntryModel $journalEntryModel, ReportModel $reportModel){
        $module =$this->module;
        $carbon = new Carbon();
        $perpage = $request->perpage ?? $module['default_perpage'];
        if(!$request->perpage && !empty($request->cookie('perpage'))) $perpage = $request->cookie('perpage');
        $model_get = $model;
        $auth_user = Auth::user();
        $roles = $auth_user->roles()->pluck('id')->toArray();
        $end_month = Carbon::now()->format('Y-m-d H-i-s');
        $start_month = Carbon::now()->subMonth()->format('Y-m-d H-i-s');
        $je_model = $journalEntryModel->where([['delstatus', '<', '1'], ['status', '>', '0'],['entry_date','>', $start_month], ['entry_date','<', $end_month]]);
        if(!in_array(1, $roles)){
            $organization_id = $auth_user->organization_id;
            $je_model = $je_model->where('organization_id', $organization_id)->get();
        }
        if($model->getDelStatusColumn()) $model_get = $model_get->where($model->getDelStatusColumn(), '<', '1');
        if ($model->getSortOrderColumn()) $model_get = $model_get->orderBy($model->getSortOrderColumn(), 'ASC');
        else $model_get = $model_get->latest();
        $query = $request->get('query') ?? '';
        if($query!='') $model_get = $model_get->where('name', 'LIKE', '%'.$query.'%');
        $data = $model_get->paginate($perpage)->onEachSide(2);

        $form_data = $journalEntryModel->where('from_month',$start_month)->get()->toArray();
        $title_shown = 'Manage Transaction '.$module['main_heading'].'s';
        $folder = $this->folder;
        return view($module['main_view'].'.index_transaction', compact('data', 'model','carbon', 'module', 'perpage', 'folder', 'je_model', 'title_shown', 'query'))->with('i', ($request->input('page', 1) - 1) * $perpage);
    }

    public function ajaxTransactionDetails(Request $request, JournalEntryModel $journalEntryModel, ReportModel $model){
        $module = $this->module;
        $carbon = new Carbon();
        $from_date = $carbon->createFromFormat('Y-m',$request->formData['from_date']);
        $from_date = $from_date->startOfMonth()->format('Y-m-d H:i:s');
        $to_date = $carbon->createFromFormat('Y-m',$request->formData['to_date']);
        $to_date = $to_date->endOfMonth();
        $memberIds = $request->formData['memberIds'];
        $je_model = $journalEntryModel->whereIn('member_id', $memberIds)->where('delstatus','<','1')->where('status','>','0')->whereBetween('entry_date',[$from_date,$to_date->format('Y-m-d H:i:s')])->get();
        $title_shown = 'Transaction Details'.$module['main_heading'];
        $html_data =view($module['main_view'].'.ajax_transaction_report', compact('memberIds', 'model', 'je_model', 'module', 'carbon'))->render();
        $response = response()->json(['html'=>$html_data, 'title_shown'=>$title_shown, ]);
        return $response;
    }

    public function getPersonalReport(Request $request, helpers $helpers,JournalEntryModel $journalEntryModel,ReportModel $model){
        $carbon = new Carbon();
        $module = $this->module;
        $perpage = $request->perpage ?? $module['default_perpage'];
        if(!$request->perpage && !empty($request->cookie('perpage'))) $perpage = $request->cookie('perpage');
        $model_get = $model;
        $auth_user = Auth::user();
        $roles = $auth_user->roles()->pluck('id')->toArray();
        if(!in_array(1, $roles)){
            $organization_id = $auth_user->organization_id;
            // $model_get = $model_get->where('organization_id', $organization_id);
        }
        if($model->getDelStatusColumn()) $model_get = $model_get->where($model->getDelStatusColumn(), '<', '1');
        
        if($model->getSortOrderColumn()) $model_get = $model_get->orderBy($model->getSortOrderColumn(), 'ASC');
        else $model_get = $model_get->latest();
        
        $query = $request->get('query') ?? '';
        if($query!='') $model_get = $model_get->where('name', 'LIKE', '%'.$query.'%');
        // $data = $model_get->paginate($perpage)->onEachSide(2);
        $data = $model_get->paginate($perpage)->onEachSide(2);

        $format = "Y-m";
        $month_arr = [];
        $end_month = Carbon::now()->format('Y-m');
        $start_month = Carbon::now()->subMonth()->format('Y-m');

        $month_arr = $helpers->get_financial_month_year($start_month, $end_month, $format);
        $form_data = $journalEntryModel->where('from_month',$start_month)->get()->toArray();

        // $month_arr = $helpers->get_financial_month_year($start_month, $end_month, $format);

        $title_shown = 'Date-Wise Txn '.$module['main_heading'].'s';
        $folder = $this->folder;

        return view($module['main_view'].'.index_personal', compact('data', 'model','month_arr' ,'carbon', 'module', 'perpage', 'folder', 'title_shown', 'query'))->with('i', ($request->input('page', 1) - 1) * $perpage);
    }

    public function ajaxPersonal(Request $request, DefaultModel $model, helpers $helpers,JournalEntryModel $journalEntryModel, ReportModel $reportModel){
        $module = $this->module;
        $carbon = new Carbon();
        $from_date = $request->formData['from_date'];
        $to_date = $request->formData['to_date'];
        $memberIds = $request->formData['memberIds'];
        $members = $model->whereIn('id',$memberIds)->get();
        $month_arr = [];
        $format = 'Y-m';
        $month_arr = $helpers->get_financial_month_year($from_date, $to_date, $format);
        $form_data = $journalEntryModel->where('from_month',$from_date)->get()->toArray();
        $title_shown = 'Manage Pending '.$module['main_heading'].'s';
        // $report_mod = $helpers->get_financial_month_year($from_date,$to_date,$format);
        $html_data = view($module['main_view'].'.ajax_personal_reports', compact(['members','memberIds','module','month_arr','carbon','helpers']))->render();
        $response = response()->json(['html'=>$html_data, 'title_shown'=>$title_shown, ]);
        return $response;
    }

    public function searchData(Request $request){
        $term = $request->search;
        $member = \App\Models\Members::where([['status','>','0'],['delstatus','<','1']]);
        $member = $member->where([['name', 'LIKE','%'.$term.'%']])->orwhere([['unit_number','LIKE','%'.$term.'%']])->get();
        $mem_data=[];
        $temp_data = [];
        foreach($member as $mem){
            $temp_data['label'] = $mem->name;
            $temp_data['value'] = $mem->id;
            $mem_data[] = $temp_data;
            $temp_data =[];
        }
        return $mem_data;
    }

    public function getFineReport(Request $request, DefaultModel $model, JournalEntryModel $journalEntryModel){
        $module = $this->module;
        $folder = $this->folder;
        $perpage = $request->perpage ?? $module['default_perpage'];
        if(!$request->perpage && !empty($request->cookie('perpage'))) $perpage = $request->cookie('perpage');
        $carbon = new Carbon();
        $model_get = $journalEntryModel->where('charge_type_id','8');
        $auth_user = Auth::user();
        $roles = $auth_user->roles()->pluck('id')->toArray();
        if(!in_array(1, $roles)){
            $organization_id = $auth_user->organization_id;
            $model_get = $model_get->where('organization_id',$organization_id);
        }
        if($model->getDelStatusColumn()) $model_get = $model_get->where($model->getDelStatusColumn(), '<', '1');
        if($model->getSortOrderColumn()) $model_get= $model_get->orderBy($model->getSortOrderColumn(), 'ASC');
        $model_get= $model_get->latest();

        $query = $request->get('query') ?? '';
        if($query!='') $model_get = $model_get->where('name', 'LIKE', '%'.$query.'%');
        // $data = $model_get->paginate($perpage)->onEachSide(2);
        $start_month = $carbon->now()->subMonths()->format('Y-m-d H-i-s');
        $end_month = $carbon->now()->format('Y-m-d H:i:s');
        $model_get = $model_get->whereBetween('entry_date', [$start_month, $end_month]);
        $data = $model_get->paginate($perpage)->onEachSide(2);
        $title_shown = 'Manage Fine '.$module['main_heading'].'s';
        return view($module['main_view'].'.index_fine', compact(['module', 'folder', 'title_shown', 'data', 'model', 'perpage', 'carbon', 'query']))->with('i', ($request->input('page', 1) - 1) * $perpage);
        
    }

    public function ajaxFine(Request $request, DefaultModel $model, helpers $helpers, JournalEntryModel $journalEntryModel){
        $module = $this->module;
        $folder = $this->folder;
        $carbon = new Carbon();
        $from_date = $carbon->createFromFormat('Y-m',$request->formData['from_date']);
        $from_date = $from_date->startOfMonth()->format('Y-m-d H:i:s');
        $to_date = $carbon->createFromFormat('Y-m',$request->formData['to_date']);
        $to_date = $to_date->endOfMonth();
        $memberIds = $request->formData['memberIds'];
        $model_get = $journalEntryModel->where('charge_type_id','=','8')->whereIn('member_id',$memberIds);
        $model_get = $model_get->where([['status','>','0'],['delstatus','<','1']])->whereBetween('entry_date',[$from_date, $to_date->format('Y-m-d H:i:s')])->get();
        $html_data= view($module['main_view'].'.ajax_fine_reports', compact(['module','folder','carbon','memberIds','model_get']))->render();
        $title_shown = 'Manage Fine '.$module['main_heading'];
        $response = response()->json(['html'=>$html_data, 'title_shown'=>$title_shown]);
        return $response;
    }

    public function getExpenseReport(Request $request, DefaultModel $model, helpers $helpers, JournalEntryModel $journalEntryModel){
        $module = $this->module;
        $folder = $this->folder;
        $perpage = $request->perpage ?? $module['default_perpage'];
        $carbon = new Carbon();
        $auth_user = Auth::user();
        $query = $request->get('query') ?? '';
        $start_month = $carbon->now()->subMonths()->startOfMonth()->format('Y-m-d H-i-s');
        $end_month = $carbon->now()->format('Y-m-d H-i-s');
        $user_array = $this->userArray($auth_user->id);
        $exp_model = \App\Models\Expense::where([['status','>','0'],['delstatus','<','1']])->whereBetween('date',[$start_month,$end_month]);
        $data = $exp_model->whereIn('user_id', $user_array)->get();
        $roles = $auth_user->roles()->pluck('id')->toArray();
        $title_shown = 'Manage Expense '.$module['main_heading'];
        return view($module['main_view'].'.index_expense', compact(['module', 'folder', 'title_shown', 'data', 'model', 'perpage', 'carbon', 'query']))->with('i', ($request->input('page', 1) - 1) * $perpage);
    }

    public function ajaxExpense(Request $request, DefaultModel $model, helpers $helpers, JournalEntryModel $journalEntryModel){
        $module = $this->module;
        $folder = $this->folder;
        $carbon = new Carbon();
        $perpage = $request->perpage ?? $module['default_perpage'];
        $start_month = $carbon->createFromFormat('Y-m', $request->formData['from_date']);
        $start_month = $start_month->startOfMonth()->format('Y-m-d H-i-s');

        $end_month = $carbon->createFromFormat('Y-m', $request->formData['to_date']);
        $model_get = \App\Models\Expense::where([['status','>','0'],['delstatus','<','1']])->whereBetween('date',[$start_month, $end_month->format('Y-m-d H-i-s')])->get();
        $html_data= view($module['main_view'].'.ajax_expense_reports', compact(['module','folder','carbon','model_get']))->render();
        $title_shown = 'Manage Fine '.$module['main_heading'];
        $response = response()->json(['html'=>$html_data, 'title_shown'=>$title_shown]);
        return $response;
    }

    public function userArray($user_id){
        $user_array = [];
        $user_array[] = $user_id;
        $user = \App\Models\User::where([['status','>','0'],['created_by','=', $user_id]])->get()->toArray();
        foreach($user as $us){
            if(!in_array($us['id'], $user_array)){
                $user_array[] = $us['id'];
                $user_array = array_unique(array_merge($user_array, $this->userArray($us['id'])));
            }
        }
        return $user_array;
    }
}
