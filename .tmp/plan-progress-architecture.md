# Workout & Meal Plan Progress Tracking Architecture Plan

> **Slug:** `plan-progress-architecture`  
> **Status:** Draft / Ready for Review  
> **Target:** Replace legacy workout/meal tracking with materialized daily instances and points-based scoring per `.tmp/plan_progress_management_architecture.md`.

---

## 1. Overview & Objectives

### What We Are Building
We are transitioning the entire workout and meal plan management system from a rigid, tightly-coupled completion tracking model to a **temporal schedule with materialized daily instances and a points-based scoring engine**.

### Why This Architecture
1. **Decoupled Plan Definitions vs Execution**: High-level plan templates can be modified or assigned without mutating past historical user logs.
2. **Materialized Daily Snapshots**: Daily assignments (`user_daily_schedules` and `user_daily_items`) are materialized per user per date, ensuring complete historical auditability and frozen records (`is_locked`).
3. **Points & Scoring Adherence**: Adherence is measured via daily points ($EarnedScore / TargetScore$), accommodating mixed workouts and meals seamlessly.
4. **Development Freedom**: As this is in dev stage, legacy completion migrations, models, services, and components will be cleaned up and refactored cleanly without backwards-compatibility debt.

---

## 2. Project Type & Tech Stack

- **Project Type:** Full-Stack Web Application (Laravel 11 + Inertia.js + React 18 + TypeScript + Tailwind CSS)
- **Backend Stack:** PHP 8.2+, Laravel 11, Eloquent ORM, MySQL/SQLite, Filament Admin v3
- **Frontend Stack:** React 18, TypeScript, Inertia.js, Tailwind CSS, Lucide Icons, Shadcn/Radix UI primitives
- **Architecture Pattern:** Service Layer + Materialized Daily Instances + Scheduled Snapshot Freezing

---

## 3. Database Schema & Data Modeling

### 3.1 Tables to Drop / Refactor (Legacy Debt Cleanup)
- ❌ Drop `workout_completions` (and migration `2025_03_19_000001_create_workout_completions_table.php`)
- ❌ Drop `meal_completions` (and migration `2025_04_24_000002_create_meal_completions_table.php`)
- ❌ Drop/Refactor `workout_set_completions` (migrate historical set tracking into item `execution_payload` or replace with dedicated item set records)
- ❌ Drop `user_workout_plan` & `user_meal_plan` pivot tables (replaced by `user_plan_assignments`)

### 3.2 New Database Tables

#### 1. `user_plan_assignments`
Tracks high-level plan assignments across calendar windows.
```sql
CREATE TABLE user_plan_assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NOT NULL, -- references unified plan or workout/meal template
    plan_type VARCHAR(50) NOT NULL DEFAULT 'unified', -- 'unified', 'workout', 'meal'
    start_date DATE NOT NULL,
    end_date DATE NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_dates (user_id, start_date, end_date, status)
);
```

#### 2. `user_daily_schedules`
Materialized daily blueprint per user for each calendar day.
```sql
CREATE TABLE user_daily_schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    assignment_id BIGINT UNSIGNED NULL,
    date DATE NOT NULL,
    target_score INT UNSIGNED NOT NULL DEFAULT 0,
    earned_score INT UNSIGNED NOT NULL DEFAULT 0,
    is_locked BOOLEAN NOT NULL DEFAULT FALSE,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assignment_id) REFERENCES user_plan_assignments(id) ON DELETE SET NULL,
    UNIQUE KEY uq_user_date (user_id, date),
    INDEX idx_user_schedule_date (user_id, date, is_locked)
);
```

#### 3. `user_daily_items`
Granular materialized tasks (workout exercises or meals) for a specific daily schedule.
```sql
CREATE TABLE user_daily_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    daily_schedule_id BIGINT UNSIGNED NOT NULL,
    type ENUM('workout', 'meal') NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    reference_id BIGINT UNSIGNED NULL, -- optional FK / link to catalog workout/meal
    target_details JSON NOT NULL,      -- sets, reps, weight target, macros, portions, instructions
    points INT UNSIGNED NOT NULL DEFAULT 1,
    is_completed BOOLEAN NOT NULL DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    execution_payload JSON NULL,       -- sets logged, reps completed, actual weight, meal consumed qty
    status ENUM('active', 'voided', 'edited') DEFAULT 'active',
    order_index INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (daily_schedule_id) REFERENCES user_daily_schedules(id) ON DELETE CASCADE,
    INDEX idx_schedule_type_status (daily_schedule_id, type, status)
);
```

