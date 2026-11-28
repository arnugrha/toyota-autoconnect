<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all vehicle_images records from .svg to .jpg
        $images = DB::table('vehicle_images')
            ->where('image_path', 'like', '%.svg')
            ->get();

        foreach ($images as $img) {
            $jpgPath = preg_replace('/\.svg$/i', '.jpg', $img->image_path);
            if (file_exists(public_path($jpgPath))) {
                DB::table('vehicle_images')
                    ->where('id', $img->id)
                    ->update(['image_path' => $jpgPath]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse required
    }
};
