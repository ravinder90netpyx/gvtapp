<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Storage;
use App\Jobs\WhatsappAPI;
use Carbon\Carbon;
use App\Models\API_Connections;
use App\Models\Settings as settingsModel;
use App\Models\Test_Cron as cronModel;

class Kernel extends ConsoleKernel{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule){
        // $schedule->command('inspire')->hourly();
        $schedule->call(function (){

            $now = Carbon::now();
            $member_model = \App\Models\Members::where([['status','>','0'], ['delstatus','<','1'], ['group_id','=', '2']])->get();

            $type = 'document';
            $message = '';
            $message = json_encode($message, true);
            $helpers = new \App\Helpers\helpers();
            $now = Carbon::now();
            $day = $now->day;
            $month = $now->format('M Y');
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
                    'date' => $date,
                    'month' => $month
                ];
                $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','reminder'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();
                if($day>12){
                    $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','overdue'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();
                } elseif ($day == 12) {
                    $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','maitenance_last_day'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();
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
                    $mobile_msg_arr =!empty($val->mobile_message)? json_decode($val->mobile_message): [];
                    $sublet_msg_arr =!empty($val->sublet_message)? json_decode($val->sublet_message): [];
                    if(!empty($val->sublet_message) && $val->sublet_message!='null'){
                       $sublet_msg_arr =json_decode($val->sublet_message);
                    }else{
                       $sublet_msg_arr =[];
                    }

                    if(!empty($val->mobile_message) && $val->mobile_message!='null'){
                       $mobile_msg_arr =json_decode($val->mobile_message);
                    }else{
                       $mobile_msg_arr =[];
                    }
                    if(in_array('reminder',$mobile_msg_arr)){
                        dispatch( new WhatsappAPI($destination,$message, $org_id,$templ_json) )->onConnection('sync');
                    }

                    if(in_array('reminder',$sublet_msg_arr)){
                        // $destination = $val->sublet_number;
                        if(!empty($destination)){
                            dispatch( new WhatsappAPI($destination,$message, $org_id,$templ_json) )->onConnection('sync');
                        }
                    }
                // }
            }      
            $model12 = new \App\Models\Test_Cron();
            $data1['name'] = "Reminder cron";
            $data1['date'] = $now;
            $model1 = $model12->create($data1);
        })/*->everyMinute();/*->everyThreeHours()->days([1, 2, 3]);/*/->cron('30 16 1,8,12,14,18,26,30 * *');
        
            
        }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
// crontab -e command
// * * * * * cd /var/www/html/journal_entry && php artisan schedule:run >> /dev/null 2>&1