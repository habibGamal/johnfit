# Workout Tracking System

This document describes the workout tracking system for JohnFit: how plans are stored, presented, and tracked across workouts, sets, and days, along with the tech stack and key backend/frontend flows.

## Tech Stack
- Backend: Laravel 11 (PHP 8.4.8), Eloquent ORM
- Frontend: React 18 with Inertia v1 (SPA routing via Laravel)
- UI: Tailwind CSS v3, shadcn/ui
- Admin: Filament v3
- Interactivity: Livewire v3 + Volt (where applicable for admin/widgets)
- Testing: Pest v3, PHPUnit v11
- Tooling: Laravel Pint (code style), Vite (bundling)

## Core Concepts
- Plans (workout and meal) are authored externally and saved as JSON files on storage. The database stores the file paths.
- Users are assigned plans. Access control ensures users can only view and track their assigned plans.
- Tracking is session-based (by calendar date). A "session" is today’s date when the user performs a workout. Previous sessions can be used for suggestions.

## Data Model & Storage

### Workout Plan JSON
- Each `WorkoutPlan` record holds `file_path` to the JSON plan in storage.
- The JSON plan contains an array of days; each day has a `day` key and an array of `workouts`. Each workout entry references a canonical `Workout` via `workout_id` and may include `reps_preset` guidance.

### Eloquent Models
- `WorkoutPlan`: Plan assignment and file path reference.
- `Workout`: Canonical exercise registry (name, tools, videos, etc.).
- `WorkoutCompletion`: Tracks workout-level completion per user/plan/day/workout.
- `WorkoutSetCompletion`:
  - Fillable: `user_id`, `workout_plan_id`, `day`, `workout_id`, `set_number`, `weight`, `reps`, `completed`, `session_date`.
  - Casts: `completed:boolean`, `weight:decimal:2`, `reps:integer`, `set_number:integer`, `session_date:date`.
  - Relationships: `user()`, `workoutPlan()`, `workout()`.
  - Helper: `getPreviousSessionSets(userId, workoutId, beforeDate?)` returns the most recent prior session’s completed sets for suggestions.

### Storage
- JSON plan files live under the configured filesystem (e.g., `storage/app`). The service reads via `Storage::get($workoutPlan->file_path)`.

## Backend Services & Controllers

### `WorkoutTrackingService`
Responsible for orchestrating plan formatting and tracking:
- `formatWorkoutPlanForUser(WorkoutPlan, User)`:
  - Loads plan JSON, gathers referenced `Workout` records.
  - Merges user-specific completion (`WorkoutCompletion`) and today’s sets (`WorkoutSetCompletion`).
  - Computes `previous_sets` suggestions (last completed session for each workout).
  - Flags `requires_weight` based on workout `tools` (e.g., excludes pure bodyweight).
- `toggleWorkoutCompletion(User, workoutPlanId, day, workoutId)`: Flips workout-level completion.
- `saveWorkoutSet(User, workoutPlanId, day, workoutId, setNumber, weight?, reps, completed?)`:
  - Upserts today’s set by `(user, plan, day, workout, set_number, session_date=today)`.
  - Calls `updateWorkoutCompletionFromSets(...)` to reconcile workout-level completion.
- `toggleSetCompletion(User, workoutPlanId, day, workoutId, setNumber)`: Flips today’s set completion.
- `deleteWorkoutSet(User, workoutPlanId, day, workoutId, setNumber)`: Deletes today’s set and renumbers remaining sets; reconciles workout completion.
- `renumberSets(...)`: Maintains sequential `set_number` order within a session.
- `updateWorkoutCompletionFromSets(...)`: Sets workout-level completion based on set states.
- `finishWorkoutDay(User, workoutPlanId, day, action)`: Finalizes the day; handles incomplete sets (`action='complete'|'discard'`).
- `getTodaySessionSummary(User, workoutPlanId, day)`: Returns session stats (e.g., incomplete set count) for dialogs.
- `canUserAccessWorkoutPlan(User, WorkoutPlan)`: Checks assignment/permissions.

### `WorkoutPlanController`
- `index()`: Lists the authenticated user’s plans with formatted data for UI.
- `show(WorkoutPlan)`: Displays a specific plan, enforcing access control.
- `toggleCompletion()`: Validates request, flips workout completion via service.
- `saveSet()`: Validates, saves a set, returns JSON `{ success, set }` with current server state for that set.
- `toggleSet()`: Validates, toggles a set, returns `{ success, completed }`.
- `deleteSet()`: Validates, deletes a set, returns `{ success }`.
- `finishDay()`: Validates, finalizes the day, returns result payload.
- `sessionSummary(WorkoutPlan, day)`: Returns summary for Finish Day dialog (e.g., incomplete sets).

