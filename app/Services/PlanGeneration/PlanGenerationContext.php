<?php

namespace App\Services\PlanGeneration;

use App\Models\InBodyLog;
use App\Models\Meal;
use App\Models\RepsPreset;
use App\Models\User;
use App\Models\Workout;
use Illuminate\Support\Collection;

class PlanGenerationContext
{
    /**
     * Dietary restriction keywords to exclude from meal names.
     */
    protected const RESTRICTION_KEYWORDS = [
        'لاكتوز' => ['حليب', 'لبن', 'جبن', 'زبادي', 'جبنة', 'ايس كريم', 'كريمة', 'موزاريلا', 'شيدر', 'dairy'],
        'جلوتين' => ['قمح', 'خبز', 'توست', 'مكرونة', 'كيك', 'بسكويت', 'فطيرة', 'gluten', 'wheat'],
    ];

    /**
     * Preference keywords map: assessment answer keyword -> meal name matching keywords.
     */
    protected const PREFERENCE_KEYWORDS = [
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
        'فول سوداني' => ['فول سوداني', 'سوداني'],
        'لوز' => ['لوز', 'almond'],
        'كاجو' => ['كاجو', 'cashew'],
        'زبدة فول سوداني' => ['زبدة فول سوداني', 'peanut butter'],
        'سمن بلدي' => ['سمن بلدي', 'سمن'],
        'زيت زيتون' => ['زيت زيتون', 'olive oil'],
        'زيت جوز الهند' => ['زيت جوز الهند', 'coconut oil'],
        'أفوكادو' => ['أفوكادو', 'avocado'],
    ];

    public function __construct(
        public readonly User $user,
        public readonly Collection $answers,
        public readonly ?InBodyLog $inBody,
        public readonly Collection $meals,
        public readonly Collection $workouts,
        public readonly Collection $repsPresets,
    ) {
    }

    /**
     * Create context instance for a user by loading necessary relations and catalogs.
     */
    public static function createForUser(User $user): self
    {
        $answers = $user->assessmentAnswers()
            ->with('assessment')
            ->get()
            ->keyBy(fn ($a) => $a->assessment->order ?? $a->assessment_id);

        $latestInBody = InBodyLog::where('user_id', $user->id)
            ->latestFirst()
            ->first();

        $allMeals = Meal::all();
        $allWorkouts = Workout::all();

        // Ensure default reps presets exist
        $repsPreset3x10 = RepsPreset::firstOrCreate(
            ['short_name' => '3x10'],
            ['reps' => [['count' => 10], ['count' => 10], ['count' => 10]]]
        );
        $repsPreset4x12 = RepsPreset::firstOrCreate(
            ['short_name' => '4x12'],
            ['reps' => [['count' => 12], ['count' => 12], ['count' => 12], ['count' => 12]]]
        );

        $presets = RepsPreset::all();
        if ($presets->isEmpty()) {
            $presets = collect([$repsPreset3x10, $repsPreset4x12]);
        }

        return new self(
            user: $user,
            answers: $answers,
            inBody: $latestInBody,
            meals: $allMeals,
            workouts: $allWorkouts,
            repsPresets: $presets,
        );
    }

    public function getAnswerValue(int $order, string $default = ''): string
    {
        $answerObj = $this->answers->get($order);
        if (! $answerObj || empty($answerObj->answer)) {
            return $default;
        }

        return is_array($answerObj->answer) ? ($answerObj->answer[0] ?? $default) : (string) $answerObj->answer;
    }

    public function getAnswerArray(int $order): array
    {
        $answerObj = $this->answers->get($order);
        if (! $answerObj || empty($answerObj->answer)) {
            return [];
        }

        return is_array($answerObj->answer) ? $answerObj->answer : [$answerObj->answer];
    }

    public function getGoal(): string
    {
        return $this->getAnswerValue(1, 'General Fitness');
    }

    public function getActivityText(): string
    {
        return $this->getAnswerValue(4, 'Sedentary');
    }

    public function getMealsPerDayText(): string
    {
        return $this->getAnswerValue(7, '3-4 meals');
    }

    public function getDietaryRestrictions(): string
    {
        return $this->getAnswerValue(10, '');
    }

    public function getProteinPreferences(): array
    {
        return $this->getAnswerArray(11);
    }

    public function getCarbPreferences(): array
    {
        return $this->getAnswerArray(12);
    }

    public function getFatPreferences(): array
    {
        return $this->getAnswerArray(13);
    }

    public function getWorkoutDaysText(): string
    {
        return $this->getAnswerValue(14, '4 أو 5 أيام');
    }

    public function getWorkoutDaysCount(): int
    {
        $daysText = $this->getWorkoutDaysText();
        if (str_contains($daysText, 'يوم أو اتنين')) {
            return 2;
        }
        if (str_contains($daysText, '3 أيام')) {
            return 3;
        }
        if (str_contains($daysText, '4 أو 5')) {
            return 4;
        }
        if (str_contains($daysText, 'كل يوم')) {
            return 5;
        }

        return 4;
    }

