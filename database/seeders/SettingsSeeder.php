<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
        /*$permissions = [
          [ 'group' => 'connection', 'key' => 'schedule_hit_count', 'value' => '1' ],
        ];

        $counter = 1;
        $default_data = [ 'is_serialized' => '0', 'created_at' => now(), 'updated_at' => now() ];
        foreach($permissions as $prr){
            $default_data['id'] = $counter;
            $ins_rr = array_merge($default_data, $prr);
            DB::table('settings')->insert($ins_rr);
            $counter++;
        }*/
    }
}
