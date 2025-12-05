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
        Schema::create('paytm_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('mobile')->unique();
            $table->string('client_id', 100)->unique();
            $table->string('client_secret')->unique();
            $table->string('sandbox_client_id', 100)->unique();
            $table->string('sandbox_client_secret')->unique();
            $table->string('callback_url');
            $table->string('redirect_url');
            $table->json('whitelist_ip')->nullable();
            $table->string('refresh_token')->nullable();
            $table->string('notify_url')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paytm_users');
    }
};
