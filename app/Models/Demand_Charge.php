<?php
namespace App\Models;

use App\Models\Base_Model;
#use Illuminate\Support\Facades\DB;

class Demand_Charge extends Base_Model{
    protected $table = 'demand_charge';
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
        'demand_id',
        'journal_entry_id',
        'member_id',
        'member_name',
        'charge_type_id',
        'pending_amount',
        'paid_amount',
        'remark',
        'charge_status',
        'partial',
        'times_paid',
        'status'
    ];
}