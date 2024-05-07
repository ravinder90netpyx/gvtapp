<?php
namespace App\Http\Controllers\Admin;

use App\Models\Members as DefaultModel;
use App\Models\Journal_Entry as JournalEntryModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use URL;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\helpers;

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

    public function index(Request $request, DefaultModel $model, helpers $helpers){
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

        $end_month = Carbon::now()->format('Y-m');
        $start_month = Carbon::now()->subMonth()->format('Y-m');
        $format = "Y-m";

        // $month_arr = $helpers->get_financial_month_year($start_month, $end_month, $format);

        $title_shown = 'Manage '.$module['main_heading'].'s';
        $folder = $this->folder;

        return view($module['main_view'].'.index', compact('data', 'model', 'carbon', 'module', 'perpage', 'folder', 'title_shown', 'query'))->with('i', ($request->input('page', 1) - 1) * $perpage);
    }

    public function getReportByDate(Request $request, helpers $helpers,JournalEntryModel $journalEntryModel){
        
        $from_date = $request->formData['from_date'];
        $to_date = $request->formData['to_date'];
        $memberIds = $request->formData['memberIds'];

        $format = "M Y";
        $month_arr = $helpers->get_financial_month_year($from_date, $to_date, $format);
        /*$format = "Y-m";
        $month_arr2 = $helpers->get_financial_month_year($start_date, $end_date, $format);*/

        dd($month_arr);

    }

   
}
