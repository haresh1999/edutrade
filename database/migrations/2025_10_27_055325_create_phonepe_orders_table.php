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
        Schema::create('phonepe_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('order_id');
            $table->uuid('tnx_id')->unique();
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('payer_name');
            $table->string('payer_email');
            $table->string('payer_mobile');
            $table->json('request_response')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->json('refund_response')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['user_id', 'order_id']);
            $table->foreign('user_id')->references('id')->on('phonepe_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phonepe_orders');
    }
};
