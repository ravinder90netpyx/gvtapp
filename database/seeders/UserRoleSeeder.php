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

            [ 'name' => 'general_settings.manage_organization_config'],
            [ 'name' => 'general_settings.manage_general_config'],

            [ 'name' => 'templates.manage' ],
            [ 'name' => 'templates.add' ],
            [ 'name' => 'templates.edit' ],
            [ 'name' => 'templates.delete' ],
            [ 'name' => 'templates.status' ],

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

            [ 'name' => 'charge_type.manage' ],
            [ 'name' => 'charge_type.add' ],
            [ 'name' => 'charge_type.edit' ],
            [ 'name' => 'charge_type.delete' ],
            [ 'name' => 'charge_type.status' ],

            [ 'name' => 'members.manage' ],
            [ 'name' => 'members.add' ],
            [ 'name' => 'members.edit' ],
            [ 'name' => 'members.delete' ],
            [ 'name' => 'members.status' ],
            [ 'name' => 'members.reminder' ],

            [ 'name' => 'group.manage' ],
            [ 'name' => 'group.add' ],
            [ 'name' => 'group.edit' ],
            [ 'name' => 'group.delete' ],
            [ 'name' => 'group.status' ],

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

            [ 'name' => 'expense.manage' ],
            [ 'name' => 'expense.add' ],
            [ 'name' => 'expense.edit' ],
            [ 'name' => 'expense.delete' ],
            [ 'name' => 'expense.status' ],

            [ 'name' => 'expense_type.manage' ],
            [ 'name' => 'expense_type.add' ],
            [ 'name' => 'expense_type.edit' ],
            [ 'name' => 'expense_type.delete' ],
            [ 'name' => 'expense_type.status' ],

            [ 'name' => 'tenancy.manage' ],
            [ 'name' => 'tenancy.add' ],
            [ 'name' => 'tenancy.edit' ],
            [ 'name' => 'tenancy.delete' ],
            [ 'name' => 'tenancy.status' ],

            [ 'name' => 'tenant.manage' ],
            [ 'name' => 'tenant.add' ],
            [ 'name' => 'tenant.edit' ],
            [ 'name' => 'tenant.delete' ],
            [ 'name' => 'tenant.status' ],

            [ 'name' => 'custom_global_variable.manage' ],
            [ 'name' => 'custom_global_variable.add' ],
            [ 'name' => 'custom_global_variable.edit' ],
            [ 'name' => 'custom_global_variable.delete' ],
            [ 'name' => 'custom_global_variable.status' ],

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
