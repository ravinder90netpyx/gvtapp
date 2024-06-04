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

            $member_model = \App\Models\Members::where([['status','>','0'], ['delstatus','<','1']])->get();

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

            foreach ($member_model as $val){
                $send_notification = 1;
                $org_model = new \App\Models\Organization_Settings();
                $org_id = $val->organization_id;
                $params = [];
                $templ_id = $org_model->getVal('whatsapp_reminder', 'template_id',$org_id);

                $template_arr = array(
                    'id' => $templ_id,
                    'params' => $params
                );
                $templ_json = json_encode($template_arr, true);
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
        
            // $settingsModel = new settingsModel;
            // $sch_count = $settingsModel->getVal('connection', 'schedule_hit_count');
            // //echo "bbnvcnb";
            // //Storage::append('custom.log', $sch_count);

            // $apConGet = $apConModel->where([ ['delstatus', '<', '1'], ['status', '>', '0'], ['sync_enabled', '>', '0'] ])->select(['id'])->get();
            // if(!empty($apConGet)){
            //     foreach($apConGet as $acg){
            //         $row_id = $acg->id;
            //         #dispatch( new \App\Jobs\PutApiData($row_id, '') )->onConnection('redis');
            //         $instModel = $apConModel->find($row_id);
            //         $uidd = $instModel->user_id;
            //         $freq_count = ($instModel->connection_sync_frequency)/10;
                    
            //         if(!empty($uidd) && $sch_count%$freq_count==0){
            //             //Storage::append('custom.log', 'Record ID '.$row_id.' is executed at '.now().' interval '.$freq_count);
            //             $logData = [ 'api_connection_id'=>$row_id, 'user_id'=>$uidd, 'created_by'=>NULL, 'user_agent'=>NULL, 'ip_address'=>NULL ];
            //             dispatch( new \App\Jobs\PutApiData($row_id, '', $logData) )->onConnection('redis');
            //         }
            //     }

            //     $settingsModel->insOrUpd(['group'=>'connection', 'key'=>'schedule_hit_count'], ['value'=>$sch_count+1]);
            // }
        })->everyMinute();/*->monthlyOn(12, '15:00');*/
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
