<?php
namespace App\Models;

use App\Models\Base_Model;
#use Illuminate\Support\Facades\DB;

class Event_Txn extends Base_Model{
    protected $table = 'event_txn';
    #protected $columnPrefix = '';
    protected $primaryKey = 'id';
    protected $nameColumn = 'name';
    #protected $codeColumn = 'code';
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

    protected $fillable = [
        'organization_id',
        'event_id',
        'series_id',
        'member_id',
        'series_number',
        'series_next_number',
        'name',
        'mobile_number',
        'amount',
        'mode',
        'payment_date',
        'payment_status',
        'file_name',
        'remark',
        'count'
    ];
}