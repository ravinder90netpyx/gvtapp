<?php
namespace App\Models;

use App\Models\Base_Model;
#use Illuminate\Support\Facades\DB;

class Roles extends Base_Model{
    protected $table = 'roles';
    protected $columnPrefix = '';
    protected $primaryKey = 'id';
    protected $nameColumn = 'name';
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /*protected $casts = [
        'name' => 'float'
    ];*/

    protected $attributes = [
        'guard_name' => 'web'
    ];

    protected $fillable = [
        'name',
        'guard_name',
        'created_by'
    ];
}