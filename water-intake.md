# Water Intake Feature - Implementation Plan (Updated)

## Overview
A comprehensive hydration tracking and personalized target recommendation system for the JohnFit platform. The system computes tailored hydration targets based on clinical sports nutrition formulas (**Tier 1 Base Weight** and **Tier 2 InBody Composition**), provides full **Admin/Coach Customization via Filament 3**, enables seamless one-tap logging in the web app, and integrates with daily schedule adherence and push notifications.

---

## Project Type
**WEB** (Laravel 11 + Filament 3 Admin Panel + Inertia.js + React 18 + TypeScript + Tailwind CSS + shadcn/ui + Expo Push Notifications)

---

## 1. How We Determine the Required Water Quantity for the User

Hydration needs are determined through a clear 4-level precedence hierarchy:

```
[Level 1: Admin/Coach Override] (Filament 3)
      ↓ (if not manually overridden by coach)
[Level 2: User Custom Goal] (if coach permits user custom targets)
      ↓ (if no manual goal set)
[Level 3: Tier 2 - InBody Composition Formula] (if InBody scan exists)
      ↓ (if no InBody scan exists)
[Level 4: Tier 1 - Base Weight Formula] (standard clinical baseline)
      + [Workout Day Bonus] (dynamically added on training days)
```

---

### Tier 1: Baseline Body Weight Formula (Primary Clinical Standard)
Derived from clinical sports medicine (ACSM, EFSA) baseline of $35\text{ ml}$ per kg of body weight:
$$\text{Base Water (ml)} = \text{Body Weight (kg)} \times 35\text{ ml}$$
- **70 kg User:** $70 \times 35 = 2,450\text{ ml}$ (~2.5 L)
- **85 kg User:** $85 \times 35 = 2,975\text{ ml}$ (~3.0 L)
- **100 kg User:** $100 \times 35 = 3,500\text{ ml}$ (~3.5 L)

---

### Tier 2: InBody Composition Refinement (Body Composition Standard)
When the user records an InBody scan (`InBodyLog` with `smm` Skeletal Muscle Mass, `lean_body_mass`, and `weight`):
- Skeletal muscle is **~75% water**, while adipose fat tissue is only **~10% water**.
- Muscular athletes require significantly higher water volumes to support glycogen storage and cellular protein synthesis.
- **Refined InBody Formula:**
  $$\text{Adjusted Base (ml)} = (\text{Lean Body Mass (kg)} \times 40\text{ ml}) + (\text{Fat Mass (kg)} \times 10\text{ ml})$$
  *(where $\text{Fat Mass} = \text{Weight} - \text{Lean Body Mass}$)*
- **Example:**
  - 85 kg athlete with 70 kg Lean Body Mass:
    $$(70 \times 40) + (15 \times 10) = 2,800 + 150 = 2,950\text{ ml}$$
  - Compared to 85 kg sedentary individual with 55 kg Lean Body Mass:
    $$(55 \times 40) + (30 \times 10) = 2,200 + 300 = 2,500\text{ ml}$$

---

### Dynamic Workout Day Adjustment (Training Bonus)
Automatically detected via `UserDailySchedule::todaySchedule()`:
- **Rest Days:** Target = Base target.
- **Workout Days (Dynamic Bonus):**
  - Workout duration $\le 30\text{ min}$: **+350 ml**
  - Workout duration $31\text{–}45\text{ min}$: **+500 ml**
  - Workout duration $46\text{–}60\text{ min}$: **+750 ml**
  - Workout duration $> 60\text{ min}$: **+1,000 ml**

---

### Admin / Coach Customization (Filament 3 Panel)
The coach/admin has full authority to customize the hydration parameters per user in `app/Filament/Resources/UserResource.php`:
1. **Target Mode Selection:**
   - `Auto (Tier 1 / Tier 2)`: The system automatically computes target from user weight/InBody + workout days.
   - `Fixed Daily Target`: Admin sets exact ml (e.g., $3,500\text{ ml}$ or $4,000\text{ ml}$ during peaking/cutting phase).
   - `Custom Multiplier`: Admin sets custom ml/kg ratio (e.g., $40\text{ ml/kg}$ or $45\text{ ml/kg}$).
2. **Coach Hydration Note:**
   - Admin can write a direct guidance note displayed on the client's water widget (e.g., *"Drink 1L before noon, and 1L around your workout session with electrolytes"*).
3. **Lock/Unlock User Override:**
   - Toggle whether the user can change their target in the app or if it remains strictly coach-prescribed.
