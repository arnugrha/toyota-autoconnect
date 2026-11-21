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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->index();
            $table->string('variant')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price')->nullable()->index();
            $table->string('transmission')->nullable();
            $table->string('fuel_type')->nullable();
            $table->string('engine_capacity')->nullable();
            $table->unsignedTinyInteger('passenger_capacity')->nullable();
            $table->string('dimensions')->nullable();
            $table->string('status')->default('available')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
