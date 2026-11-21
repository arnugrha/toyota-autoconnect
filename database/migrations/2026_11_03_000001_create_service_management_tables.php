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
        // 1. Master Service Types / Packages
        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('category')->default('regular_maintenance'); // regular_maintenance, repair, quick_service, ac_service, electrical, inspection
            $table->text('description')->nullable();
            $table->unsignedInteger('estimated_duration_minutes')->default(60);
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Branch Service Schedules & Capacities
        Schema::create('service_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Sunday, 1=Monday ... 6=Saturday
            $table->time('opening_time')->default('08:00:00');
            $table->time('closing_time')->default('16:00:00');
            $table->unsignedInteger('slot_duration_minutes')->default(60);
            $table->unsignedInteger('max_slot_capacity')->default(2);
            $table->unsignedInteger('max_daily_capacity')->default(12);
            $table->boolean('is_operational')->default(true);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['branch_id', 'day_of_week']);
        });

        // 3. Service Bookings
        Schema::create('service_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('vehicle_name');
            $table->string('license_plate');
            $table->unsignedInteger('current_mileage')->nullable();
            $table->foreignId('service_type_id')->constrained('service_types')->restrictOnDelete();
            $table->date('booking_date');
            $table->string('booking_time'); // e.g. "09:00"
            $table->text('customer_notes')->nullable();
            $table->string('status')->default('pending'); // pending, confirmed, rescheduled, cancelled, rejected, completed
            $table->text('reschedule_reason')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'booking_date', 'booking_time']);
            $table->index(['user_id', 'status']);
        });

        // 4. Service Orders (Workshop Work Orders)
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('service_booking_id')->nullable()->constrained('service_bookings')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('vehicle_name');
            $table->string('license_plate');
            $table->unsignedInteger('current_mileage')->nullable();
            $table->foreignId('service_type_id')->constrained('service_types')->restrictOnDelete();
            $table->foreignId('service_advisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('technician_name')->nullable();
            $table->string('status')->default('scheduled'); 
            // scheduled, confirmed, vehicle_received, inspection, in_progress, waiting_parts, completed, delivered, cancelled
            $table->text('customer_complaints')->nullable();
            $table->text('inspection_result')->nullable();
            $table->text('repair_actions')->nullable();
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->decimal('final_cost', 12, 2)->nullable();
            $table->text('additional_notes')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['user_id', 'status']);
        });

        // 5. Service Order Items (Spare parts & Labor fees)
        Schema::create('service_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained('service_orders')->cascadeOnDelete();
            $table->string('item_type')->default('part'); // part, service
            $table->string('name');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
        });

        // 6. Service Order Activity Logs
        Schema::create('service_order_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained('service_orders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // status_updated, inspection_recorded, item_added, etc.
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 7. After-Sales Service Reminders
        Schema::create('service_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('vehicle_name');
            $table->string('license_plate');
            $table->date('last_service_date')->nullable();
            $table->unsignedInteger('last_mileage')->nullable();
            $table->date('next_service_date');
            $table->unsignedInteger('next_service_mileage')->nullable();
            $table->string('recommended_service');
            $table->string('status')->default('pending'); // pending, sent, booked, dismissed
            $table->timestamp('reminder_sent_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['next_service_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_reminders');
        Schema::dropIfExists('service_order_logs');
        Schema::dropIfExists('service_order_items');
        Schema::dropIfExists('service_orders');
        Schema::dropIfExists('service_bookings');
        Schema::dropIfExists('service_schedules');
        Schema::dropIfExists('service_types');
    }
};

