<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasPermissions;

    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $nameColumn = 'uname';
    protected $codeColumn = 'uname';
    protected $statusColumn = 'status';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'uname',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'organization_id',
        'created_by'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function organization_user_count($organization_id){
        $user_count = DB::table('users')->where([ ['organization_id', '=', $organization_id] ])->count();
        return $user_count ?? 0;
    }

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
     * Get Name By Id.
     *
     * @return Name
    */
    public function getNameById($id){
        $conditions = [
            [ $this->primaryKey, '=', $id ]
        ];
        #if(!empty($this->delstatusColumn)) $conditions[] = [ $this->delstatusColumn, '<', '1' ];

        $options = DB::table($this->table)->select(['first_name', 'last_name'])->where($conditions)->first();
        if(!empty($options->first_name) || !empty($options->last_name)){
            return $options->first_name.' '.$options->last_name;
        } else{
            return false;
        }

        /*$options = DB::table($this->table)->select([$this->nameColumn])->where($conditions)->first();
        if(!empty($options->{ $this->nameColumn })){
            return $options->{ $this->nameColumn };
        } else{
            return false;
        }*/
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
        $options = DB::table($this->table)->select([$this->primaryKey, 'first_name', 'last_name'])->where($conditions)->get()->toArray();
        $arr = [];
        if(!empty($options)){
            foreach($options as $opt){
                $arr[$opt->{ $this->primaryKey }] = $opt->first_name.' '.$opt->last_name;
            }
        }

        return $arr;
    }
}
