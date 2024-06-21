<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Storage;
use App\Jobs\WhatsappAPI;
use Carbon\Carbon;
use App\Models\API_Connections;
use App\Models\Settings as settingsModel;

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

            $member_model = \App\Models\Members::where([['status','>','0'], ['delstatus','<','1'], ['group_id','=', '2']])->get();

            $type = 'document';
            // $date_arr = explode(' ', $model->entry_date);
            // $date = Carbon::parse($date_arr[0])->format('d-M-Y');
            // $month = Carbon::parse($model->from_month)->format('M Y')."-".Carbon::parse($model->to_month)->format('M Y');
            // $params = array(
            //     $model->charge,
            //     $month,
            //     $date
            // );
            // echo "hello"; exit;

            $message = '';
            $message = json_encode($message, true);
            $helpers = new \App\Helpers\helpers();
            foreach ($member_model as $val){
                $send_notification = 1;
                $org_model = new \App\Models\Organization_Settings();
                $org_id = $val->organization_id;
                $charge = \App\Models\Charges::find($val->charges_id);
                $data = [
                    'name'=> $val->name,
                    'mobile_number' => $val->mobile_number,
                    'unit_no'=> $val->unit_number,
                    'charge' => $charge->rate
                ];
                $temp= \App\Models\Templates::where([['organization_id', '=',$org_id],['name','=','reminder'], ['delstatus', '<', '1'], ['status', '>', '0']])->first();
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
                if(!empty($send_notification)){
                    dispatch( new WhatsappAPI($destination,$message, $org_id,$templ_json) )->onConnection('sync');
                }
            }
        
        })/*->everyMinute();*/->monthlyOn(22, '00:00');
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
