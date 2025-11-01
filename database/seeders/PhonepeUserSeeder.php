<?php

namespace Database\Seeders;

use App\Models\PhonepeUser;
use Illuminate\Database\Seeder;

class PhonepeUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PhonepeUser::updateOrCreate([
            'email' => 'ikondubai@gmail.com',
        ], [
            'name' => 'Edutrade',
            'mobile' => '8075088769',
            'client_id' => 'edutrade',
            'client_secret' => 'e1b2b70c-3574-41ad-a33d-50f38c5a927a',
            'sandbox_client_id' => 'edutrade',
            'sandbox_client_secret' => '993a1cc7-0b63-41e3-bebf-387e3070d50f',
            'callback_url' => 'https://inte-cashier-dev.finpoints.tech/pay/union_notify/AdapterEcurrency',
            'redirect_url' => 'https://inte-cashier-dev.finpoints.tech/payResult/index.html',
            'whitelist_ip' => null,
        ]);
    }
}
