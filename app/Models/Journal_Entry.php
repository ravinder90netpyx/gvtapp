<?php
namespace App\Models;

use App\Models\Base_Model;
#use Illuminate\Support\Facades\DB;

class Journal_Entry extends Base_Model{
    protected $table = 'journal_entry';
    #protected $columnPrefix = '';
    protected $primaryKey = 'id';
    // protected $nameColumn = 'name';
    // protected $codeColumn = 'mobile_number';
    protected $statusColumn = 'status';
    #protected $delstatusColumn = 'delstatus';
    #protected $sortOrderColumn = 'sort_order';
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /*protected $casts = [
        'price' => 'float'
    ];
    
    protected $attributes = [
        'guard_name' => 'web'
    ];
    */
    public const ModeOption = [
        'cash' => 'Cash',
        'online' => 'Online',
        'cheque' => 'Cheque'
    ];

    protected $fillable = [
        'organization_id',
        'series_id',
        'series_number',
        'entry_year',
        'entry_date',
        'member_id',
        'file_name',
        'series_next_number',
        'charge',
        'from_month',
        'to_month',
        'payment_mode',
        'partial',
        'custom_month',
        'remarks',
        'check_number'
    ];

    public static function getPaymentMode(){
        return self::ModeOption;
    }
}