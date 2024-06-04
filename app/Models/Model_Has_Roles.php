<?php
namespace App\Models;

use App\Models\Base_Model;
#use Illuminate\Support\Facades\DB;

class Model_Has_Roles extends Base_Model{
    protected $table = 'model_has_roles';
    public $timestamps = false;

    protected $casts = [
        'role_id' => 'integer',
        'model_id' => 'integer'
    ];

    protected $fillable = [
        'role_id',
        'model_type',
        'model_id',
    ];
}