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
        Schema::create('meal_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meal_plan_id')->constrained()->cascadeOnDelete();
            $table->string('day');
            $table->unsignedBigInteger('meal_id');
            $table->string('meal_time')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'meal_plan_id', 'day', 'meal_id', 'meal_time'], 'meal_completions_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_completions');
    }
};
