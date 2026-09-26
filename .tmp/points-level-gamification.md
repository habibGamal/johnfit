# Implementation Plan: Infinite Levels & Points-Based Gamification Engine

## 1. Overview & Mathematical Model

Replace the legacy 7-day rolling adherence scores (`workout_score`, `meal_score`, `inbody_score`, `total_score` on 0-100 scale) with a real-time, cumulative **Points & Infinite Levels Gamification System** across Backend, Frontend, and Achievements Journey.

### Level Equation
- **Required Points Formula:**
  $$P(L) = 5L^2 + 15L$$
  Where $P(L)$ is the cumulative points threshold required to complete Level $L$ and advance to Level $L+1$.
- **Starting Baseline:** Level 1 with 0 points ($P=0 \implies L=1$).
- **Level Progression Steps:**
  - Level 1: $0 \to 20$ pts ($P(1) = 20$, requires 20 pts to reach Level 2)
  - Level 2: $20 \to 50$ pts ($P(2) = 50$, requires 30 pts to reach Level 3)
  - Level 3: $50 \to 90$ pts ($P(3) = 90$, requires 40 pts to reach Level 4)
  - Level 4: $90 \to 140$ pts ($P(4) = 140$, requires 50 pts to reach Level 5)
  - Level $L$: step $\Delta P = 10L + 10$ points to advance.
- **Reverse Formula (Level from Points $P$):**
  Solving $5k^2 + 15k \le P$:
  $$k = \left\lfloor \frac{-15 + \sqrt{225 + 20P}}{10} \right\rfloor$$
  $$\text{Current Level } L = k + 1$$
- **In-Level Progress:**
  - $P_{\text{min}} = P(L-1) = 5(L-1)^2 + 15(L-1)$ (for $L=1$, $P_{\text{min}} = 0$)
  - $P_{\text{max}} = P(L) = 5L^2 + 15L$
  - Points in current level $= P - P_{\text{min}}$
  - Points needed for level $= P_{\text{max}} - P_{\text{min}} = 10L + 10$
  - Progress Percentage $= \frac{P - P_{\text{min}}}{P_{\text{max}} - P_{\text{min}}} \times 100\%$

---

## 2. Points Architecture & Sources

1. **Workouts:**
   - Default **3 points** per scheduled workout item.
   - Earned when `UserDailyItem` of type `workout` is marked `is_completed = true`.
   - Deducted if unchecked (`is_completed = false`).
2. **Meals:**
   - Default **5 points** per scheduled meal item.
   - Earned when `UserDailyItem` of type `meal` is marked `is_completed = true`.
   - Deducted if unchecked (`is_completed = false`).
3. **Hydration:**
   - **5 points** awarded when daily water goal is achieved (`UserDailyWaterLog.is_completed = true`, intake $\ge$ target).
   - Deducted if entries are deleted or target rises such that `is_completed` becomes `false`.
4. **Totals:**
   $$\text{total\_points} = \text{workout\_points} + \text{meal\_points} + \text{hydration\_points}$$

---

## 3. Database Changes

Migration to add indexed gamification columns to `users`:
- `workout_points` (integer, default 0)
- `meal_points` (integer, default 0)
- `hydration_points` (integer, default 0)
- `total_points` (integer, default 0)
- `level` (integer, default 1)

---

## 4. Backend Components

### 4.1 `App\Services\PointsService`
- `calculateLevel(int $totalPoints): array`
  Returns `level`, `total_points`, `level_min_points`, `level_max_points`, `points_in_level`, `points_needed_in_level`, `points_to_next_level`, `progress_percent`, `title`.
- `addWorkoutPoints(User $user, int $pts): void`
- `deductWorkoutPoints(User $user, int $pts): void`
- `addMealPoints(User $user, int $pts): void`
- `deductMealPoints(User $user, int $pts): void`
- `addHydrationPoints(User $user, int $pts): void`
- `deductHydrationPoints(User $user, int $pts): void`
- `recalculateUserPoints(User $user): array` (re-sync from database completed items and hydration logs)
- `getPointsHistory(User $user, int $weeks = 12): array` (history for the dashboard trend chart)
- `getSummary(User $user): array` (data structure consumed by Dashboard and API)

