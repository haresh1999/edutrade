<?php

namespace Database\Seeders;

use App\Models\SabpaisaUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SabpaisaUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SabpaisaUser::create([
            'name' => 'Edutrade',
            'email' => 'ikondubai@gmail.com',
            'mobile' => '8075088769',
            'client_id' => 'edutrade',
            'client_secret' => 'c73ba053-bf3f-4c9e-88ae-9e49fd4534e4',
            'sandbox_client_id' => 'edutrade',
            'sandbox_client_secret' => '1890b383-345a-42d3-88c7-6e80efd08460',
            'callback_url' => 'http://edutrade.in/payment/success',
            'notify_url' => 'http://edutrade.in/payment/failed',
        ]);
    }
}
