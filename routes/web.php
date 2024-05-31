<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\{
    SampleController,
    UserController,
    DashboardController,
    #ProfileController,
    OrganizationController,
    SiteuserroleController,
    SiteuserController,
    MembersController,
    ChargesController,
    SeriesController,
    JournalEntryController,
    ReportController,
    GeneralConfigController,
    OrganizationConfigController
};

use App\Http\Controllers\CronController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::pattern('id', '[a-f0-9\-]+');
Route::pattern('mode', '(activate|deactivate|delete)+');

/*Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('optimize:clear');
    return "1";
    // return what you want
});*/


Route::name('supanel.')->prefix('supanel')->group(function () { 
    
    Route::group(['middleware' => ['web']], function() {
        # guest actions
        Route::get('/', [UserController::class, 'login'])->name('login');
        #Route::get('login', [UserController::class, 'login'])->name('login');
        Route::post('loginpost', [UserController::class, 'loginPost'])->name('loginpost');
    });

    Route::group(['middleware' => ['is.admin']], function(){ 
        # logged in user        
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('logout', [UserController::class, 'logout'])->name('logout');
        Route::get('journal_entry/ajax_member/', [JournalEntryController::class, 'ajax_member'])->name('journal_entry.ajax_member');
        Route::post('journal_entry/series_select',[JournalEntryController::class, 'series_select'])->name('journal_entry.series_select');
        Route::post('journal_entry/series_data', [JournalEntryController::class, 'series_data'])->name('journal_entry.series_data');
        Route::get('report/', [ReportController::class, 'index'])->name('report.index');
        Route::post('report/order_by_date', [ReportController::class, 'getReportByDate'])->name('report.report_by_date');
        Route::get('pending_report/', [ReportController::class, 'getPendingReport'])->name('pending_report');
        Route::get('organization_configs/', [OrganizationConfigController::class, 'index'])->name('organization_configs.index');
        Route::post('organization_configs/', [OrganizationConfigController::class, 'store'])->name('organization_configs.store');
        Route::get('general_configs/', [GeneralConfigController::class, 'index'])->name('general_configs.index');
        Route::post('general_configs/', [GeneralConfigController::class,'store'])->name('general_configs.store');
        Route::get('journal_entry/{id}/view', [JournalEntryController::class, 'view_pdf'])->name('journal_entry.view_pdf');
        Route::get('journal_entry/{id}/show', [JournalEntryController::class, 'show_pdf'])->name('journal_entry.show_pdf');
        Route::get('journal_entry/{id}/make', [JournalEntryController::class, 'generate_pdf_file'])->name('journal_entry.generate_pdf_file');
        Route::get('journal_entry/{id}/send', [JournalEntryController::class, 'send_msg'])->name('journal_entry.send_msg');
        Route::get('journal_entry/{id}/reminder', [JournalEntryController::class, 'send_reminder'])->name('journal_entry.send_reminder');

        $routes_arr = [
            'sample'=>'SampleController',
            'organization' => 'OrganizationController',
            'user_roles' => 'SiteuserroleController',
            'users' => 'SiteuserController',
            'members' => 'MembersController',
            'charges' => 'ChargesController',
            'series' => 'SeriesController',
            'journal_entry' => 'JournalEntryController',
        ];

        foreach($routes_arr as $rak=>$rav){
            Route::get($rak.'/{mode}/{id}', "\App\Http\Controllers\Admin\\".$rav.'@action')->name($rak.'.action');
            Route::get($rak.'/bulk', "\App\Http\Controllers\Admin\\".$rav.'@bulk')->name($rak.'.bulk');
            Route::resource($rak, "\App\Http\Controllers\Admin\\".$rav );
        }
        
    });
});

#Route::get('cron', [CronController::class, 'index'])->name('cron.index');
Route::get('redis', [CronController::class, 'redisTest'])->name('redis.test');
Route::get('optimize', [CronController::class, 'optimize'])->name('optimize.index');

Route::get('/', function () {
    return view('welcome');
});