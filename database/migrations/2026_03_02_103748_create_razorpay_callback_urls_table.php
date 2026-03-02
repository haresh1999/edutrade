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
        Schema::create('razorpay_callback_urls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id');
            $table->string('tnx_id');
            $table->string('redirect_url');
            $table->string('callback_url');
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('razorpay_users')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('razorpay_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('razorpay_callback_urls');
    }
};
