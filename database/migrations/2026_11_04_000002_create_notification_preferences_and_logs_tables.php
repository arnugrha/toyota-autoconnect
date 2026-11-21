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
        if (!Schema::hasTable('notification_preferences')) {
            Schema::create('notification_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->boolean('in_app')->default(true);
                $table->boolean('email')->default(true);
                $table->boolean('whatsapp')->default(false);
                $table->boolean('service_reminders')->default(true);
                $table->boolean('transactional')->default(true);
                $table->boolean('promotional')->default(false);
                $table->boolean('whatsapp_consent')->default(false);
                $table->timestamp('whatsapp_consent_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notification_logs')) {
            Schema::create('notification_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->string('notification_type');
                $table->string('category')->index(); // vehicle_booking, payment, test_drive, sales_offer, service, customer_service, account, announcement
                $table->string('channel')->index(); // in_app, mail, whatsapp
                $table->string('recipient');
                $table->string('title');
                $table->text('content')->nullable();
                $table->string('status')->default('pending')->index(); // pending, queued, sent, delivered, read, failed
                $table->string('provider_message_id')->nullable()->index();
                $table->text('failure_reason')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_preferences');
    }
};

