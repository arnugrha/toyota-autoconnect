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
        Schema::table('branches', function (Blueprint $table) {
            $table->string('opening_time')->default('08:30')->after('email');
            $table->string('closing_time')->default('17:00')->after('opening_time');
            $table->unsignedSmallInteger('slot_duration_minutes')->default(60)->after('closing_time');
            $table->unsignedSmallInteger('slot_capacity')->default(2)->after('slot_duration_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['opening_time', 'closing_time', 'slot_duration_minutes', 'slot_capacity']);
        });
    }
};

