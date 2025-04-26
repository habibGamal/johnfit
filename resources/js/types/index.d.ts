// filepath: e:\johnfit\resources\js\types\index.d.ts
// Types for workout plans and related interfaces
export interface Workout {
    id: number;
    name: string;
    reps: string;
    muscles: string;
    tools: string;
    thumb: string;
    video_url: string;
    completed: boolean;
}

export interface WorkoutDay {
    day: string;
    workouts: Workout[];
}

export interface WorkoutPlan {
    id: number;
    name: string;
    plan: WorkoutDay[];
}

// Dashboard statistics types
export interface WeeklyCompletionRate {
    completed: number;
    total: number;
    percentage: number;
}

export interface RecentActivity {
    day: string;
    workout: string;
    plan_name: string;
    completed_at: string;
}

// Meal plan types
export interface Meal {
    id: number;
    name: string;
    quantity: number | string;
    calories: number;
    protein: number;
    carbs: number;
    fat: number;
    completed: boolean;
}

export interface MealTimeSlot {
    time: string;
    meals: Meal[];
}

export interface MealDay {
    day: string;
    meals: Meal[];
    timeSlots: MealTimeSlot[];
}

export interface MealPlan {
    id: number;
    name: string;
    plan: MealDay[];
}

export interface AchievementStats {
    total_completions: number;
    active_plans: number;
    recent_completions: number;
}

export interface WorkoutStats {
    weeklyCompletionRate: WeeklyCompletionRate;
    currentStreak: number;
    mostActiveDays: Record<string, number>;
    recentActivity: RecentActivity[];
    progressOverTime: {
        date: string;
        count: number;
    }[];
    aggregateStats: AchievementStats;
}

export interface NutritionAverages {
    calories: number;
    protein: number;
    carbs: number;
    fat: number;
}

export interface MealStats {
    weeklyCompletionRate: WeeklyCompletionRate;
    currentStreak: number;
    mostActiveDays: Record<string, number>;
    recentActivity: RecentActivity[];
    progressOverTime: {
        date: string;
        count: number;
    }[];
    aggregateStats: AchievementStats;
    nutritionAverages: NutritionAverages;
}
