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
        Artisan::call('optimize:clear');
        Artisan::call('optimize');

        return "Optimized";
    }
}
