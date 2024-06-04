<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class OrganizationSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('organization_settings')->insert([
            'id' => '1', 
            'organization_id' => '1',
            'group' => 'whatsapp_settings',
            'key' => 'source_number',
            'value' => '919041362511',
            'is_serialized' => '0',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('organization_settings')->insert([
            'id' => '2', 
            'organization_id' => '1',
            'group' => 'whatsapp_settings',
            'key' => 'template_id',
            'value' => 'c376f4e4-2743-4eb9-8cdb-2648f7457d22',
            'is_serialized' => '0',    
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('organization_settings')->insert([
            'id' => '3', 
            'organization_id' => '1',
            'group' => 'whatsapp_settings',
            'key' => 'api_key',
            'value' => '4ssd1jldzf7mhiprkmwt5iwff6iuafqv',
            'is_serialized' => '0',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
