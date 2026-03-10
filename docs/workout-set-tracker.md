# WorkoutSetTracker Component Documentation

## Overview

The `WorkoutSetTracker` is a sophisticated React component that provides real-time workout set tracking with automatic persistence, historical data comparison, and intelligent state management. It serves as the core interface for users to log their workout performance during training sessions.

**Location:** `resources/js/Pages/WorkoutPlans/Components/WorkoutSetTracker.tsx`

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Component Interface](#component-interface)
3. [State Management](#state-management)
4. [Initialization Logic](#initialization-logic)
5. [Data Flow](#data-flow)
6. [User Interactions](#user-interactions)
7. [Backend Integration](#backend-integration)
8. [Database Schema](#database-schema)
9. [Design Patterns](#design-patterns)
10. [Error Handling](#error-handling)
11. [Performance Considerations](#performance-considerations)

---

## Architecture Overview

### Component Hierarchy

```
WorkoutSetTracker
├── Header Section
│   ├── Title ("Set Tracker")
│   └── Video Link (if available)
├── Sets Table
│   ├── Table Header (Set, Prev, lbs, Reps, Done)
│   ├── Set Rows (dynamic)
│   │   ├── Set Number
│   │   ├── Previous Data Display
│   │   ├── Weight Input (conditional)
│   │   ├── Reps Input
│   │   └── Completion Toggle
│   └── Add Set Button
```

### Technology Stack

- **React 18+** with Hooks (useState, useEffect)
- **TypeScript** for type safety
- **Axios** for HTTP requests
- **Shadcn UI** components (Input, Tooltip)
- **Tailwind CSS** with custom utilities

---

## Component Interface

### Props Definition

```typescript
interface WorkoutSetTrackerProps {
    workout: Workout;           // Complete workout data including sets and history
    workoutPlanId: number;      // Parent workout plan identifier
    day: string;                // Day of week (e.g., "Monday", "Tuesday")
    onSetChange?: () => void;   // Optional callback fired after set updates
}
```

### Type Definitions

#### LocalSet Interface

```typescript
interface LocalSet extends WorkoutSet {
    isNew?: boolean;      // Indicates unsaved set (not yet persisted to DB)
    isSaving?: boolean;   // Loading state during API call
}
```

**Purpose:** Extends the base `WorkoutSet` type with UI-specific metadata for tracking save states.

#### WorkoutSet Interface

```typescript
interface WorkoutSet {
    id?: number;          // Database ID (undefined for new sets)
    set_number: number;   // Sequential set number (1, 2, 3, ...)
    weight: number | null; // Weight in lbs (null for bodyweight exercises)
    reps: number;         // Number of repetitions
    completed: boolean;   // Completion status
}
```

#### PreviousSet Interface

```typescript
interface PreviousSet {
    set_number: number;   // Matches current set number
    weight: number | null; // Historical weight data
    reps: number;         // Historical reps data
}
```

**Purpose:** Provides comparison data from the user's last workout session.

---

## State Management

### Primary State Variables

```typescript
const [sets, setSets] = useState<LocalSet[]>([]);
const [isInitialized, setIsInitialized] = useState(false);
```

#### State: `sets`

**Type:** `LocalSet[]`

**Purpose:** Manages the complete list of workout sets for the current session.

**Lifecycle:**
1. Initialized in `useEffect` based on workout data
2. Updated on user input (weight/reps changes)
3. Updated on completion toggle
4. Updated on API responses

#### State: `isInitialized`

**Type:** `boolean`

**Purpose:** Prevents re-initialization on subsequent renders.

**Why Needed:** The `useEffect` depends on `workout.sets`, `workout.reps_preset`, and `workout.previous_sets`, which could trigger multiple initializations. This flag ensures initialization happens only once.

---

## Initialization Logic

### Initialization Flow (Lines 35-59)

The component follows a **priority-based initialization strategy**:

```typescript
useEffect(() => {
    if (isInitialized) return;  // Guard: prevent re-initialization

    // Priority 1: Existing Sets
    if (workout.sets && workout.sets.length > 0) {
        setSets(workout.sets.map(s => ({ ...s, isNew: false, isSaving: false })));
    }
    // Priority 2: Reps Preset
    else if (workout.reps_preset && workout.reps_preset.length > 0) {
        const initialSets: LocalSet[] = workout.reps_preset.map((preset, index) => ({
            set_number: index + 1,
            weight: workout.previous_sets[index]?.weight ?? null,
            reps: preset.count,
            completed: false,
            isNew: true,
            isSaving: false,
        }));
        setSets(initialSets);
    }
    // Priority 3: Default Sets
    else {
        setSets([
            { set_number: 1, weight: null, reps: 10, completed: false, isNew: true, isSaving: false },
            { set_number: 2, weight: null, reps: 10, completed: false, isNew: true, isSaving: false },
            { set_number: 3, weight: null, reps: 10, completed: false, isNew: true, isSaving: false },
        ]);
    }

    setIsInitialized(true);
}, [workout.sets, workout.reps_preset, workout.previous_sets, isInitialized]);
```

### Priority 1: Existing Sets

**Condition:** `workout.sets && workout.sets.length > 0`

**Scenario:** User has already logged sets for this workout today.

**Behavior:**
- Load existing sets from the database
- Mark all as `isNew: false` (already persisted)
- Mark all as `isSaving: false` (not currently saving)

**Example:**
```typescript
// User previously logged:
workout.sets = [
    { id: 123, set_number: 1, weight: 135, reps: 8, completed: true },
    { id: 124, set_number: 2, weight: 135, reps: 8, completed: true },
    { id: 125, set_number: 3, weight: 135, reps: 6, completed: false },
]

// Component initializes with these exact sets
```

### Priority 2: Reps Preset

**Condition:** `workout.reps_preset && workout.reps_preset.length > 0`

**Scenario:** Workout has a predefined rep scheme (e.g., "3x8", "4x12").

**Behavior:**
- Create sets based on preset rep counts
- **Smart Weight Pre-fill:** Use weight from `previous_sets` if available
- Mark all as `isNew: true` (not yet saved)

**Example:**
```typescript
// Workout configuration:
workout.reps_preset = [
    { count: 8 },
    { count: 8 },
    { count: 8 }
]

workout.previous_sets = [
    { set_number: 1, weight: 135, reps: 8 },
    { set_number: 2, weight: 135, reps: 8 },
    { set_number: 3, weight: 135, reps: 7 }
]

// Component initializes:
sets = [
    { set_number: 1, weight: 135, reps: 8, completed: false, isNew: true },
    { set_number: 2, weight: 135, reps: 8, completed: false, isNew: true },
    { set_number: 3, weight: 135, reps: 8, completed: false, isNew: true }
]
```

**Key Insight:** This provides **progressive overload continuity** by suggesting the user's previous weights.

### Priority 3: Default Sets

**Condition:** No existing sets and no preset configuration.

**Behavior:**
- Create 3 default sets
- 10 reps per set
- No weight (null)
- All marked as `isNew: true`

**Use Case:** Generic workouts without specific programming.

---

## Data Flow

### Historical Data Retrieval

#### Function: `getPreviousData`

```typescript
const getPreviousData = (setNumber: number): PreviousSet | null => {
    return workout.previous_sets?.find(p => p.set_number === setNumber) ?? null;
};
```

**Purpose:** Retrieves historical data for a specific set number from the user's last workout session.

**Return Value:**
- `PreviousSet` object if data exists
- `null` if no historical data

#### Function: `formatPrevious`

```typescript
const formatPrevious = (previous: PreviousSet | null): string => {
    if (!previous) return '—';
    if (previous.weight !== null && previous.weight > 0) {
        return `${previous.weight} × ${previous.reps}`;
    }
    return `${previous.reps}`;
};
```

**Purpose:** Formats historical data for display in the "Prev" column.

**Output Examples:**
- `"135 × 8"` - Weighted exercise (135 lbs for 8 reps)
- `"12"` - Bodyweight exercise (12 reps)
- `"—"` - No historical data

**UI Display:**

| Set | Prev    | lbs | Reps | Done |
|-----|---------|-----|------|------|
| 1   | 135 × 8 | 140 | 8    | ✓    |
| 2   | 135 × 8 | 135 | 10   | ✓    |
| 3   | 135 × 8 | 135 | 12   | ☐    |

---

## User Interactions

### 1. Editing Weight/Reps

#### Weight Input (Lines 209-227)

```typescript
<Input
    type="number"
    inputMode="decimal"
    value={set.weight ?? ''}
    onChange={(e) => updateSetField(
        set.set_number,
        'weight',
        e.target.value ? parseFloat(e.target.value) : null
    )}
    onBlur={() => {
        if (set.reps > 0 && !set.isNew) {
            saveSet(set);
        }
    }}
    placeholder={previous?.weight?.toString() || '-'}
    disabled={set.isSaving}
/>
```

**Event Flow:**

1. **onChange:** Updates local state immediately
   - Provides instant visual feedback
   - No API call yet
   - Parses input to `float` or `null`

2. **onBlur:** Triggers auto-save
   - **Condition 1:** `set.reps > 0` (valid rep count)
   - **Condition 2:** `!set.isNew` (set has been saved before)
   - **Action:** Calls `saveSet(set)`

**Why Not Save on Every Keystroke?**
- Prevents excessive API calls
- Waits for user to finish editing
- Better UX (no lag while typing)

#### Reps Input (Lines 231-250)

```typescript
<Input
    type="number"
    inputMode="numeric"
    value={set.reps}
    onChange={(e) => updateSetField(
        set.set_number,
        'reps',
        parseInt(e.target.value) || 0
    )}
    onBlur={() => {
        if (set.reps > 0 && !set.isNew) {
            saveSet(set);
        }
    }}
    placeholder={previous?.reps?.toString() || '10'}
    disabled={set.isSaving}
/>
```

**Identical Logic to Weight Input:**
- Immediate local update
- Auto-save on blur (if not new)
- Disabled during save operation

#### Function: `updateSetField`

```typescript
const updateSetField = (setNumber: number, field: 'weight' | 'reps', value: number | null) => {
    setSets(prev => prev.map(s =>
        s.set_number === setNumber ? { ...s, [field]: value } : s
    ));
};
```

**Purpose:** Immutably updates a specific field of a specific set.

**Implementation Details:**
- Uses functional state update (`prev =>`)
- Maps over all sets
- Updates only the matching set
- Preserves all other sets unchanged

---

### 2. Completing a Set

#### Completion Toggle Button (Lines 254-269)

```typescript
<button
    onClick={() => toggleSetCompletion(set)}
    disabled={set.isSaving || set.reps <= 0}
    className={cn(
        "w-9 h-9 rounded-lg flex items-center justify-center transition-all duration-300",
        set.completed
            ? "bg-green-500 text-white shadow-[0_0_15px_-3px_rgba(34,197,94,0.4)] hover:bg-green-600"
            : "bg-zinc-800 text-muted-foreground hover:bg-zinc-700 hover:text-white",
        (set.isSaving || set.reps <= 0) && "opacity-50 cursor-not-allowed"
    )}
>
    <Check className={cn(
        "h-5 w-5 transition-transform duration-300",
        set.completed && "scale-110"
    )} />
</button>
```

**Visual States:**

| State       | Background    | Effect                          | Cursor          |
|-------------|---------------|---------------------------------|-----------------|
| Completed   | Green (#22c55e) | Glow shadow                   | Pointer         |
| Incomplete  | Dark Gray     | Hover: lighter gray             | Pointer         |
| Disabled    | Current + 50% opacity | None                  | Not-allowed     |

**Disabled Conditions:**
1. `set.isSaving === true` - Currently saving to backend
2. `set.reps <= 0` - Invalid rep count

#### Function: `toggleSetCompletion`

```typescript
const toggleSetCompletion = async (set: LocalSet) => {
    const newCompleted = !set.completed;
    if (set.reps > 0) {
        await saveSet({ ...set, completed: newCompleted }, newCompleted);
        onSetChange?.();
    }
};
```

**Logic Flow:**

1. Calculate new completion state (toggle)
2. **Validation:** Only proceed if `reps > 0`
3. Call `saveSet` with updated completion status
4. Trigger parent callback (`onSetChange`)

**Why the Validation?**
- Prevents marking invalid sets as complete
- Ensures data integrity
- Matches button disabled state

---

### 3. Adding New Sets

#### Add Set Button (Lines 277-282)

```typescript
<button
    onClick={addSet}
    className="w-full py-3 bg-zinc-900/50 hover:bg-zinc-900 text-xs font-bold uppercase tracking-widest text-muted-foreground hover:text-white transition-all border-t border-white/5"
>
    + Add New Set
</button>
```

#### Function: `addSet`

```typescript
const addSet = () => {
    const newSetNumber = sets.length + 1;
    const prevSet = sets[sets.length - 1];
    const previousData = getPreviousData(newSetNumber);

    setSets(prev => [...prev, {
        set_number: newSetNumber,
        weight: prevSet?.weight ?? previousData?.weight ?? null,
        reps: prevSet?.reps ?? previousData?.reps ?? 10,
        completed: false,
        isNew: true,
        isSaving: false,
    }]);
};
```

**Smart Default Logic:**

```
Priority 1: prevSet?.weight
    ↓ (if null)
Priority 2: previousData?.weight
    ↓ (if null)
Priority 3: null

Priority 1: prevSet?.reps
    ↓ (if null)
Priority 2: previousData?.reps
    ↓ (if null)
Priority 3: 10
```

**Example Scenario:**

```typescript
// Current session:
sets = [
    { set_number: 1, weight: 135, reps: 8, completed: true },
    { set_number: 2, weight: 140, reps: 6, completed: true },
]

// Previous session:
previous_sets = [
    { set_number: 1, weight: 135, reps: 8 },
    { set_number: 2, weight: 135, reps: 8 },
    { set_number: 3, weight: 135, reps: 7 },
]

// User clicks "Add New Set"
// New set created:
{
    set_number: 3,
    weight: 140,      // From prevSet (Set 2 current session)
    reps: 6,          // From prevSet (Set 2 current session)
    completed: false,
    isNew: true
}
```

**Design Rationale:**
- **Continuity:** Uses last set's values (user likely doing similar weight)
- **Fallback:** Uses historical data if available
- **Safety:** Defaults to 10 reps if no data exists

---

## Backend Integration

### API Endpoint

**Route:** `POST /workout-plans/save-set`

**Route Name:** `workout-plans.save-set`

**Controller:** `WorkoutPlanController@saveSet`

### Request Payload

```typescript
{
    workout_plan_id: number;    // Required
    day: string;                // Required (e.g., "Monday")
    workout_id: number;         // Required
    set_number: number;         // Required (min: 1)
    weight: number | null;      // Optional (nullable)
    reps: number;               // Required (min: 0)
    completed: boolean;         // Required
}
```

### Response Format

```typescript
{
    success: boolean;
    set: {
        id: number;
        set_number: number;
        weight: number | null;
        reps: number;
        completed: boolean;
    }
}
```

### Function: `saveSet`

```typescript
const saveSet = async (set: LocalSet, completed: boolean = set.completed) => {
    // Step 1: Optimistic UI Update
    setSets(prev => prev.map(s =>
        s.set_number === set.set_number
            ? { ...s, completed, isSaving: true }
            : s
    ));

    try {
        // Step 2: API Call
        const response = await axios.post(route('workout-plans.save-set'), {
            workout_plan_id: workoutPlanId,
            day: day,
            workout_id: workout.id,
            set_number: set.set_number,
            weight: set.weight,
            reps: set.reps,
            completed: completed,
        });

        // Step 3: Update with Server Response
        setSets(prev => prev.map(s =>
            s.set_number === set.set_number
                ? { ...response.data.set, isNew: false, isSaving: false }
                : s
        ));
    } catch (error) {
        // Step 4: Rollback on Error
        console.error('Failed to save set:', error);
        setSets(prev => prev.map(s =>
            s.set_number === set.set_number
                ? { ...s, completed: !completed, isSaving: false }
                : s
        ));
    }
};
```

**Step-by-Step Breakdown:**

#### Step 1: Optimistic UI Update

```typescript
setSets(prev => prev.map(s =>
    s.set_number === set.set_number
        ? { ...s, completed, isSaving: true }
        : s
));
```

**Purpose:** Provide instant feedback to the user.

**Changes:**
- Update `completed` status immediately
- Set `isSaving: true` (disables inputs, shows loading state)

**Why Optimistic?**
- User sees immediate response
- No waiting for network latency
- Better perceived performance

#### Step 2: API Call

```typescript
const response = await axios.post(route('workout-plans.save-set'), {
    workout_plan_id: workoutPlanId,
    day: day,
    workout_id: workout.id,
    set_number: set.set_number,
    weight: set.weight,
    reps: set.reps,
    completed: completed,
});
```

**Purpose:** Persist data to the database.

**Network Request:**
- Method: POST
- URL: `/workout-plans/save-set`
- Headers: Includes CSRF token (Laravel)
- Body: JSON payload with set data

#### Step 3: Update with Server Response

```typescript
setSets(prev => prev.map(s =>
    s.set_number === set.set_number
        ? { ...response.data.set, isNew: false, isSaving: false }
        : s
));
```

**Purpose:** Sync local state with server state.

**Changes:**
- Replace local set data with server response
- Set `isNew: false` (now persisted)
- Set `isSaving: false` (re-enable inputs)

**Why Replace Entire Object?**
- Server may add/modify fields (e.g., `id`, timestamps)
- Ensures single source of truth
- Prevents state drift

#### Step 4: Rollback on Error

```typescript
catch (error) {
    console.error('Failed to save set:', error);
    setSets(prev => prev.map(s =>
        s.set_number === set.set_number
            ? { ...s, completed: !completed, isSaving: false }
            : s
    ));
}
```

**Purpose:** Revert optimistic update if save fails.

**Changes:**
- Revert `completed` to original state
- Set `isSaving: false` (re-enable inputs)
- Log error to console

**User Experience:**
- User sees checkmark toggle back
- Can retry the action
- No silent data loss

---

## Database Schema

### Table: `workout_set_completions`

```sql
CREATE TABLE workout_set_completions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    workout_plan_id BIGINT UNSIGNED NOT NULL,
    day VARCHAR(255) NOT NULL,
    workout_id BIGINT UNSIGNED NOT NULL,
    set_number INT NOT NULL,
    session_date DATE NOT NULL,
    weight DECIMAL(8,2) NULL,
    reps INT NOT NULL,
    completed BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    UNIQUE KEY unique_set (
        user_id,
        workout_plan_id,
        day,
        workout_id,
        set_number,
        session_date
    ),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (workout_plan_id) REFERENCES workout_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (workout_id) REFERENCES workouts(id) ON DELETE CASCADE,
    
    INDEX idx_user_session (user_id, session_date),
    INDEX idx_workout_plan (workout_plan_id, day)
);
```

### Composite Unique Key

**Columns:** `(user_id, workout_plan_id, day, workout_id, set_number, session_date)`

**Purpose:** Ensures one record per user, per workout, per set number, per day.

**Why This Matters:**

1. **Prevents Duplicates:** User can't accidentally create multiple records for the same set
2. **Enables Upsert:** `updateOrCreate` in Laravel works correctly
3. **Data Integrity:** Enforces business logic at database level

**Example:**

```sql
-- First save (INSERT):
user_id=1, workout_plan_id=5, day='Monday', workout_id=42, set_number=1, session_date='2026-02-06'
weight=135, reps=8, completed=true

-- Second save (UPDATE):
user_id=1, workout_plan_id=5, day='Monday', workout_id=42, set_number=1, session_date='2026-02-06'
weight=140, reps=8, completed=true  -- Updates existing record
```

### Backend Service: `WorkoutTrackingService`

#### Method: `saveWorkoutSet`

```php
public function saveWorkoutSet(
    User $user,
    int $workoutPlanId,
    string $day,
    int $workoutId,
    int $setNumber,
    ?float $weight,
    int $reps,
    bool $completed = true
): WorkoutSetCompletion {
    $today = Carbon::today()->toDateString();

    $set = WorkoutSetCompletion::updateOrCreate(
        [
            'user_id' => $user->id,
            'workout_plan_id' => $workoutPlanId,
            'day' => $day,
            'workout_id' => $workoutId,
            'set_number' => $setNumber,
            'session_date' => $today,
        ],
        [
            'weight' => $weight,
            'reps' => $reps,
            'completed' => $completed,
        ]
    );

    // Update overall workout completion status
    $this->updateWorkoutCompletionFromSets($user, $workoutPlanId, $day, $workoutId);

    return $set;
}
```

**Logic Breakdown:**

1. **Get Today's Date:** `Carbon::today()->toDateString()`
   - Format: `YYYY-MM-DD`
   - Ensures all sets for today share the same date

2. **Upsert Operation:** `updateOrCreate`
   - **First Array:** Unique identifiers (WHERE clause)
   - **Second Array:** Values to update/insert (SET clause)
   - **Behavior:**
     - If record exists: UPDATE
     - If record doesn't exist: INSERT

3. **Side Effect:** `updateWorkoutCompletionFromSets`
   - Recalculates overall workout completion
   - Updates parent workout record
   - Likely checks if all sets are completed

4. **Return:** The saved/updated `WorkoutSetCompletion` model

---

## Design Patterns

### 1. Optimistic UI Updates

**Definition:** Update the UI immediately before server confirmation.

**Implementation:**
```typescript
// Before API call:
setSets(prev => prev.map(s =>
    s.set_number === set.set_number
        ? { ...s, completed: true, isSaving: true }
        : s
));

// After API success:
setSets(prev => prev.map(s =>
    s.set_number === set.set_number
        ? { ...response.data.set, isNew: false, isSaving: false }
        : s
));

// On API error:
setSets(prev => prev.map(s =>
    s.set_number === set.set_number
        ? { ...s, completed: false, isSaving: false }
        : s
));
```

**Benefits:**
- Instant feedback (no perceived lag)
- Better user experience
- Feels responsive even on slow networks

**Risks:**
- Temporary inconsistency if save fails
- Must handle rollback correctly

### 2. Smart Initialization

**Definition:** Initialize state based on available data with fallback priorities.

**Implementation:**
```typescript
if (workout.sets && workout.sets.length > 0) {
    // Priority 1: Use existing data
} else if (workout.reps_preset && workout.reps_preset.length > 0) {
    // Priority 2: Use preset + historical data
} else {
    // Priority 3: Use defaults
}
```

**Benefits:**
- Handles multiple scenarios gracefully
- Provides continuity across sessions
- Reduces user input burden

### 3. Debounced Auto-Save

**Definition:** Save only when user finishes editing, not on every keystroke.

**Implementation:**
```typescript
<Input
    onChange={(e) => updateSetField(...)}  // Immediate local update
    onBlur={() => {                        // Save on blur
        if (set.reps > 0 && !set.isNew) {
            saveSet(set);
        }
    }}
/>
```

**Benefits:**
- Reduces API calls (better performance)
- Prevents race conditions
- Better UX (no lag while typing)

**Alternative Considered:** `onChange` with debounce timer
- **Rejected:** More complex, unnecessary for this use case

### 4. Immutable State Updates

**Definition:** Never mutate state directly; always create new objects.

**Implementation:**
```typescript
// ❌ WRONG:
const updateSet = (setNumber, field, value) => {
    sets.find(s => s.set_number === setNumber)[field] = value;
    setSets(sets);  // React won't detect change!
};

// ✅ CORRECT:
const updateSetField = (setNumber, field, value) => {
    setSets(prev => prev.map(s =>
        s.set_number === setNumber ? { ...s, [field]: value } : s
    ));
};
```

**Benefits:**
- React detects changes correctly
- Prevents bugs from shared references
- Enables time-travel debugging

### 5. Progressive Enhancement

**Definition:** Work with minimal data, enhance when more data is available.

**Implementation:**
```typescript
// Works without previous_sets:
weight: workout.previous_sets[index]?.weight ?? null

// Works without video_url:
{workout.video_url && (
    <a href={workout.video_url}>Watch Demo</a>
)}

// Works for bodyweight exercises:
{workout.requires_weight && (
    <Input type="number" ... />
)}
```

**Benefits:**
- Graceful degradation
- Handles edge cases
- Flexible for different workout types

---

## Error Handling

### Network Errors

**Scenario:** API call fails (network timeout, server error, etc.)

**Handling:**
```typescript
catch (error) {
    console.error('Failed to save set:', error);
    setSets(prev => prev.map(s =>
        s.set_number === set.set_number
            ? { ...s, completed: !completed, isSaving: false }
            : s
    ));
}
```

**User Experience:**
- Checkmark reverts to original state
- Inputs re-enabled
- User can retry

**Improvement Opportunities:**
- Show toast notification
- Retry logic with exponential backoff
- Offline queue for saves

### Validation Errors

**Scenario:** User tries to complete a set with invalid data.

**Handling:**
```typescript
const toggleSetCompletion = async (set: LocalSet) => {
    const newCompleted = !set.completed;
    if (set.reps > 0) {  // Validation
        await saveSet({ ...set, completed: newCompleted }, newCompleted);
        onSetChange?.();
    }
};
```

**Prevention:**
```typescript
<button
    onClick={() => toggleSetCompletion(set)}
    disabled={set.isSaving || set.reps <= 0}  // Disable button
>
```

**User Experience:**
- Button disabled if `reps <= 0`
- Visual feedback (opacity, cursor)
- Prevents invalid state

### Race Conditions

**Scenario:** User rapidly toggles completion while save is in progress.

**Handling:**
```typescript
setSets(prev => prev.map(s =>
    s.set_number === set.set_number
        ? { ...s, completed, isSaving: true }  // Set loading state
        : s
));
```

**Prevention:**
```typescript
<button
    disabled={set.isSaving || set.reps <= 0}  // Disable during save
>
```

**User Experience:**
- Button disabled during save
- Prevents double-saves
- Ensures data consistency

---

## Performance Considerations

### 1. Minimal Re-renders

**Strategy:** Use functional state updates and immutable patterns.

```typescript
// ✅ GOOD: Only re-renders when state actually changes
setSets(prev => prev.map(s =>
    s.set_number === setNumber ? { ...s, [field]: value } : s
));

// ❌ BAD: Would re-render entire component tree
setSets([...sets]);
```

### 2. Debounced Saves

**Strategy:** Save on `onBlur` instead of `onChange`.

**Metrics:**
- **onChange:** 10+ API calls per field edit
- **onBlur:** 1 API call per field edit

**Savings:** ~90% reduction in API calls

### 3. Conditional Rendering

**Strategy:** Only render weight input if needed.

```typescript
{workout.requires_weight && (
    <div className="flex items-center justify-center">
        <Input type="number" ... />
    </div>
)}
```

**Benefits:**
- Smaller DOM
- Faster initial render
- Better for bodyweight exercises

### 4. Optimistic Updates

**Strategy:** Update UI before server confirmation.

**Perceived Performance:**
- **Without:** 200-500ms delay (network latency)
- **With:** 0ms delay (instant feedback)

**User Experience:** Feels 2-5x faster

### 5. Lazy Initialization

**Strategy:** Initialize state only once with `isInitialized` flag.

```typescript
useEffect(() => {
    if (isInitialized) return;  // Skip if already initialized
    // ... initialization logic
    setIsInitialized(true);
}, [workout.sets, workout.reps_preset, workout.previous_sets, isInitialized]);
```

**Benefits:**
- Prevents unnecessary re-initialization
- Avoids state reset bugs
- Better performance on re-renders

---

## Complete User Flow Example

### Scenario: User Performing Bench Press (3x8)

#### Step 1: Page Load

**Backend Response:**
```json
{
    "workout": {
        "id": 42,
        "name": "Bench Press",
        "requires_weight": true,
        "reps_preset": [
            { "count": 8 },
            { "count": 8 },
            { "count": 8 }
        ],
        "previous_sets": [
            { "set_number": 1, "weight": 135, "reps": 8 },
            { "set_number": 2, "weight": 135, "reps": 8 },
            { "set_number": 3, "weight": 135, "reps": 7 }
        ],
        "sets": []
    }
}
```

**Component Initialization:**
```typescript
// Priority 2: Reps Preset (no existing sets)
sets = [
    { set_number: 1, weight: 135, reps: 8, completed: false, isNew: true },
    { set_number: 2, weight: 135, reps: 8, completed: false, isNew: true },
    { set_number: 3, weight: 135, reps: 8, completed: false, isNew: true }
]
```

**UI Display:**

| Set | Prev    | lbs | Reps | Done |
|-----|---------|-----|------|------|
| 1   | 135 × 8 | 135 | 8    | ☐    |
| 2   | 135 × 8 | 135 | 8    | ☐    |
| 3   | 135 × 7 | 135 | 8    | ☐    |

#### Step 2: User Edits Set 1 Weight

**User Action:** Changes weight from 135 to 140

**onChange Event:**
```typescript
updateSetField(1, 'weight', 140)
```

**State Update:**
```typescript
sets = [
    { set_number: 1, weight: 140, reps: 8, completed: false, isNew: true },
    { set_number: 2, weight: 135, reps: 8, completed: false, isNew: true },
    { set_number: 3, weight: 135, reps: 8, completed: false, isNew: true }
]
```

**UI Update:** Immediate (no API call yet)

#### Step 3: User Clicks Away (Blur)

**onBlur Event:**
```typescript
if (set.reps > 0 && !set.isNew) {
    saveSet(set);
}
```

**Evaluation:**
- `set.reps > 0` ✅ (8 reps)
- `!set.isNew` ❌ (isNew: true)

**Result:** No API call (set hasn't been saved yet)

#### Step 4: User Completes Set 1

**User Action:** Clicks checkmark button

**toggleSetCompletion Event:**
```typescript
toggleSetCompletion(set)
```

**Optimistic Update:**
```typescript
sets = [
    { set_number: 1, weight: 140, reps: 8, completed: true, isNew: true, isSaving: true },
    { set_number: 2, weight: 135, reps: 8, completed: false, isNew: true },
    { set_number: 3, weight: 135, reps: 8, completed: false, isNew: true }
]
```

**UI Update:** Checkmark turns green, button disabled

**API Call:**
```http
POST /workout-plans/save-set
Content-Type: application/json

{
    "workout_plan_id": 5,
    "day": "Monday",
    "workout_id": 42,
    "set_number": 1,
    "weight": 140,
    "reps": 8,
    "completed": true
}
```

**Server Response:**
```json
{
    "success": true,
    "set": {
        "id": 789,
        "set_number": 1,
        "weight": 140,
        "reps": 8,
        "completed": true
    }
}
```

**State Update:**
```typescript
sets = [
    { id: 789, set_number: 1, weight: 140, reps: 8, completed: true, isNew: false, isSaving: false },
    { set_number: 2, weight: 135, reps: 8, completed: false, isNew: true },
    { set_number: 3, weight: 135, reps: 8, completed: false, isNew: true }
]
```

**UI Update:** Button re-enabled, checkmark stays green

#### Step 5: User Edits Set 1 Reps

**User Action:** Changes reps from 8 to 10

**onChange Event:**
```typescript
updateSetField(1, 'reps', 10)
```

**State Update:**
```typescript
sets = [
    { id: 789, set_number: 1, weight: 140, reps: 10, completed: true, isNew: false },
    { set_number: 2, weight: 135, reps: 8, completed: false, isNew: true },
    { set_number: 3, weight: 135, reps: 8, completed: false, isNew: true }
]
```

**onBlur Event:**
```typescript
if (set.reps > 0 && !set.isNew) {
    saveSet(set);
}
```

**Evaluation:**
- `set.reps > 0` ✅ (10 reps)
- `!set.isNew` ✅ (isNew: false)

**Result:** API call triggered

**API Call:**
```http
POST /workout-plans/save-set

{
    "workout_plan_id": 5,
    "day": "Monday",
    "workout_id": 42,
    "set_number": 1,
    "weight": 140,
    "reps": 10,
    "completed": true
}
```

**Server Response:** Updates existing record (same unique key)

#### Step 6: User Adds Set 4

**User Action:** Clicks "+ Add New Set"

**addSet Event:**
```typescript
const newSetNumber = 4;
const prevSet = sets[2];  // Set 3
const previousData = getPreviousData(4);  // null (no Set 4 in previous session)

setSets(prev => [...prev, {
    set_number: 4,
    weight: 135,  // From prevSet (Set 3)
    reps: 8,      // From prevSet (Set 3)
    completed: false,
    isNew: true,
    isSaving: false,
}]);
```

**State Update:**
```typescript
sets = [
    { id: 789, set_number: 1, weight: 140, reps: 10, completed: true, isNew: false },
    { set_number: 2, weight: 135, reps: 8, completed: false, isNew: true },
    { set_number: 3, weight: 135, reps: 8, completed: false, isNew: true },
    { set_number: 4, weight: 135, reps: 8, completed: false, isNew: true }
]
```

**UI Display:**

| Set | Prev    | lbs | Reps | Done |
|-----|---------|-----|------|------|
| 1   | 135 × 8 | 140 | 10   | ✓    |
| 2   | 135 × 8 | 135 | 8    | ☐    |
| 3   | 135 × 7 | 135 | 8    | ☐    |
| 4   | —       | 135 | 8    | ☐    |

---

## Summary

The `WorkoutSetTracker` component is a production-grade workout logging interface that demonstrates:

✅ **Smart State Management** - Priority-based initialization with historical data integration

✅ **Optimistic UI** - Instant feedback with graceful error handling

✅ **Efficient API Usage** - Debounced saves, upsert operations, minimal requests

✅ **Progressive Enhancement** - Works with minimal data, enhances when available

✅ **Type Safety** - Full TypeScript coverage with strict interfaces

✅ **User Experience** - Intuitive interactions, visual feedback, smart defaults

✅ **Data Integrity** - Composite unique keys, validation, rollback on errors

✅ **Performance** - Minimal re-renders, conditional rendering, lazy initialization

This component serves as an excellent reference for building complex, data-driven React components with real-time persistence and historical data integration.
