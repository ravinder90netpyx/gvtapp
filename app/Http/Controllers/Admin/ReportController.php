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
        $carbon = new Carbon();
        $module = $this->module;
        $title_shown = 'Manage '.$module['main_heading'].'s';
        $from_date = $request->formData['from_date'];
        $to_date = $request->formData['to_date'];
        $memberIds = $request->formData['memberIds'];
        $member_modal = new \App\Models\Members(); 
        $format = "Y-m";
        $month_arr = $helpers->get_financial_month_year($from_date, $to_date, $format);
        /*$format = "M Y";
        $month_arr2 = $helpers->get_financial_month_year($start_date, $end_date, $format);*/
        //var_dump($month_arr);
        
        $journalEntries = [];
        //$journalEntries = $journalEntryModel->whereIn('member_id', [3,4])->whereIn('from_month',$month_arr)->orWhereIn('to_month',$month_arr)->get();
        $member_data= 
        $data =[];
        $data = $journalEntryModel->select('from_month','to_month')->whereIn('member_id',$memberIds)->get();


        
        //$data2 = $models->select('id', 'name')->where('organization_id',$input)->get()->toArray();

        //$users = User::whereIn(‘name’,[‘John’,’Peter’])->orWhereIn(‘id’,[1,5])->get();
        
        //dd($data);
        
        $all_dates = [];
        foreach($data as $jr){
            array_push($all_dates,$jr['from_month']);
            array_push($all_dates,$jr['to_month']);
        }

        //dd($all_dates);

        $inter = [];
        $inter = array_intersect($month_arr, $all_dates);
        //var_dump($inter);

        $new_arr = [];
        //$data = $journalEntryModel->select('from_month','to_month')->whereIn('member_id',[3,4])->get();
        $form_data = $journalEntryModel->whereIn('member_id', $memberIds)->whereIn('from_month',$inter)->orWhereIn('to_month',$inter)->get()->toArray();

        //echo count($form_data);
 
        foreach($form_data as $value){
           
            $new_arr[$value['member_id']] = [
                'from_month' => $value['from_month'],
                'to_month' => $value['to_month'],
            ];            
        }
        dd($new_arr);
        

        //var_dump($new_arr);
        

       /*foreach($form_data as $jr2){
            var_dump($jr2['member_id']);
       }*/

       if($request->ajax()) {
            $html_data = view($module['main_view'].'.ajax_reports', compact(['form_data', 'module',
                'month_arr','carbon','helpers']))->render();
            $response = response()->json(['html'=>$html_data, 'title_shown'=>$title_shown, ]);
            return $response;
        } else{
            return view($module['main_view'].'.cred2', compact('form_data', 'financial_years', 'model', 'module', 'folder', 'title_shown', 'mode', 'id'));
        }


    }

   
}
