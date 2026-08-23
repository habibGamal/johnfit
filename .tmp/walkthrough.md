# Plan Progress Management Architecture - Walkthrough

We have completely refactored the workout and meal plan progress tracking system to implement the Materialized Daily Schedule & Points-based Scoring architecture specified in [plan-progress-architecture.md](file:///e:/johnfit/plan-progress-architecture.md).

---

## Key Changes & Architecture

### 1. Database & Schema
- **Purged Legacy Models & Tables**: Deleted `workout_completions`, `meal_completions`, `workout_set_completions`, `user_workout_plan`, `user_meal_plan`.
- **Created Migration** [2026_08_21_000001_create_plan_progress_management_tables.php](file:///e:/johnfit/database/migrations/2026_08_21_000001_create_plan_progress_management_tables.php):
  - `user_plan_assignments`: High-level timeline linking a user to a plan template ID with `start_date`, `end_date`, and `status`.
  - `user_daily_schedules`: Exactly 1 record per user per day storing `target_score`, `earned_score`, `is_locked`, `notes`.
  - `user_daily_items`: Materialized tasks for a date (`'workout'` or `'meal'`) with JSON `target_details`, JSON `execution_payload`, `points`, `is_completed`, `completed_at`, and `status`.

### 2. Eloquent Models & Relationships
- [UserPlanAssignment.php](file:///e:/johnfit/app/Models/UserPlanAssignment.php)
- [UserDailySchedule.php](file:///e:/johnfit/app/Models/UserDailySchedule.php) with dynamic `recalculateScores()` and adherence percentage accessor.
- [UserDailyItem.php](file:///e:/johnfit/app/Models/UserDailyItem.php) with casts for `target_details` and `execution_payload`.
- [User.php](file:///e:/johnfit/app/Models/User.php) with `planAssignments()`, `dailySchedules()`, and `todaySchedule()` relationships.

### 3. Backend Services & Scheduling
- [DailyScheduleService.php](file:///e:/johnfit/app/Services/DailyScheduleService.php): Schedule materialization from plan templates, date queries, point scoring, weekly adherence, and streak calculation.
- [DailyItemTrackingService.php](file:///e:/johnfit/app/Services/DailyItemTrackingService.php): Item toggling, set logging (weights/reps), meal consumption logging (options/quantities), and dynamic score recalculations.
- [PlanAssignmentService.php](file:///e:/johnfit/app/Services/PlanAssignmentService.php): Plan template assignment and window pre-materialization.
- [PlanGenerationService.php](file:///e:/johnfit/app/Services/PlanGenerationService.php): Auto-plan generation adapted to assign plans via `PlanAssignmentService`.
- [FitnessScoreService.php](file:///e:/johnfit/app/Services/FitnessScoreService.php) & [ProgressionService.php](file:///e:/johnfit/app/Services/ProgressionService.php): Adapted to calculate volume, 1RM, muscle heatmaps, and adherence metrics from `UserDailyItem`.
- [LockPastSchedulesCommand.php](file:///e:/johnfit/app/Console/Commands/LockPastSchedulesCommand.php): Console command to lock past days at midnight (registered in [routes/console.php](file:///e:/johnfit/routes/console.php)).

### 4. Controller & Routes
- [ScheduleController.php](file:///e:/johnfit/app/Http/Controllers/ScheduleController.php):
  - `index`: Loads daily schedule, weekly adherence, and current streak for any selected date.
  - `toggleItem`: Fast completion toggle and score update.
  - `saveWorkoutSets`: Saves weight and reps per set.
  - `saveMealConsumption`: Saves consumed option and quantity.
- [routes/web.php](file:///e:/johnfit/routes/web.php): Replaced legacy `/workout-plans` and `/meal-plans` routes with `/schedule` endpoints.

### 5. Frontend UI / UX
- **TypeScript Types** ([resources/js/types/index.d.ts](file:///e:/johnfit/resources/js/types/index.d.ts)): Defined types for schedules, items, execution payloads, and adherence.
- **Unified Schedule View** ([resources/js/Pages/Schedule/Index.tsx](file:///e:/johnfit/resources/js/Pages/Schedule/Index.tsx)):
  - [DateNavigator.tsx](file:///e:/johnfit/resources/js/Pages/Schedule/Components/DateNavigator.tsx): Interactive week strip with completion icons and score percentages.
  - [ScheduleScoreBanner.tsx](file:///e:/johnfit/resources/js/Pages/Schedule/Components/ScheduleScoreBanner.tsx): Points progress banner, adherence percentage, streak counter, and lock badges.
  - [WorkoutItemCard.tsx](file:///e:/johnfit/resources/js/Pages/Schedule/Components/WorkoutItemCard.tsx): Exercise card with sets summary, muscle tags, quick check toggle, and "Log Sets" modal trigger.
  - [MealItemCard.tsx](file:///e:/johnfit/resources/js/Pages/Schedule/Components/MealItemCard.tsx): Meal card with macro pills (cal, protein, carbs, fat), quick check toggle, and "Log Portions" modal trigger.
  - [WorkoutSetModal.tsx](file:///e:/johnfit/resources/js/Pages/Schedule/Components/WorkoutSetModal.tsx): Interactive modal to enter weight (kg) and reps for each set.
  - [MealLoggerModal.tsx](file:///e:/johnfit/resources/js/Pages/Schedule/Components/MealLoggerModal.tsx): Interactive modal to pick meal options and specify consumed portion size.
- **Updated Navigation & Dashboards**:
  - [BottomNavigation.tsx](file:///e:/johnfit/resources/js/Components/BottomNavigation.tsx): Updated tabs to point to `Schedule`.
  - [Dashboard.tsx](file:///e:/johnfit/resources/js/Pages/Dashboard.tsx): Updated cards and quick actions to point to `Schedule`.

---

## Verification & Test Results

### 1. Automated Backend Test Suite
```bash
php artisan test --filter="DailyScheduleTest|ProgressionAnalyticsTest"
```
**Result:**
- 21 tests passed (78 assertions).
- All schedule creation, points calculation, item toggling, workout sets logging, meal quantity recording, past schedule locking, volume calculation, 1RM trend, and progression score logic verified.

### 2. Frontend Typecheck & Build
```bash
npx tsc --noEmit
npm run build
```
**Result:**
- TypeScript typecheck passed with 0 errors.
- Vite build completed successfully in 7.57s.
