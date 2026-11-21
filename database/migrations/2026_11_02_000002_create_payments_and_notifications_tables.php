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
        // 1. Payments Table
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('vehicle_booking_id')->constrained('vehicle_bookings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('gateway')->default('midtrans');
            $table->string('gateway_transaction_id')->nullable()->index();
            $table->string('order_id')->unique();
            $table->unsignedBigInteger('amount');
            $table->string('payment_method')->nullable();
            $table->string('status')->default('pending')->index(); // pending, paid, failed, expired, cancelled, refunded
            $table->string('snap_token')->nullable();
            $table->string('snap_redirect_url')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->unsignedBigInteger('refund_amount')->default(0);
            $table->text('refund_reason')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['vehicle_booking_id', 'status']);
        });

        // 2. Payment Notifications Table (Audit Trail & Idempotency)
        Schema::create('payment_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('order_id')->index();
            $table->string('notification_id')->unique(); // ID from gateway or sha256 hash for idempotency
            $table->json('notification_payload');
            $table->string('processing_status')->default('processed'); // processed, duplicate_ignored, failed, signature_invalid
            $table->timestamp('received_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_notifications');
        Schema::dropIfExists('payments');
    }
};

