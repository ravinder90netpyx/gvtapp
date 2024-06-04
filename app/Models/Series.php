<?php
namespace App\Models;

use App\Models\Base_Model;
use Illuminate\Support\Facades\DB;
use App\Casts\CommaString;

class Series extends Base_Model{
    protected $table = 'series';
    protected $primaryKey = 'id';
    protected $nameColumn = 'name';
    protected $codeColumn = 'name';
    protected $statusColumn = 'status';
    protected $delstatusColumn = 'delstatus';
    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public const NumberSeparator = [
        '/' => 'Forward Slash (/)',
        '-' => 'Hyphen (-)',
        '_' => 'Underscore (_)'
    ];

    public const ModuleType = [
        'journal_entry' => 'Journal Entry'
    ];


    protected $fillable = [
        'organization_id',
        'name',
        'start_number',
        'next_number',
        'min_length',
        'number_separator',
        'type',
    ];

    public function getSeparatorList(){
        return $this::NumberSeparator;
    }

    public function getSeparator($value){
        return $this::NumberSeparator[$value] ?? false;
    }

    public function getModuleTypeList(){
        return $this::ModuleType;
    }

    public function getModuleType($value){
        return $this::ModuleType[$value] ?? false;
    }

     public function getItemTypeList(){
        return $this::ItemType;
    }

    public function getItemType($value){
        return $this::ItemType[$value] ?? false;
    }

    
    // public function organization_user_count($organization_id){
    //     $user_count = DB::table('users')->where([ ['organization_id', '=', $organization_id] ])->count();
    //     return $user_count ?? 0;
    // }

    public function getSerialNumber($series_id, $second_num = null){
        $data1 = DB::table($this->table)->where($this->primaryKey, $series_id)->select(['name', 'number_separator', 'min_length', 'next_number'])->first();
        $prefix_code = $data1->name.$data1->number_separator;
        $min_length = $data1->min_length;
        $number = $data1->next_number;

        if($second_num != null) $number = $second_num;
        
        return $prefix_code.str_pad($number, $min_length, "0", STR_PAD_LEFT);
    }

    public function updateNextNumber($series_id){
        $data1 = DB::table($this->table)->where($this->primaryKey, $series_id)->select(['next_number'])->first();

        DB::table($this->table)->where($this->primaryKey, $series_id)->update(['next_number'=> $data1->next_number + 1]);
        return true;
    }
}