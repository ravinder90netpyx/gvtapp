<?php
namespace App\Http\Controllers\Admin;

// use App\Models\Samplem as DefaultModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use URL;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use \App\Helpers\helpers;

class CommonController extends Controller{
    // common functions in between the module will come here

    public function __construct(Request $request){
        $folder = $this->folder;
        $mode_construct = '';
    }

    public function series_select(Request $request, helpers $helpers) {
        // $module = $this->module;
        $input=$request->input('org_id');
        $type = $request->input('type');
        $models =new \App\Models\Series();
        $data = $models->select('id', 'name', 'tag', 'financial_year')->where([['organization_id','=',$input], ['status','>','0'], ['delstatus','<','1'], ['type','=',$type]])->orderBy('id','DESC')->get()->toArray();
        $now = Carbon::now();
        $current_year = $helpers->get_financial_years($now);
        // dd($current_year);
        $tag_checker = $models->where([['organization_id','=',$input],['status','>','0'],['delstatus','<','1'],['financial_year','=',$current_year], ['tag','=','1'], ['type','=',$type]])->get()->toArray();
        $row_data =[];
        $tag =0;
        if(!empty($tag_checker)){
            foreach($tag_checker as $tc){
                $row_data[$tc['id']] = $tc['name'];
            }
        } else{
            foreach($data as $ds){
                $row_data[$ds['id']] = $ds['name'];
            }
        }
        // dump($row_data);
        $html_data = view('include.common_select', compact(['row_data']))->render();

        $response = response()->json(['html'=>$html_data]);
        return $response;
    }

    public function series_data(Request $request) {
        // $module = $this->module;
        $input=$request->input('ser_id');
        $models = \App\Models\Series::find($input);
        $series_num =$models->name.$models->number_separator.str_pad($models->next_number,$models->min_length,'0', STR_PAD_LEFT);
        $next_num = $models->next_number;
        $response = response()->json(['serial_no'=>$series_num, 'next_num' =>$next_num]);
        return $response;
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

    public function ajax_memberwith_non_member(Request $request) {
        $input=$request->input('q');
        // dd($request->input());
        
        $org_id = $request->input('org_id');
        $arr=[];
        $models =new \App\Models\Members();
        $name = $models->select('id', 'name', 'unit_number', 'mobile_number')->where([['delstatus', '<', '1'],['status', '>', '0'], ['organization_id', '=', $org_id]])->where(DB::raw("CONCAT_WS(' ', name, unit_number, mobile_number, alternate_name_1, alternate_name_2, alternate_number, sublet_name)"), 'like', '%'.$input.'%')->get()->toArray();
        $count=1;
        $arr[0]['id'] = 'N/A';
        $arr[0]['text'] = "Non-Member";

        foreach($name as $nm){
            $arr[$count]['id']= $nm['id'];
            $arr[$count]['text']="Name : ".$nm['name']."; Unit No : ".$nm['unit_number']."; Mob No : ".$nm['mobile_number'];
            // $arr[$count]['name'] = $nm['name'];
            // $arr[$count]['desc']= $nm['unit_number'].$nm['name'].$nm['mobile_number'];
            $count++;
        }
        return $arr;
    }
}