    public function getWorkoutDuration(): string
    {
        return $this->getAnswerValue(15, '45-60 min');
    }

    public function getWorkoutLocation(): string
    {
        return $this->getAnswerValue(16, 'Gym');
    }

    public function getWeight(): float
    {
        return (float) ($this->inBody?->weight ?? 75.0);
    }

    public function getHeight(): float
    {
        return (float) ($this->inBody?->height ?? 175.0);
    }

    public function getBmr(): float
    {
        if ($this->inBody?->bmr && $this->inBody->bmr > 500) {
            return (float) $this->inBody->bmr;
        }

        $weight = $this->getWeight();
        $height = $this->getHeight();

        // Mifflin-St Jeor formula baseline
        return (10 * $weight) + (6.25 * $height) - (5 * 26) + 5;
    }

    public function getEstimatedTargets(): array
    {
        $bmr = $this->getBmr();
        $activityText = $this->getActivityText();

        $activityMultiplier = 1.35;
        if (str_contains($activityText, 'مكتب')) {
            $activityMultiplier = 1.25;
        } elseif (str_contains($activityText, 'واقف')) {
            $activityMultiplier = 1.4;
        } elseif (str_contains($activityText, 'مجهود')) {
            $activityMultiplier = 1.6;
        }

        $tdee = $bmr * $activityMultiplier;
        $goalText = $this->getGoal();

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
        $weight = $this->getWeight();
        $proteinGrams = str_contains($goalText, 'عضلات') ? $weight * 2.2 : $weight * 2.0;
        $fatCalories = $targetCalories * 0.25;
        $fatGrams = $fatCalories / 9;
        $carbCalories = max(200, $targetCalories - (($proteinGrams * 4) + $fatCalories));
        $carbGrams = $carbCalories / 4;

        return [
            'calories' => round($targetCalories, 1),
            'proteins' => round($proteinGrams, 1),
            'carbs' => round($carbGrams, 1),
            'fats' => round($fatGrams, 1),
        ];
    }

    /**
     * Get pre-filtered and token-efficient condensed meals.
     */
    public function getCondensedMeals(): array
    {
        $restrictionsText = $this->getDietaryRestrictions();
        $excludeTerms = [];

        if (! empty($restrictionsText) && ! str_contains($restrictionsText, 'كل حاجة')) {
            foreach (self::RESTRICTION_KEYWORDS as $trigger => $keywords) {
                if (str_contains($restrictionsText, $trigger)) {
                    $excludeTerms = array_merge($excludeTerms, $keywords);
                }
            }
        }

        return $this->meals
            ->reject(function (Meal $meal) use ($excludeTerms) {
                if (empty($excludeTerms)) {
                    return false;
                }
                $nameLower = mb_strtolower($meal->name);
                foreach ($excludeTerms as $term) {
                    if (str_contains($nameLower, mb_strtolower($term))) {
                        return true;
                    }
                }

                return false;
            })
            ->map(function (Meal $m) {
                $macros = $m->meal_macros ?? [];
                $cal = (float) ($m->calories ?? 0);
                $p = (float) ($macros['proteins'] ?? 0);
                $c = (float) ($macros['carbs'] ?? 0);
                $f = (float) ($macros['fats'] ?? 0);

                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'slots' => is_array($m->type) ? $m->type : [$m->type],
                    'cal_per_g' => round($cal, 3),
                    'p_per_g' => round($p, 3),
                    'c_per_g' => round($c, 3),
                    'f_per_g' => round($f, 3),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get pre-filtered and token-efficient condensed workouts.
     */
    public function getCondensedWorkouts(): array
    {
        $locationText = $this->getWorkoutLocation();
        $workouts = $this->workouts;

        if (str_contains($locationText, 'بدون معدات')) {
            $filtered = $workouts->filter(function (Workout $w) {
                $toolsArr = is_array($w->tools) ? $w->tools : explode(',', (string) $w->tools);
                $toolsStr = strtolower(implode(' ', $toolsArr));

                return empty(trim($toolsStr))
                    || str_contains($toolsStr, 'bodyweight')
                    || str_contains($toolsStr, 'none')
                    || str_contains($toolsStr, 'dumbbell');
            });

            if ($filtered->isNotEmpty()) {
                $workouts = $filtered;
            }
        }

        return $workouts->map(function (Workout $w) {
            $musclesArr = is_array($w->muscles) ? $w->muscles : explode(',', (string) $w->muscles);
            $toolsArr = is_array($w->tools) ? $w->tools : explode(',', (string) $w->tools);

            return [
                'id' => $w->id,
                'name' => $w->name,
                'muscles' => array_values(array_filter(array_map('trim', $musclesArr))),
                'tools' => array_values(array_filter(array_map('trim', $toolsArr))),
            ];
        })->values()->toArray();
    }

    /**
     * Get available reps presets.
     */
    public function getCondensedPresets(): array
    {
        return $this->repsPresets->map(fn (RepsPreset $p) => [
            'id' => $p->id,
            'name' => $p->short_name,
        ])->values()->toArray();
    }
}
