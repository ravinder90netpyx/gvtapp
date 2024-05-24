<?php
namespace App\Models;

use App\Models\Base_Model;
#use Illuminate\Support\Facades\DB;

class Settings extends Base_Model{
    protected $table = 'settings';
    #protected $columnPrefix = '';
    protected $primaryKey = 'id';
    protected $nameColumn = 'value';
    #protected $codeColumn = 'value';
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

    protected $fillable = [
        'group',
        'key',
        'value',
        'is_serialized'
    ];

    public function getGroup($group){
        $qry = $this->where([ ['group', '=', $group] ])->select(['key', 'value', 'is_serialized'])->get();
        $data = [];
        if(!empty($qry)){
            foreach($qry as $itr){
                if($itr->is_serialized=='1') $data[$itr->key] = unserialize($itr->value);
                else $data[$itr->key] = $itr->value;
            }
        }

        return $data;
    }
    
    public function getVal($group, $key){
        return $this->where([ ['group', '=', $group], ['key', '=', $key] ])->first()->value;
    }

    public function insOrUpd($conditions=[], $data=[]){
        return $this->updateOrCreate($conditions, $data);
    }
}