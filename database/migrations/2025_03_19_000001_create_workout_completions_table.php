<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workout_plan_id')->constrained()->cascadeOnDelete();
            $table->string('day');
            $table->unsignedBigInteger('workout_id');
            $table->boolean('completed')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'workout_plan_id', 'day', 'workout_id'],'workout_completions_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_completions');
    }
};
