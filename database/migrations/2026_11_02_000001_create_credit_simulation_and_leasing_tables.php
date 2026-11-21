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
        // 1. Leasing Providers Table
        Schema::create('leasing_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('base_interest_rate', 5, 2)->default(6.00);
            $table->decimal('min_down_payment_percentage', 5, 2)->default(20.00);
            $table->unsignedInteger('max_tenor_months')->default(60);
            $table->string('logo_url')->nullable();
            $table->string('endpoint_url')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 2. Credit Simulations Table
        Schema::create('credit_simulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('leasing_provider_id')->nullable()->constrained('leasing_providers')->nullOnDelete();
            $table->unsignedBigInteger('vehicle_price');
            $table->unsignedBigInteger('down_payment');
            $table->decimal('down_payment_percentage', 5, 2);
            $table->unsignedBigInteger('financing_amount');
            $table->decimal('interest_rate', 5, 2);
            $table->unsignedInteger('tenor_months');
            $table->unsignedBigInteger('total_interest');
            $table->unsignedBigInteger('monthly_installment');
            $table->string('calculation_method')->default('flat');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'vehicle_id']);
        });

        // 3. Leasing Applications Table
        Schema::create('leasing_applications', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('credit_simulation_id')->nullable()->constrained('credit_simulations')->nullOnDelete();
            $table->foreignId('leasing_provider_id')->constrained('leasing_providers')->cascadeOnDelete();
            $table->foreignId('sales_opportunity_id')->nullable()->constrained('sales_opportunities')->nullOnDelete();
            $table->string('provider_reference')->nullable()->index();
            $table->string('status')->default('draft')->index(); // draft, submitted, processing, approved, rejected, cancelled, need_revision
            $table->unsignedBigInteger('vehicle_price');
            $table->unsignedBigInteger('down_payment');
            $table->unsignedInteger('tenor_months');
            $table->unsignedBigInteger('monthly_installment');
            $table->json('applicant_data')->nullable();
            $table->text('notes')->nullable();
            $table->text('revision_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('response_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['leasing_provider_id', 'status']);
        });

        // 4. Leasing Application Logs Table
        Schema::create('leasing_application_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leasing_application_id')->constrained('leasing_applications')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type')->index();
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leasing_application_logs');
        Schema::dropIfExists('leasing_applications');
        Schema::dropIfExists('credit_simulations');
        Schema::dropIfExists('leasing_providers');
    }
};

