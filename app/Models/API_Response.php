<?php
namespace App\Models;

use App\Models\Base_Model;
#use Illuminate\Support\Facades\DB;

class API_Response extends Base_Model{
    protected $table = 'api_response';
    #protected $columnPrefix = '';
    protected $primaryKey = 'id';
    // protected $nameColumn = 'name';
    #protected $codeColumn = 'code';
    // protected $statusColumn = 'status';
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
        'journal_entry_id',
        'response',
        'category'
    ];
}