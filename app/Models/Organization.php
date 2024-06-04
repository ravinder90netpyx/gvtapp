<?php
namespace App\Models;

use App\Models\Base_Model;
#use Illuminate\Support\Facades\DB;

class Organization extends Base_Model{
    protected $table = 'organization';
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
        'description',
        'gst_number',
        'users_allowed'
    ];
}