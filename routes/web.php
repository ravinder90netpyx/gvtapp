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
    OrganizationConfigController,
    TemplatesController,
    GroupController,
    ExpenseTypeController,
    ExpenseController,
    TenantController,
    TenancyController
};

use App\Http\Controllers\CronController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great! testing purpos
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
        Route::get('transaction_report/',[ReportController::class, 'getTransactionDetails'])->name('transaction_report');
        Route::post('report/ajax_transaction', [ReportController::class, 'ajaxTransactionDetails'])->name('report.ajax_transaction_details');
        Route::get('fine_report/', [ReportController::class, 'getFineReport'])->name('fine_report');
        Route::post('report/ajax_fine', [ReportController::class, 'ajaxFine'])->name('report.ajax_fine');
        Route::get('pending_report/', [ReportController::class, 'getPendingReport'])->name('pending_report');
        Route::get('search_data/', [ReportController::class, 'searchData'])->name('report.search_data');
        Route::get('personal_report/', [ReportController::class, 'getPersonalReport'])->name('personal_report');
        Route::get('expense_report/', [ReportController::class, 'getExpenseReport'])->name('expense_report');
        Route::post('report/ajax_data', [ReportController::class, 'ajaxExpense'])->name('report.ajax_data');
        Route::post('report/ajax_personal', [ReportController::class, 'ajaxPersonal'])->name('report.ajax_personal');
        Route::get('organization_configs/', [OrganizationConfigController::class, 'index'])->name('organization_configs.index');
        Route::post('organization_configs/', [OrganizationConfigController::class, 'store'])->name('organization_configs.store');
        Route::get('general_configs/', [GeneralConfigController::class, 'index'])->name('general_configs.index');
        Route::post('general_configs/', [GeneralConfigController::class,'store'])->name('general_configs.store');

        Route::post('tenancy/get_member', [TenancyController::class,'get_member'])->name('tenancy.get_member');

        Route::get('tenancy/{id}/make', [TenancyController::class, 'generate_file_'])->name('tenancy.generate_file_');
        Route::get('tenancy/{id}/show', [TenancyController::class, 'show_pdf'])->name('tenancy.show_pdf');
        
        Route::get('journal_entry/{id}/view', [JournalEntryController::class, 'view_pdf'])->name('journal_entry.view_pdf');
        Route::get('journal_entry/{id}/show', [JournalEntryController::class, 'show_pdf'])->name('journal_entry.show_pdf');
        Route::get('journal_entry/{id}/make', [JournalEntryController::class, 'generate_pdf_file'])->name('journal_entry.generate_pdf_file');
        Route::post('journal_entry/send', [JournalEntryController::class, 'send_msg'])->name('journal_entry.send_msg');
        Route::post('journal_entry/fine_get', [JournalEntryController::class, 'fine_ajax'])->name('journal_entry.fine_ajax');
        Route::post('journal_entry/get_table', [JournalEntryController::class, 'get_table'])->name('journal_entry.get_table');
        Route::get('members/{id}/reminder', [MembersController::class, 'send_reminder'])->name('members.send_reminder');
        Route::post('expense/ajax_name', [ExpenseController::class, 'ajax_name'])->name('expense.ajax_name');

        Route::post('dashboard', [DashboardController::class, 'ajax_year'])->name('dashboard.ajax_year');

        $routes_arr = [
            'sample'=>'SampleController',
            'organization' => 'OrganizationController',
            'user_roles' => 'SiteuserroleController',
            'users' => 'SiteuserController',
            'members' => 'MembersController',
            'charges' => 'ChargesController',
            'series' => 'SeriesController',
            'journal_entry' => 'JournalEntryController',
            'templates' => 'TemplatesController',
            'chargetype'=>'ChargeTypeController',
            'group'=>'GroupController',
            'expense_type'=>'ExpenseTypeController',
            'expenses'=>'ExpenseController',
            'tenant'=>'TenantController',
            'tenancy'=>'TenancyController'
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