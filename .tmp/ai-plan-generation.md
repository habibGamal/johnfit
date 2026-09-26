# Task Plan: AI Plan Generation Agent (Prism PHP / OpenAI)

## 🎯 Objectives
Build an advanced AI-driven Plan Generation Agent that creates personalized Meal Plans and Workout Plans tailored to the user's:
1. **Assessment Answers** (goals, activity, sleep, meal frequency, dietary restrictions, preferred proteins/carbs/fats, workout days, workout duration, training location/equipment).
2. **InBody Logs** (weight, height, BMR, body fat %, muscle mass if available).
3. **Available Database Workouts** (IDs, names, target muscles, equipment/tools).
4. **Available Database Meals** (IDs, names, macro profiles per 100g, allowed slots: breakfast, lunch, snack, dinner).

The generated output will strictly follow the exact data schema of the current plan generation algorithm (`MealPlan` and `WorkoutPlan` models, saved JSON plans, and assigned to the user).

The system will use the **Strategy Pattern** to allow instant switching between the current deterministic rule-based generator and the new AI generator via `PLAN_GENERATION_DRIVER=ai` with automatic fallback.

---

## 🛠️ Tech Stack & Architecture

- **Engine:** `prism-php/prism` (LLM integration for Laravel 11 supporting multi-provider structured schema outputs).
- **Default Provider:** OpenAI (`gpt-4o` / `gpt-4o-mini`).
- **Pattern:** Strategy Pattern with `PlanGeneratorInterface`:
  - `RuleBasedPlanGenerator` (encapsulating existing algorithmic logic from `PlanGenerationService`).
  - `AiPlanGenerator` (AI Agent using Prism PHP structured outputs).
  - `PlanGenerationManager` (resolves the configured driver `rule_based` vs `ai`, with automatic fallback to rule-based on AI timeout or failure).
- **Controller & API:** `PlanGenerationController` continues serving `/api/plan-generation/generate` seamlessly without breaking existing frontend or mobile callers.

---

## 📋 Structured Output Schema (Identical to Existing Algorithm)

### 1. Meal Plan Output
- `targets`:
  - `calories`: float
  - `proteins`: float (grams)
  - `carbs`: float (grams)
  - `fats`: float (grams)
- `days`: Array of 7 days (`Day 1` .. `Day 7`), each day containing:
  - `day`: string (`Day 1` ... `Day 7`)
  - `time`: Array of meal slots (`Breakfast`, `Lunch`, `Snack`, `Dinner`):
    - `meal_time`: string
    - `meals`: array containing options:
      - `options`: array of `{ meal_id: int, quantity: int (grams) }`

### 2. Workout Plan Output
- `days`: Array of 7 days (`Day 1` .. `Day 7`), each containing:
  - `day`: string (`Day 1` ... `Day 7`)
  - `workouts`: array of `{ workout_id: int, reps: int (reps_preset_id) }`, or `[]` for rest days.

---

## 🚀 Execution Steps

1. **Step 1: Install & Configure Prism PHP**
   - Run `composer require prism-php/prism --ignore-platform-req=php`
   - Publish config `php artisan vendor:publish --tag=prism-config`
   - Configure OpenAI provider and `.env.example` keys (`OPENAI_API_KEY`, `PLAN_GENERATION_DRIVER=ai`, `AI_PLAN_MODEL=gpt-4o-mini`).

2. **Step 2: Create Strategy Interface & Refactor Existing Engine**
   - Create `app/Contracts/PlanGeneratorInterface.php`.
   - Implement `App\Services\PlanGeneration\RuleBasedPlanGenerator.php` preserving existing deterministic algorithms.
   - Refactor `PlanGenerationService` to act as the Manager/Facade that routes to the active driver with safe fallback.

3. **Step 3: Build AI Agent Context Builder & Prompt Generator**
   - Extract and format User Assessment answers into clear, structured context.
   - Extract latest InBody data (or calculate standard Harris-Benedict/Mifflin estimates if none exists).
   - Intelligently pre-filter meals (respecting dietary restrictions like lactose/gluten) and condense to essential token-efficient data `[id, name, type, calories, protein, carbs, fat]`.
   - Filter workouts according to equipment/location (gym vs home without equipment) and condense `[id, name, muscles, tools]`.
   - Load RepsPresets (`3x10`, `4x12`, etc.) so the AI selects valid preset IDs.

4. **Step 4: Implement `AiPlanGenerator` with Prism Structured Output**
   - Define Prism `ObjectSchema` matching the exact meal plan and workout plan structure.
   - Inject the system prompt and contextual user data.
   - Execute Prism structured generation with OpenAI.
   - Validate returned IDs against the database to guarantee zero hallucinated meal or workout IDs.
   - Persist `MealPlan` and `WorkoutPlan` models, attach JSON file paths via existing booted hooks, and assign to the user via `PlanAssignmentService`.

5. **Step 5: Verification & Safety Tests**
   - Verify syntax with `php -l`.
   - Test generating plans with rule-based engine and AI engine.
   - Confirm assignments, targets, and days structure are identical.
