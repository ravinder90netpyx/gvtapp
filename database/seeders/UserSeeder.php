<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'id' => '1',
            'uuid'=>Str::uuid(),
            'uname' => 'admin',
            'first_name' => 'Sukhmeet',
            'last_name' => 'Singh',
            'email' => 'sukhmeetraina@gmail.com',
            'phone' => '9878116682',            
            'email_verified_at' => now(),
            'password' => Hash::make('master25admin'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('users')->insert([
            'id' => '2',
            'uuid'=>Str::uuid(),
            'uname' => 'test1',
            'first_name' => 'test1',
            'organization_id' => '1',
            'last_name' => 'User',
            'email' => 'test1@test.com',
            'phone' => '1234567880',            
            'email_verified_at' => now(),
            'password' => Hash::make('123456'),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
