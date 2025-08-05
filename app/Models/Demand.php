<?php
namespace App\Models;

use App\Models\Base_Model;
#use Illuminate\Support\Facades\DB;

class Demand extends Base_Model{
    protected $table = 'demand';
    #protected $columnPrefix = '';
    protected $primaryKey = 'id';
    protected $nameColumn = 'member_name';
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
        'series_id',
        'charge_type_id',
        'demand_reciept_number',
        'series_next_number',
        'member_name',
        'member_id',
        'file_name',
        'remark',
        'from_date',
        'to_date',
        'current_date',
        'due_date',
        'charge',
        'demand_status',
        'count',
        'partial',
        'status'
    ];
}