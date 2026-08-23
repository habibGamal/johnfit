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
        Schema::create('user_plan_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('plan_id')->index();
            $table->string('plan_type', 50)->default('unified'); // 'unified', 'workout', 'meal'
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamps();

            $table->index(['user_id', 'start_date', 'end_date', 'status']);
        });

        Schema::create('user_daily_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workout_assignment_id')->nullable()->constrained('user_plan_assignments')->nullOnDelete();
            $table->foreignId('meal_assignment_id')->nullable()->constrained('user_plan_assignments')->nullOnDelete();
            $table->date('date');
            $table->unsignedInteger('target_score')->default(0);
            $table->unsignedInteger('earned_score')->default(0);
            $table->boolean('is_locked')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'date']);
            $table->index(['user_id', 'date', 'is_locked']);
        });

        Schema::create('user_daily_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_schedule_id')->constrained('user_daily_schedules')->cascadeOnDelete();
            $table->enum('type', ['workout', 'meal'])->index();
            $table->string('item_name');
            $table->unsignedBigInteger('reference_id')->nullable()->index();
            $table->json('target_details');
            $table->unsignedInteger('points')->default(1);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->json('execution_payload')->nullable();
            $table->enum('status', ['active', 'voided', 'edited'])->default('active');
            $table->unsignedInteger('order_index')->default(0);
            $table->timestamps();

            $table->index(['daily_schedule_id', 'type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_daily_items');
        Schema::dropIfExists('user_daily_schedules');
        Schema::dropIfExists('user_plan_assignments');
    }
};
