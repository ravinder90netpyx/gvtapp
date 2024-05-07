<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = array(
          array('id' => '1','name' => 'Admin','guard_name' => 'web', 'created_by'=>'1', 'created_at' => now(),'updated_at' => now())
        );
        foreach($roles as $rr){
            DB::table('roles')->insert($rr);
        }

        $permissions = [
            [ 'name' => 'organization.manage' ],
            [ 'name' => 'organization.add' ],
            [ 'name' => 'organization.edit' ],
            [ 'name' => 'organization.delete' ],
            [ 'name' => 'organization.status' ],

            [ 'name' => 'user_roles.manage' ],
            [ 'name' => 'user_roles.add' ],
            [ 'name' => 'user_roles.edit' ],
            [ 'name' => 'user_roles.delete' ],
            [ 'name' => 'user_roles.status' ],

            [ 'name' => 'users.manage' ],
            [ 'name' => 'users.add' ],
            [ 'name' => 'users.edit' ],
            [ 'name' => 'users.delete' ],
            [ 'name' => 'users.status' ],

            [ 'name' => 'charges.manage' ],
            [ 'name' => 'charges.add' ],
            [ 'name' => 'charges.edit' ],
            [ 'name' => 'charges.delete' ],
            [ 'name' => 'charges.status' ],

            [ 'name' => 'members.manage' ],
            [ 'name' => 'members.add' ],
            [ 'name' => 'members.edit' ],
            [ 'name' => 'members.delete' ],
            [ 'name' => 'members.status' ],

            [ 'name' => 'series.manage' ],
            [ 'name' => 'series.add' ],
            [ 'name' => 'series.edit' ],
            [ 'name' => 'series.delete' ],
            [ 'name' => 'series.status' ],

            
            [ 'name' => 'journal_entry.manage' ],
            [ 'name' => 'journal_entry.add' ],
            [ 'name' => 'journal_entry.edit' ],
            [ 'name' => 'journal_entry.delete' ],
            [ 'name' => 'journal_entry.status' ],
            [ 'name' => 'journal_entry.report' ],

        ];

        $default_data = [ 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now() ];
        foreach($permissions as $prr){
            $ins_rr = array_merge($default_data, $prr);
            DB::table('permissions')->insert($ins_rr);
        }

        $model_has_roles = array(
          array('role_id' => '1', 'model_type' => 'App\Models\User','model_id' => '1')
        );
        foreach($model_has_roles as $mrr){
            DB::table('model_has_roles')->insert($mrr);
        }

        $all_permissions = DB::table('permissions')->get();
        if(!empty($all_permissions)){
            foreach($all_permissions as $apr){
                $inrr = array('permission_id'=>$apr->id, 'role_id'=>'1');
                DB::table('role_has_permissions')->insert($inrr);
            }
        }
    }
}
