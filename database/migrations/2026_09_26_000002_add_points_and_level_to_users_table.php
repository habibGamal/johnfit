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
            $table->unsignedInteger('workout_points')->default(0)->after('email');
            $table->unsignedInteger('meal_points')->default(0)->after('workout_points');
            $table->unsignedInteger('hydration_points')->default(0)->after('meal_points');
            $table->unsignedInteger('total_points')->default(0)->after('hydration_points');
            $table->unsignedInteger('level')->default(1)->after('total_points');

            $table->index(['total_points']);
            $table->index(['level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['total_points']);
            $table->dropIndex(['level']);

            $table->dropColumn([
                'workout_points',
                'meal_points',
                'hydration_points',
                'total_points',
                'level',
            ]);
        });
    }
};
