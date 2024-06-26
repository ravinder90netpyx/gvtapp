<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Console\Scheduling\Schedule;
#use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
#use Illuminate\Support\Facades\DB;
use App\Jobs\WhatsappAPI;
use App\Models\API_Connections;
use App\Models\Settings as settingsModel;
use Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use Artisan;

class CronController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(Request $request){
    }

    public function index(Schedule $schedule){
        /*$schedule->call(function(){
        
        })->cron('* * * * *');*/

        //Storage::append('custom.log', "testing");
        //echo "yes";
    }

    public function redisTest(){
        $redis = Redis::connection();
        try{
            var_dump($redis->ping());
        } catch (Exception $e){
            $e->getMessage();
        }
    }

    public function optimize(){

        // $model12 = new \App\Models\Test_Cron();
            $now = Carbon::now();

            // $data1['name'] = "testing cronesss";
            // $data1['date'] = $now;
            // $model1 = $model12->create($data1);
            // echo "hello"; exit;
            $member_model = \App\Models\Members::where([['status','>','0'], ['delstatus','<','1'], ['group_id','=', '2']])->get();
            //echo '<pre>';print_R($member_model);exit;


            $type = 'document';
            // $date_arr = explode(' ', $model->entry_date);
            // $date = Carbon::parse($date_arr[0])->format('d-M-Y');
            // $month = Carbon::parse($model->from_month)->format('M Y')."-".Carbon::parse($model->to_month)->format('M Y');
            // $params = array(
            //     $model->charge,
            //     $month,
            //     $date
            // );
            $message = '';
            $message = json_encode($message, true);
            $helpers = new \App\Helpers\helpers();
            $now = Carbon::now();
            $day = $now->day;
            $now_date = $now->day(12);
            $date = Carbon::parse($now_date)->format('d-M-Y');
            foreach ($member_model as $val){
                $send_notification = 1;
                $org_model = new \App\Models\Organization_Settings();
                $org_id = $val->organization_id;
                $charge = \App\Models\Charges::find($val->charges_id);
                $data = [
                    'name'=> $val->name,
                    'mobile_number' => $val->mobile_number,
                    'unit_no'=> $val->unit_number,
                    'charge' => $charge->rate,
                    'date' => $date
                ];
                $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','reminder'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();
                if($day>12){
                    $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','overdue'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();
                } elseif ($day == 12) {
                    $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','reminder'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();
                }

                $templ_json = $helpers->make_temp_json($temp->id, $data);

                $destination = $val->mobile_number;
                $now = Carbon::now();
                $month = $now->format('Y-m');
                $rp_month =\App\Models\Report::where([['member_id','=',$val->id], ['month','=',$month], ['status', '>', '0'],['delstatus','<','1']])->first();
                if(!empty($rp_month)){
                    if(empty($rp_month->money_pending)){
                        $send_notification = 0;
                    }
                }

                // if(!empty($send_notification)){
                //$destination= '+917479735912';
                    $mobile_msg_arr =!empty($val->mobile_message)? json_decode($val->mobile_message): [];
                    $sublet_msg_arr =!empty($val->sublet_message)? json_decode($val->sublet_message): [];
                    if(!empty($val->sublet_message) && $val->sublet_message!='null'){
                       $sublet_msg_arr =$val->sublet_message; 
                    }else{
                       $sublet_msg_arr =[]; 
                    }

                    if(!empty($val->mobile_message) && $val->mobile_message!='null'){
                       $mobile_msg_arr =$val->mobile_message;
                    }else{
                       $mobile_msg_arr =[]; 
                    }

                    //echo '<pre>';print_R($sublet_msg_arr);exit;
                    if(in_array('reminder',$mobile_msg_arr)){
                        dispatch( new WhatsappAPI($destination,$message, $org_id,$templ_json) )->onConnection('sync');
                    }

                    if(in_array('reminder',$sublet_msg_arr)){
                        // $destination = $val->sublet_number;
                        if(!empty($destination)){
                            dispatch( new WhatsappAPI($destination,$message, $org_id,$templ_json) )->onConnection('sync');
                        }
                    }
                    // dispatch( new WhatsappAPI($destination,$message, $org_id,$templ_json) )->onConnection('sync');
                // }
            }
            echo "hjsdk"; exit;
        Artisan::call('optimize:clear');
        Artisan::call('optimize');

        return "Optimized";
    }
}