### Routes
Authenticated group in `routes/web.php`:
- GET `/workout-plans` → `WorkoutPlanController@index` (`workout-plans.index`)
- GET `/workout-plans/{workoutPlan}` → `WorkoutPlanController@show` (`workout-plans.show`)
- POST `/workout-plans/toggle-completion` → toggle workout completion (`workout-plans.toggle-completion`)
- POST `/workout-plans/save-set` → save or update a set (`workout-plans.save-set`)
- POST `/workout-plans/toggle-set` → toggle set completion (`workout-plans.toggle-set`)
- DELETE `/workout-plans/delete-set` → delete a set (`workout-plans.delete-set`)
- POST `/workout-plans/finish-day` → finalize day (`workout-plans.finish-day`)
- GET `/workout-plans/{workoutPlan}/session-summary/{day}` → summary (`workout-plans.session-summary`)

## Frontend (Inertia + React)

### Pages & Components
- `WorkoutPlans/Show.tsx`:
  - Displays plan with sidebar progress and main content by day.
  - Computes progress metrics (`completedWorkouts`, `totalWorkouts`, percentage).
  - Calls `sessionSummary` and opens `FinishWorkoutDialog` for day finalization.
- `Components/WorkoutItem.tsx`:
  - Collapsible card per workout, shows thumbnail/video/tools.
  - Displays compact metrics when collapsed; renders `WorkoutSetTracker` when expanded.
  - Tracks local completed count; optimistic UI for smooth interactions.
- `Components/WorkoutSetTracker.tsx`:
  - Initializes sets from `workout.sets` or builds from `reps_preset` with defaults.
  - Supports add/delete/renumber; optimistically toggles completed state and persists via `save-set`.
  - Uses previous session suggestions (`previous_sets`) to prefill weight/reps.
  - Error handling reverts optimistic updates on failure.
- `Components/FinishWorkoutDialog.tsx`:
  - Summarizes incomplete sets; posts `finish-day` with `action='complete'|'discard'`.

### UX & Rules
- Session is today; saving and toggling operate on `session_date=today`.
- Previous session suggestions appear alongside current sets for guidance.
- Workout-level completion is derived from set state; explicit toggling is available.
- Access control prevents viewing plans not assigned to the user.

## Request/Response Shapes (Simplified)
- Toggle workout completion: `{ workout_plan_id, day, workout_id }` → `{ redirected }` (back).
- Save set: `{ workout_plan_id, day, workout_id, set_number, weight?, reps, completed? }` → `{ success: true, set: { id?, set_number, weight, reps, completed } }`.
- Toggle set: `{ workout_plan_id, day, workout_id, set_number }` → `{ success: true, completed }` or `404` if not found.
- Delete set: `{ workout_plan_id, day, workout_id, set_number }` (DELETE) → `{ success: bool }`.
- Finish day: `{ workout_plan_id, day, action:'complete'|'discard' }` → summary/result payload.
- Session summary: `GET` → `{ incomplete_sets, ... }`.

## Access Control & Validation
- All workout tracking routes are under `auth` middleware.
- Controller methods validate payload fields (existence of `workout_plans`/`workouts`, types, ranges).
- Service method `canUserAccessWorkoutPlan` enforces user assignment before formatting or fetching sensitive data.

## Completion Logic Details
- Workout completion flips via `WorkoutCompletion` or is computed from sets by `updateWorkoutCompletionFromSets`.
- Set operations maintain integrity:
  - Upsert by key tuple (user, plan, day, workout, set_number, session_date).
  - Delete triggers renumbering to keep sequential order.
  - Toggling and saving reconcile workout-level completion.

## Testing Guidance
- Use Pest for feature tests; follow existing test directory conventions.
- Filament testing (for admin resources) should follow Filament docs.
- Authenticate within tests accessing these routes/components.
- Livewire/Volt components should be tested via `Volt::test` when applicable; Inertia pages via HTTP tests ensuring props and rendering.

## Development Conventions
- Keep UI consistent (theme, colors, fonts, spacing, layouts).
- For empty list states, render an explicit empty state component.
- Use `npx shadcn@latest add <component>` to add UI parts when needed.
- When implementing Observers, use Laravel’s `#[ObservedBy]` attribute (no manual registration in the event service provider).

## Future Enhancements
- Streak tracking on workout plan pages.
- Rich analytics on previous sessions per workout.
- Export/import plan templates via the admin with Filament actions.
