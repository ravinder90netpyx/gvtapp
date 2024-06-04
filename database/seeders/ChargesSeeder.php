<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class ChargesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('charges')->insert([
            'id' => '1',
            'name'=>'Regular',
            'rate' => '3000',
            
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('charges')->insert([
            'id' => '2',
            'name'=>'Custom',
            'rate' => '5000',
            
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('charges')->insert([
            'id' => '3',
            'name'=>'BigUnit',
            'rate' => '7500',
            
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
