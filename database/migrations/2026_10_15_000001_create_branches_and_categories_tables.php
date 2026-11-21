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
        // 1. Vehicle Categories Table
        Schema::create('vehicle_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 2. Dealer Branches Table
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('address');
            $table->string('city')->index();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // 3. Add category_id and publication_status to existing vehicles table safely
        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('category')->constrained('vehicle_categories')->nullOnDelete();
            $table->string('publication_status')->default('published')->after('status')->index(); // draft, published, archived
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'publication_status']);
        });

        Schema::dropIfExists('branches');
        Schema::dropIfExists('vehicle_categories');
    }
};
