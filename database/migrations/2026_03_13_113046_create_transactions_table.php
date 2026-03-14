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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->uuid('reference_id')->unique();
            $table->string('order_id');
            $table->string('payer_name');
            $table->string('payer_email');
            $table->string('payer_mobile');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            $table->enum('gateway', ['phonepe', 'razorpay', 'cashfree', 'payu', 'easebuzz', 'paytm', 'zaaakapay']);
            $table->enum('env', ['production', 'sandbox']);
            $table->decimal('amount', 10, 2);
            $table->json('payment_response')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->json('refund_response')->nullable();
            $table->string('redirect_url');
            $table->string('callback_url');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'order_id']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
