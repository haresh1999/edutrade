<?php

namespace Database\Seeders;

use App\Models\SabpaisaUser;
use Illuminate\Database\Seeder;

class SabpaisaUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SabpaisaUser::updateOrCreate([
            'email' => 'ikondubai@gmail.com',
        ], [
            'name' => 'Edutrade',
            'mobile' => '8075088769',
            'client_id' => 'edutrade',
            'client_secret' => 'c73ba053-bf3f-4c9e-88ae-9e49fd4534e4',
            'sandbox_client_id' => 'edutrade',
            'sandbox_client_secret' => '1890b383-345a-42d3-88c7-6e80efd08460',
            'callback_url' => 'https://inte-cashier-stg.finpoints.tech/pay/union_notify/AdapterEcurrency',
            'redirect_url' => 'https://inte-cashier-stg.finpoints.tech/pay/processing',
            'whitelist_ip' => json_encode([
                '206.237.35.13',
                '206.237.52.50',
                '206.237.35.36',
                '206.237.35.27',
                '206.237.52.50',
                '103.151.210.16',
                '206.237.35.129',
                '206.237.35.154',
                '47.76.96.185',
                '8.210.179.124',
                '8.210.248.4',
                '103.151.210.37',
                '206.237.32.129',
                '8.210.179.124',
            ]),
        ]);
    }
}
