<?php
namespace App\Models;

use App\Models\Base_Model;
#use Illuminate\Support\Facades\DB;

class Model_Has_Permissions extends Base_Model{
    protected $table = 'model_has_permissions';
    public $timestamps = false;

    protected $casts = [
        'permission_id' => 'integer',
        'model_id' => 'integer',
    ];

    protected $fillable = [
        'permission_id',
        'model_type',
        'model_id',
    ];
}