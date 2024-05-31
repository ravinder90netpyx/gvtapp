<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Storage;
use App\Models\API_Connections;
use App\Models\Settings as settingsModel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->call(function (){
            $apConModel = new API_Connections;
            $settingsModel = new settingsModel;
            $sch_count = $settingsModel->getVal('connection', 'schedule_hit_count');
            //echo "bbnvcnb";
            //Storage::append('custom.log', $sch_count);

            $apConGet = $apConModel->where([ ['delstatus', '<', '1'], ['status', '>', '0'], ['sync_enabled', '>', '0'] ])->select(['id'])->get();
            if(!empty($apConGet)){
                foreach($apConGet as $acg){
                    $row_id = $acg->id;
                    #dispatch( new \App\Jobs\PutApiData($row_id, '') )->onConnection('redis');
                    $instModel = $apConModel->find($row_id);
                    $uidd = $instModel->user_id;
                    $freq_count = ($instModel->connection_sync_frequency)/10;
                    
                    if(!empty($uidd) && $sch_count%$freq_count==0){
                        //Storage::append('custom.log', 'Record ID '.$row_id.' is executed at '.now().' interval '.$freq_count);
                        $logData = [ 'api_connection_id'=>$row_id, 'user_id'=>$uidd, 'created_by'=>NULL, 'user_agent'=>NULL, 'ip_address'=>NULL ];
                        dispatch( new \App\Jobs\PutApiData($row_id, '', $logData) )->onConnection('redis');
                    }
                }

                $settingsModel->insOrUpd(['group'=>'connection', 'key'=>'schedule_hit_count'], ['value'=>$sch_count+1]);
            }
        })/*->everyTenMinutes();*/->monthlyOn(12, '15:00');
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
