<?php
namespace App\Http\Controllers\Admin;

#use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RequestQuote;
use App\Helpers\helpers;
use Carbon\Carbon;
class DashboardController extends Controller{
	public function __construct(){
        #$this->middleware('auth:web');
    }

    public function index(helpers $helpers){
        $carbon = new Carbon();
        $now = date('Y-m');
        $month_money = \App\Models\Report::where([['month','=', $now],['status','>','0'],['delstatus','<','1']])->sum('money_paid');
        $folder = $this->folder;
        $member = \App\Models\Members::where([['status','>','0'],['delstatus','<','1']])->count();
        $unpaid = 0;
        $mon = date('M');
        $today_date = Carbon::now();
        $sixMonthsAgo = $today_date->subMonths(6)->format('Y-m');
        $month_arr = $helpers->get_financial_month_year($sixMonthsAgo,$now,'M');
        $month_str = '["' . implode('", "', $month_arr) . '"]';
        $yr = $helpers->get_financial_years(date('Y-m-d'), date('Y-m-d'));
        $req = new Request(['year'=>$yr[0]]);
        $yr_collection = $this->ajax_year($req);
        $paid_val = $this->paid_member($sixMonthsAgo,$now);
        $month_val = $this->find_month_val($sixMonthsAgo,$now);
        $fine_val =0;
        $je_rep = \App\Models\Journal_Entry::where([['status','>','0'],['delstatus','<','1'],['charge_type_id','=','8'],['reciept_date', 'LIKE', '%'.$now.'%']])->get();
        foreach($je_rep as $fv){
            $fine = \App\Models\Entrywise_Fine::where([['status','>','0'],['delstatus','<','1'],['journal_entry_id', '=',$fv->id]])->first();
            $fine_val = $fine_val+ $fine->fine_paid;
        }
        $report = \App\Models\Report::where([['month','=',$now],['status','>','0'],['delstatus','<', '1']])->orderBy('id','DESC')->count();
        $unpaid = $member - $report;
        $financial_years = $helpers->get_financial_years(null, null);
    	return view($folder['folder_name'].'.dashboard', compact('folder','financial_years','month_money','unpaid','yr_collection','month_str', 'month_val', 'paid_val', 'fine_val'));
    }

    public function ajax_year(Request $request){
        $carbon = new Carbon();
        $yr = $request->year;
        $helpers = new helpers();
        $yr_arr = explode('-',$yr);
        $strt_yr_month = Carbon::create($yr_arr[0], 3, 1);
        $end_yr_month = Carbon::create($yr_arr[1], 3, 1);
        $strt_yr_month = $strt_yr_month->format('Y-m');
        $end_yr_month = $end_yr_month->format('Y-m');
        $mon_arr = $helpers->get_financial_month_year($strt_yr_month,$end_yr_month);
        $total_collection = \App\Models\Report::where([['month','>', $strt_yr_month],['month','<=', $end_yr_month],['status','>','0'],['delstatus','<','1']])->sum('money_paid');
        
        return $total_collection;
    }

    public function find_fine_val(){
        
    }

    public function find_month_val($start_month, $end_month){
        $helpers = new helpers();
        $month_arr = $helpers->get_financial_month_year($start_month, $end_month);
        $month_val = [];
        $fine_val =0;
        foreach($month_arr as $mt){
            $month_val[$mt] = \App\Models\Report::where([['month','=',$mt],['status','>','0'], ['delstatus','<','1']])->sum('money_paid');
            $je_rep = \App\Models\Journal_Entry::where([['status','>','0'],['delstatus','<','1'],['charge_type_id','=','8'],['reciept_date', 'LIKE', '%'.$mt.'%']])->get();
            foreach($je_rep as $jr){
                if(!empty($jr)){
                    $fine_total = \App\Models\Entrywise_Fine::where([['status','>','0'],['delstatus','<','1'],['journal_entry_id','=',$jr->id]])->first();
                    $fine_val = isset($fine_total->fine_paid)? $fine_val+$fine_total->fine_paid : 0;
                }
            }
            $month_val[$mt]= $month_val[$mt]+$fine_val;
        }
        return $month_val;
    }

    public function paid_member($start_month, $end_month){
        $helpers = new helpers();
        $month_arr = $helpers->get_financial_month_year($start_month, $end_month);
        $paid_val = [];
        $member = \App\Models\Members::where([['status','>','0'],['delstatus','<','1']])->get();
        foreach($month_arr as $mt){
            $unpaid = 0;
            $paid_val[$mt] = \App\Models\Report::where([['month', '=', $mt],['status','>','0'],['delstatus','<','1']])->count();
        }
        return $paid_val;
    }
}