---

## 4. Proposed Architecture & System Components

### 4.1 Backend Service Layer
1. **`PlanAssignmentService`**:
   - Assigns a plan template to a user with a `start_date` and optional `end_date`.
   - Materializes the schedule window (`user_daily_schedules` and `user_daily_items`) from plan template days.
2. **`DailyScheduleService`**:
   - Queries and returns materialized daily schedules with items for any requested date or range.
   - Calculates and updates `target_score` and `earned_score` dynamically.
   - Handles admin update strategies: Option 2 (default: future days only) vs Option 1 (recalculate today with active items).
3. **`DailyItemTrackingService`**:
   - Records completion / uncompletion of workout or meal items.
   - Validates and stores `execution_payload` (e.g. sets with weight/reps for workouts, or consumption macros for meals).
   - Atomically updates schedule `earned_score` and recalculates overall adherence.
4. **`ScheduleLockCommand` (Cron / Scheduler)**:
   - Midnight scheduled command (`schedule:lock-past-days`) locking past `user_daily_schedules` (`is_locked = true`).
5. **Score & Progression Integration**:
   - Refactor `FitnessScoreService` and `ProgressionService` to read from `user_daily_items` (where `type = 'workout'` / `type = 'meal'` and `is_completed = true`).

### 4.2 Frontend Architecture (Inertia + React + TypeScript)
1. **Unified Daily Schedule / Plan View (`Pages/Schedule/Index.tsx` & `Pages/Schedule/Show.tsx`)**:
   - Replaces separate legacy `/workout-plans` and `/meal-plans` with a high-performance, unified daily planner.
   - Date picker / calendar strip showing daily completion rings, target points, and locked/active badges.
   - Separate tabs or unified stream for Today's Workouts & Meals with instant toggle and detailed logger.
   - Workout set logger drawer/modal for logging sets, reps, and weights directly into `execution_payload`.
   - Meal completion modal for logging portions/calories.
2. **Dashboard Refactoring (`Pages/Dashboard.tsx` & Dashboard Components)**:
   - Modern Adherence & Points Ring widget ($Earned / Target$).
   - Weekly Adherence Heatmap / Bar chart consuming new points data.
   - Streamlined Recent Activity cards driven by `user_daily_items`.
3. **Navigation & Routes**:
   - Update `BottomNavigation.tsx` (Dashboard, Schedule/Plan, InBody, Analytics, Profile).
   - Remove legacy routes (`/workout-plans/*`, `/meal-plans/*`) and add clean RESTful `/schedule/*` routes.

---

## 5. Detailed Task Breakdown

### Phase 1: Database & Backend Cleanup & Migration (P0)
- [ ] **Task 1.1: Remove Legacy Migrations & Schema**:
  - **Agent**: `backend-specialist` | **Skill**: `@clean-code`
  - **Input**: Existing migration files for legacy completions and pivots.
  - **Output**: Clean migrations directory with legacy completions removed.
  - **Verify**: `php artisan migrate:fresh` executes cleanly.
- [ ] **Task 1.2: Create New Migrations**:
  - **Agent**: `database-architect` | **Skill**: `@database-design`
  - **Input**: Schema specification for `user_plan_assignments`, `user_daily_schedules`, `user_daily_items`.
  - **Output**: Migration files created with correct foreign keys, indexes, and cascades.
  - **Verify**: Database tables verified with `php artisan migrate`.
- [ ] **Task 1.3: Create Eloquent Models & Relationships**:
  - **Agent**: `backend-specialist` | **Skill**: `@clean-code`
  - **Input**: Table definitions.
  - **Output**: `UserPlanAssignment.php`, `UserDailySchedule.php`, `UserDailyItem.php` with casts, scopes, and relationships to `User`.
  - **Verify**: Unit tests asserting model relationships.

### Phase 2: Core Services & Business Logic (P1)
- [ ] **Task 2.1: Implement `DailyScheduleService`**:
  - **Agent**: `backend-specialist` | **Skill**: `@clean-code`
  - **Input**: Models and plan template schema.
  - **Output**: Methods for generating schedules, fetching daily items, calculating scores, and updating targets.
  - **Verify**: Feature tests testing schedule creation and score calculation.
- [ ] **Task 2.2: Implement `DailyItemTrackingService`**:
  - **Agent**: `backend-specialist` | **Skill**: `@clean-code`
  - **Input**: User item completions and execution payloads.
  - **Output**: Toggle completion, save set/meal execution payloads, and update `earned_score`.
  - **Verify**: Feature tests testing completion toggle and score update.
