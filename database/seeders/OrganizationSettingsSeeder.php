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
            'key' => 'api_url',
            'value' => 'https://api.gupshup.io/wa/api/v1/template/msg',
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
        DB::table('organization_settings')->insert([
            'id' => '4', 
            'organization_id' => '1',
            'group' => 'whatsapp_settings',
            'key' => 'src_name',
            'value' => 'GVTGH9',
            'is_serialized' => '0',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('organization_settings')->insert([
            'id' => '5', 
            'organization_id' => '1',
            'group' => 'whatsapp_settings',
            'key' => 'channel',
            'value' => 'whatsapp',
            'is_serialized' => '0',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('organization_settings')->insert([
            'id' => '6', 
            'organization_id' => '2',
            'group' => 'whatsapp_settings',
            'key' => 'source_number',
            'value' => '919041362511',
            'is_serialized' => '0',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('organization_settings')->insert([
            'id' => '7',
            'organization_id' => '2',
            'group' => 'whatsapp_settings',
            'key' => 'api_url',
            'value' => 'https://api.gupshup.io/wa/api/v1/template/msg',
            'is_serialized' => '0',    
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('organization_settings')->insert([
            'id' => '8',
            'organization_id' => '2',
            'group' => 'whatsapp_settings',
            'key' => 'api_key',
            'value' => '4ssd1jldzf7mhiprkmwt5iwff6iuafqv',
            'is_serialized' => '0',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('organization_settings')->insert([
            'id' => '9',
            'organization_id' => '2',
            'group' => 'whatsapp_settings',
            'key' => 'src_name',
            'value' => 'GVTGH9',
            'is_serialized' => '0',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('organization_settings')->insert([
            'id' => '10',
            'organization_id' => '2',
            'group' => 'whatsapp_settings',
            'key' => 'channel',
            'value' => 'whatsapp',
            'is_serialized' => '0',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
