<?php
namespace App\Models;

use App\Models\Base_Model;
#use Illuminate\Support\Facades\DB;

class ChargeType extends Base_Model{
    protected $table = 'charge_type';
    #protected $columnPrefix = '';
    protected $primaryKey = 'id';
    protected $nameColumn = 'name';
    protected $statusColumn = 'status';
    protected $delstatusColumn = 'delstatus';
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /*protected $casts = [
        'price' => 'float'
    ];*/

    protected $fillable = [
        'name',
        'alias_name',
        'type',
        'price'
    ];
}