- [ ] **Task 2.3: Implement `ScheduleLockCommand`**:
  - **Agent**: `backend-specialist` | **Skill**: `@clean-code`
  - **Input**: Command definition in `routes/console.php` or `app/Console/Commands`.
  - **Output**: Command to lock past days and schedule in Kernel/Console.
  - **Verify**: Artisan test verifying `is_locked` flips for past days.
- [ ] **Task 2.4: Refactor `FitnessScoreService` & `ProgressionService`**:
  - **Agent**: `backend-specialist` | **Skill**: `@clean-code`
  - **Input**: Existing scoring logic.
  - **Output**: Scoring algorithms adapted to compute adherence from `user_daily_items`.
  - **Verify**: `php artisan test` runs without regressions.

### Phase 3: Controllers, Routes & API Layer (P1)
- [ ] **Task 3.1: Create `DailyScheduleController`**:
  - **Agent**: `backend-specialist` | **Skill**: `@api-patterns`
  - **Input**: Requests for daily schedule view, item toggle, and set recording.
  - **Output**: `index`, `show`, `toggleItem`, `saveExecutionPayload`, `daySummary` actions returning Inertia responses and JSON.
  - **Verify**: HTTP test asserting 200 OK and correct JSON/Inertia props.
- [ ] **Task 3.2: Update Web Routes**:
  - **Agent**: `backend-specialist` | **Skill**: `@clean-code`
  - **Input**: `routes/web.php`.
  - **Output**: Removed obsolete workout/meal plan routes; added `/schedule` and `/schedule/items/*` routes.
  - **Verify**: `php artisan route:list` is clean and structured.

### Phase 4: Frontend UI / UX Implementation (P2)
- [ ] **Task 4.1: Update TypeScript Types**:
  - **Agent**: `frontend-specialist` | **Skill**: `@clean-code`
  - **Input**: `resources/js/types/index.d.ts`.
  - **Output**: New interfaces for `UserDailySchedule`, `UserDailyItem`, `UserPlanAssignment`, and updated `DashboardProps`.
  - **Verify**: TypeScript compile check (`npx tsc --noEmit`).
- [ ] **Task 4.2: Build Unified Daily Schedule View (`Pages/Schedule/Index.tsx`)**:
  - **Agent**: `frontend-specialist` | **Skill**: `@frontend-design` + `@shadcn-ui`
  - **Input**: Daily schedule props.
  - **Output**: Modern, responsive daily schedule view with date navigator, points progress bar, workout item cards with set tracker, and meal cards with macro breakdown.
  - **Verify**: Visual review in browser; smooth interaction state.
- [ ] **Task 4.3: Refactor Dashboard & Navigation**:
  - **Agent**: `frontend-specialist` | **Skill**: `@frontend-design`
  - **Input**: `resources/js/Pages/Dashboard.tsx`, `BottomNavigation.tsx`, Dashboard components.
  - **Output**: Points adherence cards, weekly progress bar, updated navigation tabs pointing to `/schedule`.
  - **Verify**: Dashboard loads without errors and displays real-time score summaries.
- [ ] **Task 4.4: Remove Legacy Frontend Components**:
  - **Agent**: `frontend-specialist` | **Skill**: `@clean-code`
  - **Input**: Legacy `WorkoutPlans/` and `MealPlans/` folders.
  - **Output**: Cleaned `resources/js/Pages/` and `resources/js/Components/` directories.
  - **Verify**: Clean build via `npm run build`.

---

## 6. Phase X: Verification & Definition of Done

- [ ] **Database Verification**: `php artisan migrate:fresh --seed` runs without error.
- [ ] **Static Type & Lint Check**: `npx tsc --noEmit` and `npm run lint` pass.
- [ ] **Backend Test Suite**: `php artisan test` passes with full coverage on schedule and item tracking.
- [ ] **Build Validation**: `npm run build` generates production bundle without warnings.
- [ ] **Manual Interactive Validation**:
  1. Assign plan $\rightarrow$ Daily schedule & items materialized.
  2. Toggle item done $\rightarrow$ Points earned immediately increments.
  3. Log sets $\rightarrow$ Execution payload persisted.
  4. Change date $\rightarrow$ Schedule loads correct frozen or active snapshot.
  5. Dashboard shows updated points adherence.

---

## 7. Next Steps
1. Review the plan details below.
2. Confirm the plan or provide feedback to make adjustments.
3. Once approved, proceed to execution phase.
