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
        Schema::create('inbody_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Core InBody Measurements with precise decimal types
            $table->decimal('weight', 5, 2);                    // Weight in kg (max 999.99)
            $table->decimal('smm', 5, 2);                       // Skeletal Muscle Mass in kg
            $table->decimal('pbf', 5, 2);                       // Percent Body Fat (0-100%)
            $table->decimal('bmi', 4, 1);                       // Body Mass Index
            $table->decimal('bmr', 6, 1);                       // Basal Metabolic Rate (kcal)

            // Optional extended measurements for comprehensive tracking
            $table->decimal('body_water', 5, 2)->nullable();    // Total Body Water in liters
            $table->decimal('lean_body_mass', 5, 2)->nullable(); // Lean Body Mass in kg
            $table->decimal('visceral_fat', 4, 1)->nullable();  // Visceral Fat Level (1-60)
            $table->decimal('waist_hip_ratio', 4, 3)->nullable(); // Waist-Hip Ratio

            // Multi-tenant support
            $table->unsignedBigInteger('tenant_id')->nullable()->index();

            // Measurement date (separate from created_at for backdated entries)
            $table->date('measured_at');

            // Optional notes
            $table->text('notes')->nullable();

            $table->timestamps();

            // Composite index for efficient querying
            $table->index(['user_id', 'measured_at']);
            $table->index(['tenant_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbody_logs');
    }
};
