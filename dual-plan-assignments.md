# Plan: Dual Plan Assignments Foreign Keys on User Daily Schedules

## Overview
Enable `user_daily_schedules` to support simultaneous assignment of both a **Workout Plan** and a **Meal Plan** on the same calendar date for a user by replacing the single `assignment_id` column with dual nullable foreign keys: `workout_assignment_id` and `meal_assignment_id`.

## Project Type
**BACKEND** (Laravel 11/12, MySQL/SQLite, Eloquent ORM)

---

## Success Criteria
- [ ] `user_daily_schedules` table schema has `workout_assignment_id` and `meal_assignment_id` as nullable foreign keys referencing `user_plan_assignments(id)`.
- [ ] `UserDailySchedule` model defines `workoutAssignment()` and `mealAssignment()` relationships.
- [ ] `UserPlanAssignment` model defines inverse relationships to daily schedules.
- [ ] `DailyScheduleService::materializeDateForUser()` accurately links both workout and meal plan assignments when generating daily schedules.
- [ ] Architecture design doc (`.tmp/plan_progress_management_architecture.md`) is updated to reflect the new schema.
- [ ] No regression in plan synchronization, score calculations, or existing schedule queries.

---

## Tech Stack & Dependencies
- **Framework**: Laravel 11/12
- **ORM**: Eloquent
- **Database**: MySQL / SQLite
- **Date Handling**: Carbon / CarbonPeriod

---

## File Structure & Impact Analysis
```text
├── .tmp/
│   └── plan_progress_management_architecture.md   [MODIFY] Update schema diagram and ERD description
├── database/migrations/
│   └── 2026_08_21_000001_create_plan_progress_management_tables.php [MODIFY] Replace assignment_id with dual FKs
├── app/Models/
│   ├── UserDailySchedule.php                      [MODIFY] Fillable attributes & relationship methods
│   └── UserPlanAssignment.php                     [MODIFY] Inverse relationships for workout & meal schedules
└── app/Services/
    └── DailyScheduleService.php                   [MODIFY] Populate workout_assignment_id & meal_assignment_id
```

---

## Task Breakdown

### Task 1: Update Architecture Documentation
- **Task ID**: `TASK-001`
- **Agent**: `database-architect`
- **Skills**: `@clean-code`, `@database-design`
- **Priority**: `P0`
- **Dependencies**: None
- **Input**: Current schema section in `.tmp/plan_progress_management_architecture.md`
- **Output**: Updated table diagram showing `workout_assignment_id (FK, nullable)` and `meal_assignment_id (FK, nullable)` in `user_daily_schedules`.
- **Verify**: Inspect file to ensure schema diagram and text are aligned with dual assignment strategy.

---

### Task 2: Update Database Migration
- **Task ID**: `TASK-002`
- **Agent**: `database-architect`
- **Skills**: `@clean-code`, `@database-design`
- **Priority**: `P0`
- **Dependencies**: `TASK-001`
- **Target File**: `database/migrations/2026_08_21_000001_create_plan_progress_management_tables.php`
- **Input**: Existing table definition for `user_daily_schedules`.
- **Output**: 
  - Remove `$table->foreignId('assignment_id')`.
  - Add `$table->foreignId('workout_assignment_id')->nullable()->constrained('user_plan_assignments')->nullOnDelete();`.
  - Add `$table->foreignId('meal_assignment_id')->nullable()->constrained('user_plan_assignments')->nullOnDelete();`.
- **Verify**: Run `php artisan migrate:fresh` (or migration verification).

---

### Task 3: Update `UserDailySchedule` Model
- **Task ID**: `TASK-003`
- **Agent**: `backend-specialist`
- **Skills**: `@clean-code`
- **Priority**: `P1`
- **Dependencies**: `TASK-002`
- **Target File**: `app/Models/UserDailySchedule.php`
- **Input**: Existing model attributes and relationships.
- **Output**:
  - Update `$fillable` array to replace `'assignment_id'` with `'workout_assignment_id'` and `'meal_assignment_id'`.
  - Add `public function workoutAssignment(): BelongsTo` returning relationship for `workout_assignment_id`.
  - Add `public function mealAssignment(): BelongsTo` returning relationship for `meal_assignment_id`.
- **Verify**: Verify Eloquent relationship instantiation via model tests or Tinker.

---

### Task 4: Update `UserPlanAssignment` Model
- **Task ID**: `TASK-004`
- **Agent**: `backend-specialist`
- **Skills**: `@clean-code`
- **Priority**: `P1`
- **Dependencies**: `TASK-003`
- **Target File**: `app/Models/UserPlanAssignment.php`
- **Input**: Existing `dailySchedules()` relationship.
- **Output**:
  - Update or expand relationships to support `hasMany(UserDailySchedule::class, 'workout_assignment_id')` and `hasMany(UserDailySchedule::class, 'meal_assignment_id')`.
- **Verify**: Model unit check.

---

### Task 5: Update `DailyScheduleService` Materialization Logic
- **Task ID**: `TASK-005`
- **Agent**: `backend-specialist`
- **Skills**: `@clean-code`, `@lint-and-validate`
- **Priority**: `P1`
- **Dependencies**: `TASK-003`, `TASK-004`
- **Target File**: `app/Services/DailyScheduleService.php`
- **Input**: `materializeDateForUser()` method.
- **Output**:
  - Separate `$activeAssignments` into `$workoutAssignment` and `$mealAssignment` by `plan_type`.
  - Pass `'workout_assignment_id' => $workoutAssignment?->id` and `'meal_assignment_id' => $mealAssignment?->id` into `firstOrCreate` / schedule creation.
  - If a schedule already exists on that date and gains a new active assignment (e.g. meal assigned after workout), update the respective assignment ID.
- **Verify**: Materialize a test user with both assignments and inspect the generated `user_daily_schedules` row.

---

## Phase X: Verification Checklist

- [x] Schema validation: `user_daily_schedules` contains both foreign keys with proper nullability and cascade rules.
- [x] Model relationships: `$schedule->workoutAssignment` and `$schedule->mealAssignment` load properly without query errors.
- [x] Materialization check: User with 1 workout plan + 1 meal plan receives 1 `UserDailySchedule` row with both FKs populated, and items for both plans attached.
- [x] Single-plan check: User with only a workout plan receives `meal_assignment_id = null` and vice versa.
- [x] No regression in score recalculations or weekly adherence analytics.

## ✅ PHASE X COMPLETE
- Migration: ✅ Ran cleanly (`workout_assignment_id`, `meal_assignment_id` with `nullOnDelete`)
- Eloquent Models: ✅ Direct & inverse relationships verified
- Service Materialization: ✅ Populates both plan types concurrently
- Verification Script: ✅ Passed without error
