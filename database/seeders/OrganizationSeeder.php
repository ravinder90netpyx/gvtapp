<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('organization')->insert([
            'id' => '1', 
            'name' => 'Test Organization',
            'description' => '',
            'gst_number' => null,
            'users_allowed' => '10',           
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('organization')->insert([
            'id' => '2', 
            'name' => 'Test Organization2',
            'description' => '',
            'gst_number' => null,
            'users_allowed' => '10',           
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
