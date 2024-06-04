<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Base_Model extends Model{
    use HasFactory;

    /**
     * Activate record from table.
     *
     * @return true
    */
    public function postActivate($id)
    {
        if(DB::table($this->table)->where($this->primaryKey, $id)->update([$this->statusColumn => '1'])) return true;
    }

    /**
     * Deactivate record from table.
     *
     * @return true
    */
    public function postDeactivate($id)
    {
        if(DB::table($this->table)->where($this->primaryKey, $id)->update([$this->statusColumn => '0'])) return true;
    }

    /**
     * Delete record from table.
     *
     * @return true
    */
    public function postDelete($id)
    {
        if(!empty($this->delstatusColumn)){
            if(DB::table($this->table)->where($this->primaryKey, $id)->update([$this->delstatusColumn => '1'])) return true;
        } else{
            if(DB::table($this->table)->where($this->primaryKey, '=', $id)->delete()) return true;
        }
    }

    /**
     * Get Options for select box.
     *
     * @return true
    */
    public function getOptionValues($mode='insert'){
        $conditions = [];
        if(!empty($this->delstatusColumn)) $conditions[] = [ $this->delstatusColumn, '<', '1' ];
        if($mode=='insert') $conditions[] = [ $this->statusColumn, '>', '0' ];
        $options = DB::table($this->table)->select([$this->primaryKey, $this->nameColumn])->where($conditions)->get()->toArray();
        $arr = [];
        if(!empty($options)){
            foreach($options as $opt){
                $arr[$opt->{ $this->primaryKey }] = $opt->{ $this->nameColumn };
            }
        }

        return $arr;
    }

    /**
     * Get Name By Id.
     *
     * @return Name
    */
    public function getNameById($id){
        $conditions = [
            [ $this->primaryKey, '=', $id ]
        ];
        #if(!empty($this->delstatusColumn)) $conditions[] = [ $this->delstatusColumn, '<', '1' ];

        $options = DB::table($this->table)->select([$this->nameColumn])->where($conditions)->first();
        if(!empty($options->{ $this->nameColumn })){
            return $options->{ $this->nameColumn };
        } else{
            return false;
        }
    }

    /**
     * Get Id By Name.
     *
     * @return Id
    */
    public function getIdByName($name, $attribute_id=''){
        $conditions = [
            [ $this->nameColumn, '=', $name ]
        ];
        #if(!empty($this->delstatusColumn)) $conditions[] = [ $this->delstatusColumn, '<', '1' ];

        $options = DB::table($this->table)->select([$this->primaryKey])->where($conditions)->first();
        if(!empty($options->{ $this->primaryKey })){
            return $options->{ $this->primaryKey };
        } else{
            return false;
        }
    }
    
    public function getColumnPrefix(){
        return isset($this->columnPrefix) ? $this->columnPrefix : false;
    }

    public function getNameColumn(){
        return isset($this->nameColumn) ? $this->nameColumn : false;
    }

    public function getCodeColumn(){
        return isset($this->codeColumn) ? $this->codeColumn : false;
    }

    public function getStatusColumn(){
        return isset($this->statusColumn) ? $this->statusColumn : false;
    }

    public function getDelStatusColumn(){
        return isset($this->delstatusColumn) ? $this->delstatusColumn : false;
    }

    public function getSortOrderColumn(){
        return isset($this->sortOrderColumn) ? $this->sortOrderColumn : false;
    }
}