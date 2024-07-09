<?php
namespace App\Models;

use App\Models\Base_Model;
#use Illuminate\Support\Facades\DB;

class Entrywise_Fine extends Base_Model{
    protected $table = 'entrywise_fine';
    #protected $columnPrefix = '';
    protected $primaryKey = 'id';
    // protected $nameColumn = 'name';
    #protected $codeColumn = 'code';
    protected $statusColumn = 'status';
    protected $delstatusColumn = 'delstatus';
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
        'member_id',
        'journal_entry_id',
        'total_fine',
        'fine_paid'
    ];
}