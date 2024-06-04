<?php
namespace App\Models;

use App\Models\Base_Model;
#use Illuminate\Support\Facades\DB;

class Role_Has_Permissions extends Base_Model{
    protected $table = 'role_has_permissions';
    public $timestamps = false;

    protected $casts = [
        'permission_id' => 'integer',
        'role_id' => 'integer',
    ];

    protected $fillable = [
        'permission_id',
        'role_id',
    ];
}