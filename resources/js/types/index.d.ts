// Types for authentication and user
export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

// Types for workout plans and related interfaces

// User and authentication types
export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export interface Auth {
    user: User;
}

export interface PageProps<T extends Record<string, any> = {}> extends T {
    auth: Auth;
    [key: string]: any;
}

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
    workout?: string;
    meal?: string;
    plan_name: string;
    completed_at: string;
}

export interface MealRecentActivity {
    day: string;
    meal: string;
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

export interface ComparisonStats {
    this_week: number;
    last_week: number;
    percentage_change: number;
    trend: 'up' | 'down' | 'neutral';
}

export interface Achievement {
    id: string;
    title: string;
    description: string;
    icon: string;
    condition?: boolean;
    progress: number;
    tier: 'bronze' | 'silver' | 'gold';
    unlocked: boolean;
}

export interface MacroDistributionItem {
    name: string;
    value: number;
    color: string;
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
    comparisonStats: ComparisonStats;
    achievements: Achievement[];
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
    recentActivity: MealRecentActivity[];
    progressOverTime: {
        date: string;
        count: number;
    }[];
    aggregateStats: AchievementStats;
    nutritionAverages: NutritionAverages;
    comparisonStats: ComparisonStats;
    macroDistribution: MacroDistributionItem[];
}

// Page props interface
export interface PageProps<T extends Record<string, unknown> = Record<string, unknown>> extends T {
    auth: {
        user: User;
    };
}
