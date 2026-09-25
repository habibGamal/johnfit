<?php

use App\Models\Meal;
use App\Models\UserDailyItem;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $meals = Meal::all()->keyBy('id');

        UserDailyItem::where('type', 'meal')->chunkById(100, function ($items) use ($meals) {
            foreach ($items as $item) {
                $targetDetails = $item->target_details;
                if (empty($targetDetails)) {
                    continue;
                }

                $dirty = false;

                if (!empty($targetDetails['options']) && is_array($targetDetails['options'])) {
                    $newOptions = [];
                    foreach ($targetDetails['options'] as $opt) {
                        $meal = $meals->get($opt['meal_id'] ?? null);
                        $qty = (float) ($opt['quantity'] ?? 100);
                        if ($meal) {
                            $opt['quantity'] = $qty;
                            $opt['calories'] = round(($meal->calories ?? 0) * $qty, 1);
                            $opt['protein'] = round(($meal->protein ?? 0) * $qty, 1);
                            $opt['carbs'] = round(($meal->carbs ?? 0) * $qty, 1);
                            $opt['fat'] = round(($meal->fat ?? 0) * $qty, 1);
                            $dirty = true;
                        }
                        $newOptions[] = $opt;
                    }
                    $targetDetails['options'] = $newOptions;
                }

                if (!empty($targetDetails['primary_option']) && is_array($targetDetails['primary_option'])) {
                    $meal = $meals->get($targetDetails['primary_option']['meal_id'] ?? null);
                    $qty = (float) ($targetDetails['primary_option']['quantity'] ?? 100);
                    if ($meal) {
                        $targetDetails['primary_option']['quantity'] = $qty;
                        $targetDetails['primary_option']['calories'] = round(($meal->calories ?? 0) * $qty, 1);
                        $targetDetails['primary_option']['protein'] = round(($meal->protein ?? 0) * $qty, 1);
                        $targetDetails['primary_option']['carbs'] = round(($meal->carbs ?? 0) * $qty, 1);
                        $targetDetails['primary_option']['fat'] = round(($meal->fat ?? 0) * $qty, 1);
                        $dirty = true;
                    }
                }

                if ($dirty) {
                    $item->target_details = $targetDetails;
                    $item->save();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-time data fix; no rollback needed.
    }
};
