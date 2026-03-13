<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate([
            'email' => 'ikondubai@gmail.com',
            'mobile' => '8075088769',
        ], [
            'name' => 'Apexonline',
            'email_verified_at' => now(),
            'client_id' => 'apexonline',
            'client_secret' => 'c73ba053-bf3f-4c9e-88ae-9e49fd4534e4',
            'sbx_client_id' => 'apexonline',
            'sbx_client_secret' => '1890b383-345a-42d3-88c7-6e80efd08460',
            'password' => bcrypt('123456'),
            'env' => 'sandbox',
            'callback_secret' => '03e1bf84-50aa-4d91-8925-e66b17c088a4',
            'whitelist_ip' => null,
            'default_gateway' => null
        ]);
    }
}
