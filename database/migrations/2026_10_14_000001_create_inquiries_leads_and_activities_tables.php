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
        // 1. Inquiries Table
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->string('status')->default('new'); // new, in_progress, answered, closed
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['vehicle_id']);
        });

        // 2. Leads Table
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_number')->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('inquiry_id')->nullable()->constrained('inquiries')->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('new'); // new, contacted, interested, test_drive, negotiation, booking, deal, lost
            $table->string('source')->default('website_inquiry'); // website_inquiry, walk_in, referral, phone
            $table->text('notes')->nullable();
            $table->dateTime('next_follow_up_at')->nullable();
            $table->dateTime('last_activity_at')->useCurrent();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['vehicle_id']);
            $table->index(['status', 'last_activity_at']);
        });

        // 3. Lead Activities Table (Append-only CRM log)
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('activity_type'); // created, status_changed, assigned, follow_up_note, contacted
            $table->text('description');
            $table->string('previous_status')->nullable();
            $table->string('new_status')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['lead_id', 'created_at']);
            $table->index(['activity_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('inquiries');
    }
};
