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
        // 1. Sales Opportunities Table
        Schema::create('sales_opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Customer
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // Sales Executive
            $table->string('stage')->default('prospecting'); // prospecting, qualification, presentation, test_drive, negotiation, booking, closed_won, closed_lost
            $table->unsignedBigInteger('estimated_value')->default(0);
            $table->text('notes')->nullable();
            $table->text('lost_reason')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'stage']);
            $table->index(['branch_id', 'stage']);
            $table->index(['assigned_to', 'stage']);
            $table->index('stage');
        });

        // 2. Sales Activities Table (Audit log for opportunities)
        Schema::create('sales_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_opportunity_id')->constrained('sales_opportunities')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type'); // stage_changed, note_added, offer_created, booking_created, etc.
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['sales_opportunity_id', 'created_at']);
            $table->index('type');
        });

        // 3. Sales Offers Table
        Schema::create('sales_offers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('sales_opportunity_id')->constrained('sales_opportunities')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->unsignedBigInteger('vehicle_price'); // Snapshot of vehicle price at offer creation
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('additional_cost')->default(0);
            $table->unsignedBigInteger('total_amount');
            $table->date('valid_until');
            $table->string('status')->default('draft'); // draft, sent, accepted, rejected, expired
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['sales_opportunity_id']);
            $table->index(['customer_id', 'status']);
            $table->index('status');
        });

        // 4. Vehicle Bookings Table
        Schema::create('vehicle_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('sales_opportunity_id')->constrained('sales_opportunities')->cascadeOnDelete();
            $table->foreignId('sales_offer_id')->nullable()->constrained('sales_offers')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('inventory_id')->nullable()->constrained('vehicle_inventories')->nullOnDelete();
            $table->unsignedBigInteger('vehicle_price');
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('booking_fee')->default(0);
            $table->date('booking_date');
            $table->dateTime('expires_at')->nullable();
            $table->string('status')->default('pending'); // pending, confirmed, cancelled, expired, completed
            $table->string('payment_status')->default('unpaid'); // unpaid, verified, refunded
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index('status');
            $table->index('sales_opportunity_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_bookings');
        Schema::dropIfExists('sales_offers');
        Schema::dropIfExists('sales_activities');
        Schema::dropIfExists('sales_opportunities');
    }
};

