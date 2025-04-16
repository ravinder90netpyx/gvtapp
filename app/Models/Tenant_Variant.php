<?php
namespace App\Models;

use App\Models\Base_Model;
#use Illuminate\Support\Facades\DB;

class Tenant_Variant extends Base_Model{
    protected $table = 'tenant_variant';
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
        'tenant_master_id',
        'name',
        'age',
        'gender',
        'photo',
        'photo_name',
        'document',
        'document_name',
        'mobile_number',
        'email',
        'locality',
        'city',
        'state',
        'pincode',
        'isfamily',
        'police_verification',
        'police_verification_name'
    ];
}