4. **Quick Table Action in Filament:**
   - "Set Water Target" action directly on the Users list table with a fast modal to adjust client target in seconds.

---

## Success Criteria
- [ ] Tier 1 and Tier 2 formulas calculate accurate, personalized targets based on user biometric data.
- [ ] Admin can view and customize any client's hydration target and notes from Filament 3 (`UserResource`).
- [ ] Client sees their assigned target and coach note on their dashboard widget.
- [ ] One-tap logging (+250ml glass, +500ml bottle, +750ml shaker, custom ml) with instant undo.
- [ ] Daily progress visualized with animated liquid reservoir / progress circle.
- [ ] 7-day adherence history available on dashboard / analytics.
- [ ] Automated Expo push notifications remind users if they are behind schedule.

---

## Tech Stack & Architecture
- **Admin Panel:** Filament 3 (Laravel Forms, Infolists, Actions, Tables).
- **Backend:** Laravel 11, Eloquent ORM, Service Layer (`WaterIntakeService`).
- **Database:** MySQL / SQLite (`users` table fields for admin settings, `user_daily_water_logs`, `user_water_entries`).
- **Frontend:** React 18 + Inertia.js + TypeScript + Tailwind CSS + shadcn/ui + Framer Motion + Recharts.
- **Notifications:** Expo Push Notifications (`NotificationChannels\Expo`).

---

## File Structure & Changes

```
app/
├── Filament/
│   └── Resources/
│       ├── UserResource.php                    (Update: Add Water Target Form Section, Table Action, Infolist)
│       └── UserResource/Pages/ViewUser.php     (Update: Display Hydration Infolist)
├── Models/
│   ├── User.php                                (Update: Add water settings attributes & relations)
│   ├── UserDailyWaterLog.php                   (New: Daily target, consumed ml, completion)
│   └── UserWaterEntry.php                      (New: Timestamped container logs for undo/history)
├── Services/
│   └── WaterIntakeService.php                  (New: Tier 1/2 calculation, admin overrides, logging, stats)
├── Http/Controllers/
│   └── WaterIntakeController.php               (New: Inertia/JSON endpoints for logging, undo, custom target)
routes/
└── web.php                                     (Update: Register /water endpoints)
database/
└── migrations/
    ├── 2026_09_25_000001_add_water_settings_to_users_table.php (New: Admin settings columns)
    └── 2026_09_25_000002_create_water_intake_tables.php        (New: Logs and entries tables)
resources/js/
├── Components/
│   └── Water/
│       ├── WaterIntakeWidget.tsx               (New: Liquid animation, quick-adds, undo, coach note)
│       ├── HydrationCalculatorModal.tsx        (New: Tier 1/2 breakdown + user override if allowed)
│       └── WaterWeeklyChart.tsx                (New: 7-day adherence bar chart)
├── Pages/
│   ├── Dashboard.tsx                           (Update: Place WaterIntakeWidget in dashboard layout)
│   └── Schedule/Index.tsx                      (Update: Daily hydration checklist item)
└── types/
    └── water.d.ts                              (New: TypeScript interfaces)
```

---

## Database Schema Design

### 1. Columns added to `users` table:
| Column | Type | Default | Description |
|---|---|---|---|
| `water_target_mode` | `enum('auto', 'fixed', 'custom_multiplier')` | `'auto'` | Target calculation strategy |
| `admin_water_target_ml` | `unsignedInteger, nullable` | `null` | Coach-prescribed exact target |
| `water_multiplier_per_kg` | `decimal:2, nullable` | `null` | Coach-prescribed ml per kg |
| `admin_water_notes` | `text, nullable` | `null` | Hydration notes from coach to user |
| `allow_user_water_override`| `boolean` | `true` | Whether user can set custom target |

### 2. `user_daily_water_logs` table:
| Column | Type | Notes |
|---|---|---|
| `id` | `bigint unsigned` | Primary key |
| `user_id` | `foreignId` | Constrained to `users`, cascade on delete |
| `date` | `date` | Target date (`Y-m-d`) |
| `target_ml` | `unsignedInteger` | Computed daily target (reflecting Tier 1/2, workout bonus, or admin override) |
| `consumed_ml` | `unsignedInteger` | Total logged for the day |
| `custom_target_ml` | `unsignedInteger, nullable` | User custom override (if allowed) |
| `is_completed` | `boolean` | True when `consumed_ml >= target_ml` |
| `completed_at` | `timestamp, nullable` | Timestamp when target was reached |
| `timestamps` | `timestamps` | Created/updated |
| *Indexes* | Unique `['user_id', 'date']` | Fast daily lookup |

