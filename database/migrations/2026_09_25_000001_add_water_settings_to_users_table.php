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
        Schema::table('users', function (Blueprint $table) {
            $table->string('water_target_mode', 30)->default('auto')->after('role'); // auto, fixed, custom_multiplier
            $table->unsignedInteger('admin_water_target_ml')->nullable()->after('water_target_mode');
            $table->decimal('water_multiplier_per_kg', 4, 1)->nullable()->after('admin_water_target_ml');
            $table->text('admin_water_notes')->nullable()->after('water_multiplier_per_kg');
            $table->boolean('allow_user_water_override')->default(true)->after('admin_water_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'water_target_mode',
                'admin_water_target_ml',
                'water_multiplier_per_kg',
                'admin_water_notes',
                'allow_user_water_override',
            ]);
        });
    }
};
