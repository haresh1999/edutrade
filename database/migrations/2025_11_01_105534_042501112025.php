<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('phonepe_users', function (Blueprint $table) {
            $table->json('whitelist_ip')->change()->nullable();
        });

        Schema::table('sabpaisa_users', function (Blueprint $table) {
            $table->json('whitelist_ip')->change()->nullable();
        });

        Schema::table('razorpay_users', function (Blueprint $table) {
            $table->json('whitelist_ip')->change()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phonepe_users', function (Blueprint $table) {
            //
        });
    }
};
