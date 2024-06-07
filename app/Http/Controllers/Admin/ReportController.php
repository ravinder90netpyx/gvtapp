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
        'default_perpage' => 10
    );

    public function __construct(Request $request){
        $module = $this->module;
        $folder = $this->folder;

        $module['main_view'] = $folder['folder_name'].'.'.$module['module_view'];
        $module['main_route'] = $folder['route_folder_name'].'.'.$module['module_route'];

        $this->module = $module;

        $this->middleware('permission:'.$module['permission_group'].'.manage', ['only' => ['index']]); // .report after migration
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

        $data = $model_get->paginate($perpage)->onEachSide(2);

        $format = "Y-m";
        $month_arr = [];
        $end_month = Carbon::now()->format('Y-m');
        $start_month = Carbon::now()->subMonth()->format('Y-m');

        $month_arr = $helpers->get_financial_month_year($start_month, $end_month, $format);
        $form_data = $journalEntryModel->where('from_month',$start_month)->get()->toArray();

        // echo "<pre>"; print_r($data->toArray()); exit;

        // $month_arr = $helpers->get_financial_month_year($start_month, $end_month, $format);

        $title_shown = 'Manage '.$module['main_heading'].'s';
        $folder = $this->folder;

        return view($module['main_view'].'.index', compact('data', 'model','month_arr' ,'carbon', 'module', 'perpage', 'folder', 'title_shown', 'query'))->with('i', ($request->input('page', 1) - 1) * $perpage);
    }

    public function getReportByDate(Request $request, DefaultModel $memberModel ,helpers $helpers,JournalEntryModel $journalEntryModel,ReportModel $reportModel){
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
            $html_data = view($module['main_view'].'.ajax_pending_reports', compact(['report','members','module',
                'month_arr','carbon','helpers']))->render();
            $response = response()->json(['html'=>$html_data, 'title_shown'=>$title_shown, ]);
        }else{
            $html_data = view($module['main_view'].'.ajax_reports', compact(['report','members','module',
                'month_arr','carbon','helpers']))->render();
            $response = response()->json(['html'=>$html_data, 'title_shown'=>$title_shown, ]);
            }
            return $response;
        } else{
            return view($module['main_view'].'.cred2', compact('form_data', 'financial_years', 'model', 'module', 'folder', 'title_shown', 'mode', 'id'));
        }
    }

    public function getPendingReport(Request $request, DefaultModel $model ,helpers $helpers,JournalEntryModel $journalEntryModel,ReportModel $reportModel){
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

        $data = $model_get->paginate($perpage)->onEachSide(2);

        $format = "Y-m";
        $month_arr = [];
        $end_month = Carbon::now()->format('Y-m');
        $start_month = Carbon::now()->subMonth()->format('Y-m');

        $month_arr = $helpers->get_financial_month_year($start_month, $end_month, $format);
        $form_data = $journalEntryModel->where('from_month',$start_month)->get()->toArray();

        

        // $month_arr = $helpers->get_financial_month_year($start_month, $end_month, $format);

        $title_shown = 'Manage Pending '.$module['main_heading'].'s';
        $folder = $this->folder;

        return view($module['main_view'].'.index_pending', compact('data', 'model','month_arr' ,'carbon', 'module', 'perpage', 'folder', 'title_shown', 'query'))->with('i', ($request->input('page', 1) - 1) * $perpage);
    }

   
}
