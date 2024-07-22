<?php
namespace App\Models;

use App\Models\Base_Model;
#use Illuminate\Support\Facades\DB;

class Templates extends Base_Model{
    protected $table = 'templates';
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
    protected $templates_name = [
        'welcome' => 'Welcome',
        'reminder' => 'Reminder',
        'reciept' => 'Reciept',
        'overdue' => 'Over-due',
        'maitenance_last_day' => 'Maitenance Last Day',
        'fine' => 'Fine'
    ];

    protected $fillable = [
        'organization_id',
        'name',
        'template_id',
        'params'
    ];

    public function template_name(){
        return $this->templates_name;
    }
}