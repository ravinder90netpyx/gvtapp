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
        $permissions = [
          [ 'group' => 'system', 'key' => 'starting_year', 'value' => '2020' ],
          [ 'group' => 'pdf', 'key' => 'pdf_note', 'value' => 'Note: Overdue Account are subject to pay late fees of Rs. 50/day till the date of clearance.

If you found any discrepancy, please reach out to us.' ],
          [ 'group' => 'pdf', 'key' => 'line1', 'value' => 'This is a system-generated receipt and does not require signature. Any unauthorized use, disclosure,
dissemination or copying of this receipt is strictly prohibited and may be unlawful.' ],
          [ 'group' => 'pdf', 'key' => 'address', 'value' => 'Regd. Office: Golf View Towers, GH-9, Sector-91, SAS Nagar, Mohali, Punjab – 160055' ],
        ];

        $counter = 1;
        $default_data = [ 'is_serialized' => '0', 'created_at' => now(), 'updated_at' => now() ];
        foreach($permissions as $prr){
            $default_data['id'] = $counter;
            $ins_rr = array_merge($default_data, $prr);
            DB::table('settings')->insert($ins_rr);
            $counter++;
        }
    }
}
