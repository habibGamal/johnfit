<?php

namespace App\Services;

use App\Models\InBodyLog;
use App\Models\Meal;
use App\Models\MealPlan;
use App\Models\RepsPreset;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutPlan;
use Illuminate\Support\Collection;

class PlanGenerationService
{
    public function __construct(
        protected PlanAssignmentService $assignmentService
    ) {
    }

    /**
     * Generate both Workout Plan and Meal Plan for the given user.
     *
     * @return array{workout_plan: WorkoutPlan, meal_plan: MealPlan, summary: array}
     */
    public function generateForUser(User $user): array
    {
        $answers = $user->assessmentAnswers()
            ->with('assessment')
            ->get()
            ->keyBy(fn($a) => $a->assessment->order ?? $a->assessment_id);

        $latestInBody = InBodyLog::where('user_id', $user->id)
            ->latestFirst()
            ->first();

        // 1. Generate Meal Plan
        $mealPlan = $this->generateMealPlan($user, $answers, $latestInBody);

        // 2. Generate Workout Plan
        $workoutPlan = $this->generateWorkoutPlan($user, $answers);

        return [
            'workout_plan' => $workoutPlan,
            'meal_plan' => $mealPlan,
            'summary' => [
                'goal' => $this->getAnswerValue($answers, 1, 'General Fitness'),
                'weight' => $latestInBody?->weight ?? 75,
                'target_calories' => $mealPlan->targets['calories'] ?? 2000,
                'workout_days' => $this->getAnswerValue($answers, 14, '3-4 Days'),
            ],
        ];
    }

    /**
     * Slot-specific macro ratio targets: [protein%, carbs%, fats%].
     */
    private const SLOT_MACRO_RATIOS = [
        'breakfast' => ['protein' => 0.20, 'carbs' => 0.50, 'fats' => 0.30],
        'lunch' => ['protein' => 0.35, 'carbs' => 0.40, 'fats' => 0.25],
        'snack' => ['protein' => 0.15, 'carbs' => 0.55, 'fats' => 0.30],
        'dinner' => ['protein' => 0.35, 'carbs' => 0.35, 'fats' => 0.30],
    ];

    /**
     * Realistic quantity ranges (in grams) by calorie density.
     */
    private const QUANTITY_RANGES = [
        'very_light' => ['min' => 150, 'max' => 400],  // cal/g < 0.5 (salads, vegetables)
        'light' => ['min' => 100, 'max' => 350],  // cal/g 0.5-1.5 (fruits, yogurt)
        'medium' => ['min' => 80, 'max' => 300],   // cal/g 1.5-3.0 (meats, grains)
        'dense' => ['min' => 20, 'max' => 150],   // cal/g 3.0-5.0 (cheese, nuts)
        'very_dense' => ['min' => 10, 'max' => 80],    // cal/g > 5.0 (oils, butters)
    ];

    /**
     * Keyword map: assessment answer keywords → meal name search terms.
     */
    private const PREFERENCE_KEYWORDS = [
        // Q11: Protein preferences
        'فراخ' => ['دجاج', 'فراخ', 'صدر دجاج', 'فخد دجاج', 'chicken'],
        'كبدة بلدي' => ['كبدة', 'كبد'],
        'لحم أحمر' => ['لحم', 'بقري', 'ضاني', 'عجل', 'بتلو', 'ستيك'],
        'سلمون' => ['سلمون', 'salmon'],
        'سمك' => ['سمك', 'بلطي', 'بوري', 'قاروص', 'fish'],
        'تونة' => ['تونة', 'تونا', 'tuna'],
        'جمبري' => ['جمبري', 'shrimp'],
        'بيض' => ['بيض', 'egg'],
        'جبنة قريش' => ['جبنة قريش', 'قريش'],
        'رومي' => ['رومي', 'ديك رومي', 'turkey'],
        'رومي مدخن' => ['رومي مدخن'],
        'زبادي يوناني' => ['زبادي يوناني', 'يوناني'],
        'زبادي عادي' => ['زبادي'],
        'جبنة فيتا' => ['فيتا', 'feta'],
        // Q12: Carb preferences
        'بطاطا' => ['بطاطا', 'sweet potato'],
        'بطاطس' => ['بطاطس', 'potato'],
        'أرز أبيض' => ['أرز أبيض', 'أرز مصري', 'رز'],
        'أرز بسمتي' => ['أرز بسمتي', 'بسمتي'],
        'عدس' => ['عدس'],
        'فاصوليا' => ['فاصوليا'],
        'فول' => ['فول'],
        'توست بني' => ['توست بني', 'توست'],
        'شوفان' => ['شوفان', 'oat'],
        'رايس كيك' => ['رايس كيك', 'rice cake'],
        'تورتيلا بني' => ['تورتيلا'],
        // Q13: Fat preferences
        'فول سوداني' => ['فول سوداني', 'سوداني'],
        'لوز' => ['لوز', 'almond'],
        'كاجو' => ['كاجو', 'cashew'],
        'زبدة فول سوداني' => ['زبدة فول سوداني', 'peanut butter'],
        'سمن بلدي' => ['سمن بلدي', 'سمن'],
        'زيت زيتون' => ['زيت زيتون', 'olive oil'],
        'زيت جوز الهند' => ['زيت جوز الهند', 'coconut oil'],
        'أفوكادو' => ['أفوكادو', 'avocado'],
    ];

