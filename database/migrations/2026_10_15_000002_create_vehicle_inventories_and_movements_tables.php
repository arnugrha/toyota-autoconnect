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
        // 1. Vehicle Inventories Table (Multi-branch stock quantity tracking)
        Schema::create('vehicle_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('reserved_quantity')->default(0);
            $table->timestamps();

            // Constraint: Satu model kendaraan hanya memiliki 1 baris stok per cabang
            $table->unique(['vehicle_id', 'branch_id']);
            $table->index(['branch_id', 'stock_quantity']);
        });

        // 2. Inventory Movements Table (Audit trail log pergerakan stok)
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_inventory_id')->constrained('vehicle_inventories')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('movement_type'); // adjustment_in, adjustment_out, initial_stock, reservation, release
            $table->integer('quantity'); // delta: positif bila bertambah, negatif bila berkurang
            $table->unsignedInteger('stock_before');
            $table->unsignedInteger('stock_after');
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['vehicle_inventory_id', 'created_at']);
            $table->index('movement_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('vehicle_inventories');
    }
};
