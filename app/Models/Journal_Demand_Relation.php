<?php
namespace App\Models;

use App\Models\Base_Model;
#use Illuminate\Support\Facades\DB;

class Journal_Demand_Relation extends Base_Model{
    protected $table = 'journal_demand_relation';
    #protected $columnPrefix = '';
    protected $primaryKey = 'id';
    // protected $nameColumn = 'name';
    #protected $codeColumn = 'code';
    // protected $statusColumn = 'status';
    #protected $delstatusColumn = 'delstatus';
    #protected $sortOrderColumn = 'sort_order';
    // public $timestamps = true;
    // const CREATED_AT = 'created_at';
    // const UPDATED_AT = 'updated_at';

    /*protected $casts = [
        'price' => 'float'
    ];
    
    protected $attributes = [
        'guard_name' => 'web'
    ];
    */

    protected $fillable = [
        'organization_id',
        'member_id',
        'demand_charge_id',
        'journal_entry_id',
        'demand_id',
        'charge_type_id',
        'partial',
        'demand_status',
        'amount'
    ];
}