    /**
     * Dietary restriction keywords to exclude from meal names.
     */
    private const RESTRICTION_KEYWORDS = [
        'لاكتوز' => ['حليب', 'لبن', 'جبن', 'زبادي', 'جبنة', 'ايس كريم', 'كريمة', 'موزاريلا', 'شيدر', 'dairy'],
        'جلوتين' => ['قمح', 'خبز', 'توست', 'مكرونة', 'كيك', 'بسكويت', 'فطيرة', 'gluten', 'wheat'],
    ];

    /**
     * Generate a customized Meal Plan with smart selection.
     */
    protected function generateMealPlan(User $user, Collection $answers, ?InBodyLog $inBody): MealPlan
    {
        $weight = $inBody?->weight ? (float) $inBody->weight : 75.0;
        $bmr = $inBody?->bmr ? (float) $inBody->bmr : (10 * $weight + 6.25 * 175 - 5 * 25 + 5);

        $goalText = $this->getAnswerValue($answers, 1, '');
        $activityText = $this->getAnswerValue($answers, 4, '');
        $mealsPerDayText = $this->getAnswerValue($answers, 7, '');
        $restrictionsText = $this->getAnswerValue($answers, 10, '');
        $proteinPrefs = $this->getAnswerArray($answers, 11);
        $carbPrefs = $this->getAnswerArray($answers, 12);
        $fatPrefs = $this->getAnswerArray($answers, 13);

        // Activity multiplier
        $activityMultiplier = 1.35;
        if (str_contains($activityText, 'مكتب')) {
            $activityMultiplier = 1.25;
        } elseif (str_contains($activityText, 'واقف')) {
            $activityMultiplier = 1.4;
        } elseif (str_contains($activityText, 'مجهود')) {
            $activityMultiplier = 1.6;
        }

        $tdee = $bmr * $activityMultiplier;

        // Goal adjustment
        if (str_contains($goalText, 'خسارة دهون + بناء عضلات')) {
            $targetCalories = $tdee - 250;
        } elseif (str_contains($goalText, 'خسارة دهون')) {
            $targetCalories = $tdee - 500;
        } elseif (str_contains($goalText, 'بناء عضلات')) {
            $targetCalories = $tdee + 350;
        } else {
            $targetCalories = $tdee;
        }

        $targetCalories = max(1300, round($targetCalories));

        // Macro calculation
        $proteinGrams = str_contains($goalText, 'عضلات') ? $weight * 2.2 : $weight * 2.0;
        $fatCalories = $targetCalories * 0.25;
        $fatGrams = $fatCalories / 9;
        $carbCalories = max(200, $targetCalories - (($proteinGrams * 4) + $fatCalories));
        $carbGrams = $carbCalories / 4;

        $targets = [
            'calories' => round($targetCalories, 1),
            'proteins' => round($proteinGrams, 1),
            'carbs' => round($carbGrams, 1),
            'fats' => round($fatGrams, 1),
        ];

        // Retrieve available meals and apply dietary restriction filtering
        $allMeals = Meal::all();
        if ($allMeals->isEmpty()) {
            throw new \RuntimeException('No meals found in the database to generate plan.');
        }

        $filteredMeals = $this->filterByDietaryRestrictions($allMeals, $restrictionsText);

        // Build user preference keyword set for scoring
        $userPreferenceTerms = $this->buildPreferenceTerms($proteinPrefs, $carbPrefs, $fatPrefs);

        // Determine meal structure
        $has4Meals = str_contains($mealsPerDayText, '4') || str_contains($mealsPerDayText, '5');
        $slotDistribution = $has4Meals ? [
            'Breakfast' => 0.25,
            'Lunch' => 0.35,
            'Snack' => 0.15,
            'Dinner' => 0.25,
        ] : [
            'Breakfast' => 0.30,
            'Lunch' => 0.45,
            'Dinner' => 0.25,
        ];

        $daysNames = ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'];
        $daysPlan = [];

        // Cross-day tracking: slotName => [mealId, mealId, ...]
        $slotHistory = [];
        // Global usage count across all days for variety
        $globalUsageCount = [];

        foreach ($daysNames as $dayName) {
            $usedMealIdsToday = [];
            $timeSlots = [];

            foreach ($slotDistribution as $mealTimeName => $ratio) {
                $slotKey = strtolower(str_replace(' ', '_', $mealTimeName));
                $targetSlotCal = $targetCalories * $ratio;

                // Slot-specific macro targets in grams
                $macroRatios = self::SLOT_MACRO_RATIOS[$slotKey] ?? self::SLOT_MACRO_RATIOS['lunch'];
                $slotTargets = [
                    'protein' => ($targetSlotCal * $macroRatios['protein']) / 4,
                    'carbs' => ($targetSlotCal * $macroRatios['carbs']) / 4,
                    'fats' => ($targetSlotCal * $macroRatios['fats']) / 9,
                ];

                // Filter meals that match this slot type
                $suitableMeals = $this->getMealsForSlotType($filteredMeals, $slotKey);

                // Exclude meals already used today
                $availableMeals = $suitableMeals->reject(
                    fn(Meal $m) => in_array($m->id, $usedMealIdsToday)
                );

                // Fallback: if too few available, relax the duplicate constraint
                if ($availableMeals->count() < 3) {
                    $availableMeals = $suitableMeals;
                }

                // Score each meal
                $scored = $availableMeals->map(function (Meal $meal) use ($targetSlotCal, $slotTargets, $userPreferenceTerms, $slotKey, $slotHistory, $globalUsageCount) {
                    $score = $this->scoreMealForSlot(
                        $meal,
                        $targetSlotCal,
                        $slotTargets,
                        $userPreferenceTerms,
                        $slotKey,
                        $slotHistory,
                        $globalUsageCount
                    );

                    return ['meal' => $meal, 'score' => $score];
                });

                // Select best meal with weighted randomization from top candidates
                $selectedMeal = $this->selectBestMeal($scored);

                // Calculate optimal quantity
                $quantityGrams = $this->calculateOptimalQuantity($selectedMeal, $targetSlotCal);

                // Track usage
                $usedMealIdsToday[] = $selectedMeal->id;
                $slotHistory[$slotKey][] = $selectedMeal->id;
                $globalUsageCount[$selectedMeal->id] = ($globalUsageCount[$selectedMeal->id] ?? 0) + 1;

                $timeSlots[] = [
                    'meal_time' => $mealTimeName,
                    'meals' => [
                        [
                            'options' => [
                                [
                                    'meal_id' => $selectedMeal->id,
                                    'quantity' => $quantityGrams,
                                ],
                            ],
                        ],
                    ],
                ];
            }

            $daysPlan[] = [
                'day' => $dayName,
                'time' => $timeSlots,
            ];
        }

        // Create MealPlan model
        $mealPlan = new MealPlan();
        $mealPlan->name = 'AI Plan - ' . $user->name . ' (' . now()->format('d M Y - H.i.s') . ')';
        $mealPlan->targets = $targets;
        $mealPlan->days = $daysPlan;
        $mealPlan->save();

        $this->assignmentService->assignPlan($user, $mealPlan->id, 'meal');

        return $mealPlan;
    }

