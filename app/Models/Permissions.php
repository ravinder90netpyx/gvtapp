<?php
namespace App\Models;

use App\Models\Base_Model;
#use Illuminate\Support\Facades\DB;

class Permissions extends Base_Model{
    protected $table = 'permissions';
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $casts = [
    ];

    protected $fillable = [
        'name',
        'guard_name',
    ];
}