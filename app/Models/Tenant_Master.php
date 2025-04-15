<?php
namespace App\Models;

use App\Models\Base_Model;
#use Illuminate\Support\Facades\DB;

class Tenant_Master extends Base_Model{
    protected $table = 'tenant_master';
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
        'user_id',
        'type',
        'member_id',
        'member_name',
        'start_date',
        'rent_agreement',
        'rent_agreement_name',
        'police_verification',
        'police_verification_name',
        'undertaking',
        'undertaking_name',
        'acceptance',
        'aceeptance_name'
    ];
}