### 4.2 Hooking Completion Events
- [`DailyItemTrackingService.php`](file:///e:/johnfit/app/Services/DailyItemTrackingService.php):
  - In `toggleItemCompletion`: add or deduct points based on workout/meal item points.
  - In `saveWorkoutSets`: trigger points add/deduct when completion state flips.
  - In `saveMealConsumption`: award meal points.
  - Trigger `BadgeService::syncUnlocks($user)` on points increase.
- [`UserDailyWaterLog.php`](file:///e:/johnfit/app/Models/UserDailyWaterLog.php) & [`WaterIntakeService.php`](file:///e:/johnfit/app/Services/WaterIntakeService.php):
  - When `recalculate()` runs and `is_completed` becomes `true`, award 5 hydration points.
  - If `is_completed` becomes `false`, deduct 5 hydration points.
  - Trigger `BadgeService::syncUnlocks($user)`.

### 4.3 Achievements Journey Overhaul
- [`App\Enums\BadgeMetric`](file:///e:/johnfit/app/Enums/BadgeMetric.php):
  - Replace old score metrics (`workout`, `meal`, `inbody`, `total`) with:
    - `WorkoutPoints = 'workout_points'`
    - `MealPoints = 'meal_points'`
    - `HydrationPoints = 'hydration_points'`
    - `TotalPoints = 'total_points'`
    - `Level = 'level'`
  - Retain streak metrics (`streak_workout`, `streak_meal`, `streak_hydration`, `streak_overall`).
- [`App\Services\BadgeService`](file:///e:/johnfit/app/Services/BadgeService.php):
  - In `currentScores` / `currentMetrics`: fetch user's live points and level.
  - In `getJourney`: expose points summary and evaluate requirements against points.
- [`Database\Seeders\AchievementSeeder`](file:///e:/johnfit/database/seeders/AchievementSeeder.php):
  - Update badge thresholds with point and level targets.

---

## 5. Frontend & UI Overhaul

### 5.1 Type Definitions
- Update [`resources/js/types/fitness-score.d.ts`](file:///e:/johnfit/resources/js/types/fitness-score.d.ts):
  - Define `PointsSummaryData` (level, total_points, level_min_points, level_max_points, points_in_level, points_needed_in_level, points_to_next_level, progress_percent, components: workout, meal, hydration).
  - Define `PointsHistoryItem` (date, fullDate, total_points, points_earned, level).

### 5.2 Dashboard Widgets
- [`resources/js/Components/Dashboard/FitnessScoreWidget.tsx`](file:///e:/johnfit/resources/js/Components/Dashboard/FitnessScoreWidget.tsx):
  - Transform into a **Level & Points Card**:
    - Hero Level Badge with Level Number and Title.
    - Animated XP Progress bar to next level ($X / Y$ XP, $Z\%$).
    - 3 breakdown item pills:
      - 🏋️‍♂️ Workouts: $N$ pts
      - 🥗 Nutrition: $N$ pts
      - 💧 Hydration: $N$ pts
- [`resources/js/Components/Dashboard/FitnessScoreTrend.tsx`](file:///e:/johnfit/resources/js/Components/Dashboard/FitnessScoreTrend.tsx):
  - Transform into a Points Trend chart showing points progression.
- [`resources/js/Pages/Dashboard.tsx`](file:///e:/johnfit/resources/js/Pages/Dashboard.tsx):
  - Rename section heading to "Your Level & Points" / "Your Progression".

### 5.3 Achievements Journey Page
- [`resources/js/Pages/Achievements/Index.tsx`](file:///e:/johnfit/resources/js/Pages/Achievements/Index.tsx):
  - Replace the "Weekly Scores" card with "Points Breakdown" (Total Points, Current Level, Workout pts, Meal pts, Hydration pts).
  - Badges render with point progress bars.
