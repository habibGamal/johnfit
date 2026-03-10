<?php

namespace Database\Seeders;

use App\Models\Quote;
use Illuminate\Database\Seeder;

class QuoteSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = base_path('qoutes.json');
        $quotes = json_decode(file_get_contents($jsonPath), true);

        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Quote::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        Quote::insert(
            array_map(fn (array $item) => [
                'id' => $item['id'],
                'quote' => $item['quote'],
                'created_at' => now(),
                'updated_at' => now(),
            ], $quotes)
        );
    }
}
