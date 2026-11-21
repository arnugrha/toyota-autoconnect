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
        // 1. System Settings Table
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->index(); // general, business, booking, notification, payment
            $table->string('key', 100)->unique();
            $table->longText('value')->nullable();
            $table->string('type', 30)->default('string'); // string, integer, boolean, decimal, json, image, text
            $table->string('description')->nullable();
            $table->boolean('is_public')->default(false)->index();
            $table->timestamps();
        });

        // 2. Lead Sources Master Data Table
        Schema::create('lead_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 3. Payment Methods Master Data Table
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50)->unique(); // bca_va, mandiri_bill, bni_va, bri_va, gopay, credit_card, cash_dealer
            $table->string('category', 50)->default('virtual_account'); // virtual_account, e_wallet, card, offline
            $table->text('instructions')->nullable();
            $table->string('logo_url')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 4. Audit Trail Logs Table (Immutable append-only)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name', 150)->nullable();
            $table->string('user_role', 100)->nullable();
            $table->string('action', 50)->index(); // login, login_failed, logout, created, updated, deleted, status_changed, settings_updated
            $table->string('module', 50)->index(); // auth, system_settings, branches, categories, vehicles, service_types, lead_sources, payment_methods, bookings, payments, service_orders, crm
            $table->string('auditable_type', 150)->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['module', 'action']);
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('lead_sources');
        Schema::dropIfExists('settings');
    }
};

