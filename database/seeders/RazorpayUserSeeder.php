<?php

namespace Database\Seeders;

use App\Models\RazorpayUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RazorpayUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RazorpayUser::updateOrCreate([
            'email' => 'ikondubai@gmail.com',
        ], [
            'name' => 'Edutrade',
            'mobile' => '8075088769',
            'client_id' => 'edutrade',
            'client_secret' => '3cf6119c-18c9-411e-94b8-aa521588ec9d',
            'sandbox_client_id' => 'edutrade',
            'sandbox_client_secret' => 'e6395a8c-1566-496a-9623-d8ee4529d1a9',
            'callback_url' => 'http://edutrade.in/payment/success',
            'notify_url' => 'http://edutrade.in/payment/failed',
        ]);
    }
}
