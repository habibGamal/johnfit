<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('next_quote_id')->nullable()->constrained('quotes')->nullOnDelete();
            $table->foreignId('last_sent_quote_id')->nullable()->constrained('quotes')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_settings');
    }
};
