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
        // 1. Master Spare Parts (Toyota Genuine Parts)
        Schema::create('spare_parts', function (Blueprint $table) {
            $table->id();
            $table->string('part_number')->unique();
            $table->string('name');
            $table->string('category')->default('other');
            $table->string('unit')->default('Pcs');
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('min_stock')->default(5);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active']);
            $table->index('stock_quantity');
        });

        // 2. Spare Part Procurements (PO / Restock Header)
        Schema::create('spare_part_procurements', function (Blueprint $table) {
            $table->id();
            $table->string('procurement_number')->unique();
            $table->date('procurement_date');
            $table->string('supplier_name');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('received'); // received, cancelled
            $table->decimal('total_cost', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['procurement_date', 'status']);
        });

        // 3. Spare Part Procurement Items
        Schema::create('spare_part_procurement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spare_part_procurement_id')->constrained('spare_part_procurements')->cascadeOnDelete();
            $table->foreignId('spare_part_id')->constrained('spare_parts')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->timestamps();
        });

        // 4. Spare Part Stock Movements (Inventory Audit Trail)
        Schema::create('spare_part_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spare_part_id')->constrained('spare_parts')->cascadeOnDelete();
            $table->string('type'); // in, out, adjustment, return
            $table->integer('quantity');
            $table->unsignedInteger('stock_before')->default(0);
            $table->unsignedInteger('stock_after')->default(0);
            $table->string('reference_type')->nullable(); // procurement, service_order, manual
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['spare_part_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });

        // 5. Enhance Service Orders for Invoice & Separated Costs
        Schema::table('service_orders', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->unique()->after('order_number');
            $table->decimal('labor_cost', 12, 2)->default(0)->after('estimated_cost');
            $table->decimal('parts_cost', 12, 2)->default(0)->after('labor_cost');
            $table->string('payment_status')->default('unpaid')->after('final_cost'); // unpaid, paid
            $table->timestamp('paid_at')->nullable()->after('payment_status');
            $table->timestamp('invoice_issued_at')->nullable()->after('delivered_at');
        });

        // 6. Enhance Service Order Items for Spare Part / Service Type FK & Customer Approval
        Schema::table('service_order_items', function (Blueprint $table) {
            $table->foreignId('spare_part_id')->nullable()->after('item_type')->constrained('spare_parts')->nullOnDelete();
            $table->foreignId('service_type_id')->nullable()->after('spare_part_id')->constrained('service_types')->nullOnDelete();
            $table->string('approval_status')->default('approved')->after('subtotal'); // pending_approval, approved, rejected
            $table->timestamp('approved_at')->nullable()->after('approval_status');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->text('customer_response_note')->nullable()->after('rejected_at');
            $table->boolean('stock_deducted')->default(false)->after('customer_response_note');

            $table->index(['service_order_id', 'approval_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_order_items', function (Blueprint $table) {
            $table->dropIndex(['service_order_id', 'approval_status']);
            $table->dropConstrainedForeignId('spare_part_id');
            $table->dropConstrainedForeignId('service_type_id');
            $table->dropColumn([
                'approval_status',
                'approved_at',
                'rejected_at',
                'customer_response_note',
                'stock_deducted',
            ]);
        });

        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropUnique(['invoice_number']);
            $table->dropColumn([
                'invoice_number',
                'labor_cost',
                'parts_cost',
                'payment_status',
                'paid_at',
                'invoice_issued_at',
            ]);
        });

        Schema::dropIfExists('spare_part_stock_movements');
        Schema::dropIfExists('spare_part_procurement_items');
        Schema::dropIfExists('spare_part_procurements');
        Schema::dropIfExists('spare_parts');
    }
};