    /**
     * Filter out meals that conflict with dietary restrictions (Q10).
     */
    private function filterByDietaryRestrictions(Collection $meals, string $restrictionsText): Collection
    {
        if (empty($restrictionsText) || str_contains($restrictionsText, 'كل حاجة')) {
            return $meals;
        }

        $excludeTerms = [];
        foreach (self::RESTRICTION_KEYWORDS as $trigger => $keywords) {
            if (str_contains($restrictionsText, $trigger)) {
                $excludeTerms = array_merge($excludeTerms, $keywords);
            }
        }

        if (empty($excludeTerms)) {
            return $meals;
        }

        return $meals->reject(function (Meal $meal) use ($excludeTerms) {
            $nameLower = mb_strtolower($meal->name);
            foreach ($excludeTerms as $term) {
                if (str_contains($nameLower, mb_strtolower($term))) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Build a flat list of search terms from user's preference answers (Q11, Q12, Q13).
     *
     * @return array<string>
     */
    private function buildPreferenceTerms(array $proteinPrefs, array $carbPrefs, array $fatPrefs): array
    {
        $terms = [];
        $allPrefs = array_merge($proteinPrefs, $carbPrefs, $fatPrefs);

        foreach ($allPrefs as $pref) {
            $cleanPref = preg_replace('/[\x{1F000}-\x{1F9FF}]/u', '', $pref);
            $cleanPref = trim($cleanPref);

            foreach (self::PREFERENCE_KEYWORDS as $answerKey => $searchTerms) {
                if (str_contains($cleanPref, $answerKey)) {
                    $terms = array_merge($terms, $searchTerms);
                }
            }
        }

        return array_unique($terms);
    }

    /**
     * Get meals whose type tag includes the given slot type.
     */
    private function getMealsForSlotType(Collection $meals, string $slotType): Collection
    {
        $suitable = $meals->filter(function (Meal $m) use ($slotType) {
            $types = $m->type; // Already an array via accessor
            $typesLower = array_map(fn($t) => strtolower(trim($t)), $types);

            return in_array($slotType, $typesLower);
        });

        // Fallback to all meals if no type match
        return $suitable->isNotEmpty() ? $suitable : $meals;
    }

    /**
     * Score a meal 0-100 for how well it fits a specific slot.
     *
     * Scoring breakdown:
     *  - Macro fitness:     0-40 points
     *  - User preferences:  0-25 points
     *  - Freshness penalty: 0 to -20 points
     *  - Calorie density:   0-15 points
     */
    private function scoreMealForSlot(
        Meal $meal,
        float $targetSlotCal,
        array $slotTargets,
        array $userPreferenceTerms,
        string $slotKey,
        array $slotHistory,
        array $globalUsageCount
    ): float {
        $score = 0;

        // 1. Macro fitness (40 points max)
        $macros = $meal->meal_macros ?? [];
        $mealProtein = $macros['proteins'] ?? 0;
        $mealCarbs = $macros['carbs'] ?? 0;
        $mealFats = $macros['fats'] ?? 0;
        $mealCal = $macros['calories'] ?? 0;

        if ($mealCal > 0) {
            // Estimate macros at a "reasonable serving" (e.g., 200g reference)
            $refGrams = 200;
            $servingProtein = $mealProtein * $refGrams;
            $servingCarbs = $mealCarbs * $refGrams;
            $servingFats = $mealFats * $refGrams;

            $proteinDiff = abs($servingProtein - $slotTargets['protein']);
            $carbsDiff = abs($servingCarbs - $slotTargets['carbs']);
            $fatsDiff = abs($servingFats - $slotTargets['fats']);

            $maxDeviation = max($slotTargets['protein'], $slotTargets['carbs'], $slotTargets['fats'], 1);
            $proteinScore = max(0, 1 - ($proteinDiff / $maxDeviation));
            $carbsScore = max(0, 1 - ($carbsDiff / $maxDeviation));
            $fatsScore = max(0, 1 - ($fatsDiff / $maxDeviation));

            $score += ($proteinScore * 16) + ($carbsScore * 12) + ($fatsScore * 12);
        }

        // 2. User preference match (25 points max)
        if (!empty($userPreferenceTerms)) {
            $nameLower = mb_strtolower($meal->name);
            $matchCount = 0;
            foreach ($userPreferenceTerms as $term) {
                if (str_contains($nameLower, mb_strtolower($term))) {
                    $matchCount++;
                }
            }
            $score += min(25, $matchCount * 12);
        }

        // 3. Calorie density appropriateness (15 points max)
        if ($mealCal > 0) {
            $densityScore = match (true) {
                $slotKey === 'breakfast' => ($mealCal >= 0.5 && $mealCal <= 3.0) ? 15 : 5,
                $slotKey === 'lunch' => ($mealCal >= 0.8 && $mealCal <= 3.5) ? 15 : 5,
                $slotKey === 'snack' => ($mealCal >= 0.3 && $mealCal <= 4.0) ? 15 : 5,
                $slotKey === 'dinner' => ($mealCal >= 0.5 && $mealCal <= 3.0) ? 15 : 5,
                default => 10,
            };
            $score += $densityScore;
        }

        // 4. Freshness penalty — penalize meals used in same slot on other days
        $timesInSlot = isset($slotHistory[$slotKey])
            ? count(array_filter($slotHistory[$slotKey], fn($id) => $id === $meal->id))
            : 0;
        $score -= $timesInSlot * 15;

        // 5. Global over-use penalty
        $globalUses = $globalUsageCount[$meal->id] ?? 0;
        $score -= $globalUses * 8;

        // 6. Filter out zero-nutrient condiments (salt, spices, etc.)
        $totalMacro = $mealProtein + $mealCarbs + $mealFats;
        if ($totalMacro < 0.005 && $mealCal < 0.05) {
            $score -= 50;
        }

        return max(0, $score);
    }

    /**
     * Select the best meal using weighted randomization from top candidates.
     */
    private function selectBestMeal(Collection $scoredMeals): Meal
    {
        $sorted = $scoredMeals->sortByDesc('score')->values();

        // Take top 5 candidates (or fewer if not enough)
        $topCandidates = $sorted->take(5)->filter(fn($item) => $item['score'] > 0);

        if ($topCandidates->isEmpty()) {
            // Absolute fallback — pick any meal
            return $sorted->first()['meal'];
        }

        // Weighted random selection: higher-scored meals have proportionally higher chance
        $totalScore = $topCandidates->sum('score');
        if ($totalScore <= 0) {
            return $topCandidates->first()['meal'];
        }

        $rand = mt_rand(1, (int) ($totalScore * 100)) / 100;
        $cumulative = 0;

        foreach ($topCandidates as $item) {
            $cumulative += $item['score'];
            if ($rand <= $cumulative) {
                return $item['meal'];
            }
        }

        return $topCandidates->last()['meal'];
    }

    /**
     * Calculate optimal quantity in grams, clamped to realistic ranges
     * based on the meal's calorie density.
     */
    private function calculateOptimalQuantity(Meal $meal, float $targetSlotCal): int
    {
        $calPerGram = $meal->calories ?: 1.5;

        // Determine density category
        $range = match (true) {
            $calPerGram < 0.5 => self::QUANTITY_RANGES['very_light'],
            $calPerGram < 1.5 => self::QUANTITY_RANGES['light'],
            $calPerGram < 3.0 => self::QUANTITY_RANGES['medium'],
            $calPerGram < 5.0 => self::QUANTITY_RANGES['dense'],
            default => self::QUANTITY_RANGES['very_dense'],
        };

        $idealGrams = $targetSlotCal / $calPerGram;

        // Round to nearest 10g for cleaner numbers
        $clamped = max($range['min'], min($range['max'], round($idealGrams / 10) * 10));

        return (int) $clamped;
    }

    /**
     * Generate a customized Workout Plan.
     */
    protected function generateWorkoutPlan(User $user, Collection $answers): WorkoutPlan
    {
        $daysText = $this->getAnswerValue($answers, 14, '');
        $locationText = $this->getAnswerValue($answers, 16, '');

        // Determine active workout days
        $daysCount = 4;
        if (str_contains($daysText, 'يوم أو اتنين')) {
            $daysCount = 2;
        } elseif (str_contains($daysText, '3 أيام')) {
            $daysCount = 3;
        } elseif (str_contains($daysText, '4 أو 5')) {
            $daysCount = 4;
        } elseif (str_contains($daysText, 'كل يوم')) {
            $daysCount = 5;
        }

        // Retrieve workouts
        $allWorkouts = Workout::all();
        if ($allWorkouts->isEmpty()) {
            throw new \RuntimeException('No workouts found in the database to generate plan.');
        }

        // Filter by location if home without equipment
        if (str_contains($locationText, 'بدون معدات')) {
            $filteredWorkouts = $allWorkouts->filter(function (Workout $w) {
                $toolsArr = is_array($w->tools) ? $w->tools : explode(',', (string) $w->tools);
                $toolsStr = strtolower(implode(' ', $toolsArr));
                return empty(trim($toolsStr)) || str_contains($toolsStr, 'bodyweight') || str_contains($toolsStr, 'none') || str_contains($toolsStr, 'dumbbell');
            });
            if ($filteredWorkouts->isNotEmpty()) {
                $allWorkouts = $filteredWorkouts;
            }
        }

        // Ensure RepsPresets exist
        $repsPreset3x10 = RepsPreset::firstOrCreate(
            ['short_name' => '3x10'],
            ['reps' => [['count' => 10], ['count' => 10], ['count' => 10]]]
        );
        $repsPreset4x12 = RepsPreset::firstOrCreate(
            ['short_name' => '4x12'],
            ['reps' => [['count' => 12], ['count' => 12], ['count' => 12], ['count' => 12]]]
        );

        $daysNames = ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'];
        $daysPlan = [];

        // Muscle splits
        $splits = [
            0 => ['Chest', 'Shoulder', 'Triceps'],
            1 => ['Lat', 'Biceps', 'Abdominals'],
            2 => ['Glutes', 'Quadriceps', 'Calf'],
            3 => ['Chest', 'Lat', 'Shoulder'],
            4 => ['Quadriceps', 'Triceps', 'Biceps'],
        ];

        $activeDaysIndices = match ($daysCount) {
            2 => [0, 3], // Day 1, Day 4
            3 => [0, 2, 4], // Day 1, Day 3, Day 5
            4 => [0, 1, 3, 4], // Day 1, Day 2, Day 4, Day 5
            default => [0, 1, 2, 3, 4], // Day 1, Day 2, Day 3, Day 4, Day 5
        };

        foreach ($daysNames as $index => $dayName) {
            if (!in_array($index, $activeDaysIndices)) {
                // Rest day
                $daysPlan[] = [
                    'day' => $dayName,
                    'workouts' => [],
                ];
                continue;
            }

            $splitMuscles = $splits[$index % count($splits)];
            $selectedWorkouts = collect();

            foreach ($splitMuscles as $muscle) {
                $muscleWorkouts = $allWorkouts->filter(function (Workout $w) use ($muscle) {
                    $musclesArr = is_array($w->muscles) ? $w->muscles : explode(',', (string) $w->muscles);
                    $musclesStr = strtolower(implode(' ', $musclesArr));
                    return str_contains($musclesStr, strtolower($muscle));
                });

                if ($muscleWorkouts->isNotEmpty()) {
                    $selectedWorkouts = $selectedWorkouts->concat($muscleWorkouts->random(min(2, $muscleWorkouts->count())));
                }
            }

            if ($selectedWorkouts->isEmpty()) {
                $selectedWorkouts = $allWorkouts->random(min(4, $allWorkouts->count()));
            }

            $workoutsList = $selectedWorkouts->unique('id')->take(6)->map(function (Workout $w) use ($repsPreset3x10, $repsPreset4x12) {
                return [
                    'workout_id' => $w->id,
                    'reps' => rand(0, 1) ? $repsPreset3x10->id : $repsPreset4x12->id,
                ];
            })->values()->toArray();

            $daysPlan[] = [
                'day' => $dayName,
                'workouts' => $workoutsList,
            ];
        }

        // Create WorkoutPlan model with unique timestamp
        $workoutPlan = new WorkoutPlan();
        $workoutPlan->name = 'AI Workout Plan - ' . $user->name . ' (' . now()->format('d M Y - H.i.s') . ')';
        $workoutPlan->days = $daysPlan; // Booted saving hook converts 'days' to JSON file_path
        $workoutPlan->save();

        $this->assignmentService->assignPlan($user, $workoutPlan->id, 'workout');

        return $workoutPlan;
    }

    private function getAnswerValue(Collection $answers, int $order, string $default = ''): string
    {
        $answerObj = $answers->get($order);
        if (!$answerObj || empty($answerObj->answer)) {
            return $default;
        }

        return is_array($answerObj->answer) ? ($answerObj->answer[0] ?? $default) : (string) $answerObj->answer;
    }

    private function getAnswerArray(Collection $answers, int $order): array
    {
        $answerObj = $answers->get($order);
        if (!$answerObj || empty($answerObj->answer)) {
            return [];
        }

        return is_array($answerObj->answer) ? $answerObj->answer : [$answerObj->answer];
    }
}
