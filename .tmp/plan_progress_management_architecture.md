# Workout & Meal Plan Progress Tracking Architecture

## 1. Overview & Core Dilemma
Tracking progress across workout and meal plans that change dynamically requires decoupling high-level plan definitions from daily user execution logs. Moving to a **time-based daily target + points/scoring system** and **materializing daily instances** (cloning/forking daily templates into the user's schedule) ensures historical data integrity, clean progress calculation, and flexibility when admins edit active plans.

---

## 2. Tracking Assigned Plans at Any Given Time (Temporal Schedule & Materialized Instances)

Instead of relying on a fragile single foreign key linking past logs directly to mutable plan templates, use a two-tiered schedule structure:

### 1. `user_plan_assignments` (High-Level Timeline)
Stores explicit date ranges (`start_date`, `end_date`, `plan_id`, `status`).
* **Querying historical assignments:**
```sql
SELECT plan_id, status 
FROM user_plan_assignments 
WHERE user_id = :uid 
  AND :target_date BETWEEN start_date AND end_date;
```

### 2. `user_daily_schedules` (Materialized Day Blueprint)
Every individual day is instantiated as an independent record containing its target score, earned score, locking status (`is_locked`), and template origin reference:
* Holds `user_id`, `date`, `workout_assignment_id` (nullable FK), `meal_assignment_id` (nullable FK), `target_score`, `earned_score`, and `is_locked`.
* When an admin assigns a multi-day plan (workout, meal, or both), the backend generates/updates individual `user_daily_schedules` records and materializes their items into `user_daily_items`.
* **Result:** Historical days remain frozen even if the original template is modified or deleted later.

---

## 3. Handling "Today's" Edits (Option 1 vs Option 2)

| Scenario | What Happens to Completed Items | What Happens to Today's Score Target | User Experience |
| :--- | :--- | :--- | :--- |
| **Option 1: Force Update Today** | Completed items matching altered/deleted items are marked or voided. | Target score recalculates dynamically ($Target_{new} = \sum ActiveItems$). | User opens app, sees updated list and recalculated points ratio ($Earned / Target_{new}$). |
| **Option 2: Future Days Only (Recommended Default)** | Today's schedule remains **untouched**. | Target score remains unchanged for today. | Admin edits apply starting from `Tomorrow (00:00:00)`. Today finishes on the existing snapshot. |

---

## 4. Database Schema Design

```text
+----------------------------+       +------------------------------------+
|   user_plan_assignments    |       |        user_daily_schedules        |
|----------------------------|       |------------------------------------|
| id (PK)                    | 1   * | id (PK)                            |
| user_id (FK)               |------>| user_id (FK)                       |
| plan_id (FK)               |       | workout_assignment_id (FK, null)   |
| plan_type (workout/meal)   |       | meal_assignment_id (FK, null)      |
| start_date (DATE)          |       | date (DATE, UNIQUE per user)       |
| end_date (DATE)            |       | target_score (INT)                 |
| status (active/completed)  |       | earned_score (INT)                 |
+----------------------------+       | is_locked (BOOLEAN)                |
                                     +------------------------------------+
                                                        | 1
                                                        |
                                                        | *
                                     +------------------------------------+
                                     |          user_daily_items          |
                                     |------------------------------------|
                                     | id (PK)                            |
                                     | daily_schedule_id (FK)             |
                                     | type (workout | meal)              |
                                     | item_name (VARCHAR)                |
                                     | target_details (JSON)              |
                                     | points (INT)                       |
                                     | is_completed (BOOLEAN)             |
                                     | completed_at (TIMESTAMP)           |
                                     | execution_payload (JSON)           |
                                     | status (active/voided/edited)      |
                                     +------------------------------------+
```

---

## 5. Step-by-Step State Flow for "Today"

### Initial State (Tuesday Morning — Target: 16 pts)
* `ex1` (3 pts) $\rightarrow$ **Done** (+3 pts earned)
* `ex2` (3 pts) $\rightarrow$ Pending
* `meal1` (5 pts) $\rightarrow$ **Done** (+5 pts earned)
* `meal2` (5 pts) $\rightarrow$ Pending
* **Current Progress:** $\frac{8}{16} = 50\%$

### Admin Edits Tuesday at 2:00 PM (Updates `ex1` details, removes `ex2`, adds `ex3` [4 pts])

#### Under Option 1 (Force Update Today):
1. `ex1` updates to `ex1v2`. Its completion status is preserved (or flagged `needs_reconfirmation` if target intensity changed significantly).
2. `ex2` is soft-deleted/voided from `user_daily_items`.
3. `ex3` (4 pts) is inserted into `user_daily_items`.
4. Backend recalculates `target_score` for Tuesday:
   $$3\text{ (ex1)} + 4\text{ (ex3)} + 5\text{ (meal1)} + 5\text{ (meal2)} = 17\text{ pts}$$
5. Current progress updates immediately to: $\frac{8}{17} = 47.05\%$.

#### End-of-Day Freezing (Midnight Cron / Trigger):
1. At `23:59:59`, the backend executes:
```sql
UPDATE user_daily_schedules 
SET is_locked = true 
WHERE date = CURRENT_DATE;
```
2. Past days become permanently immutable for reporting and historical consistency.