### 3. `user_water_entries` table:
| Column | Type | Notes |
|---|---|---|
| `id` | `bigint unsigned` | Primary key |
| `water_log_id` | `foreignId` | Constrained to `user_daily_water_logs`, cascade |
| `amount_ml` | `unsignedInteger` | e.g., 250, 500, 750 |
| `container_type` | `string(30)` | 'cup', 'bottle', 'shaker', 'custom' |
| `logged_at` | `timestamp` | Timestamp of logging event |
| `timestamps` | `timestamps` | For auditing and ordering |

---

## Detailed Task Breakdown

### Phase 1: Database & Admin Panel (Filament 3)
- [x] **Task 1: Database Migrations & Eloquent Models**
  - **Agent:** `backend-specialist` | **Skill:** `database-design`
  - Created migrations `2026_09_25_000001_add_water_settings_to_users_table.php` and `2026_09_25_000002_create_water_intake_tables.php`.
  - Updated `User.php` with relations (`dailyWaterLogs()`, `todayWaterLog()`) and fillable/casts.
  - Created `UserDailyWaterLog.php` and `UserWaterEntry.php` models.

- [x] **Task 2: Filament 3 Admin Integration (`UserResource.php`)**
  - **Agent:** `backend-specialist` | **Skill:** `clean-code`
  - Added "Hydration & Water Settings" section in `UserResource::form()`.
  - Added "Water Goal" modal table action in `UserResource::table()`.
  - Added "Hydration & Daily Water Target" section in `UserResource::infolist()`.

- [x] **Task 3: WaterIntakeService (Core Calculation & Precedence Engine)**
  - **Agent:** `backend-specialist` | **Skill:** `clean-code`
  - Implemented 4-level calculation engine in `WaterIntakeService`:
    1. Admin fixed target
    2. Admin custom multiplier
    3. Tier 2 InBody composition formula (`lean_body_mass * 40 + fat_mass * 10`)
    4. Tier 1 base weight formula (`weight * 35`)
    + Dynamic workout day bonus
  - Implemented `logIntake()`, `deleteEntry()`, and `getWeeklyStats()`.

- [x] **Task 4: WaterIntakeController & Web Routes**
  - **Agent:** `backend-specialist` | **Skill:** `api-patterns`
  - Created `WaterIntakeController` with `/water`, `/water/log`, `/water/entries/{id}`, `/water/target`.
  - Registered authenticated routes in `routes/web.php`.
  - Injected `WaterIntakeService` into `DashboardController` and `ScheduleController`.

---

### Phase 2: Frontend Components (React + Tailwind + shadcn)
- [x] **Task 5: TypeScript Types & WaterIntakeWidget**
  - **Agent:** `frontend-specialist` | **Skill:** `frontend-architecture`
  - Created `resources/js/types/water.d.ts`.
  - Built `WaterIntakeWidget.tsx` with liquid reservoir animation, quick-add buttons (+250ml, +500ml, +750ml, custom), undo functionality, and coach note banner.

- [x] **Task 6: HydrationCalculatorModal & Breakdown**
  - **Agent:** `frontend-specialist` | **Skill:** `shadcn-ui`
  - Built `HydrationCalculatorModal.tsx` showing the full biometric formula breakdown and custom target override.

- [x] **Task 7: Dashboard & Schedule Layout Integration**
  - **Agent:** `frontend-specialist` | **Skill:** `nextjs-react-expert`
  - Embedded `WaterIntakeWidget` into `resources/js/Pages/Dashboard.tsx` in the "Nutrition & Hydration" tab.
  - Connected `resources/js/Pages/Schedule/Index.tsx` to live database water log and API.
  - Built `WaterWeeklyChart.tsx` with Recharts 7-day adherence chart.

---

## Phase X: Verification Checklist
- [x] PHP syntax check: All modified and new files pass `php -l` with 0 errors
- [x] Service logic verification: Tested Tier 1, Tier 2, Admin override, logging (+500ml), and undo (-500ml)
- [x] TypeScript validation: `npx tsc --noEmit` passed with 0 errors
- [x] Production build: `npm run build` completed successfully

## ✅ PHASE X COMPLETE
- Lint & Types: ✅ Pass (0 errors)
- Build: ✅ Success (`npm run build` compiled all assets)
- Database & Migrations: ✅ Applied successfully
- Backend Tests: ✅ All verification checks passed
- Date: 2026-09-24
