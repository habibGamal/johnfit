<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE badge_requirements MODIFY COLUMN metric VARCHAR(50) NOT NULL');
        DB::statement('ALTER TABLE badge_requirements MODIFY COLUMN threshold DECIMAL(10, 2) NOT NULL DEFAULT 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed as we don't support legacy score constraints
    }
};
