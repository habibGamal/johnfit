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
    reps_preset?: RepsPresetItem[] | null;
    muscles: string;
    tools: string;
    thumb: string;
    video_url: string;
    completed: boolean;
    requires_weight: boolean;
    sets: WorkoutSet[];
    previous_sets: PreviousSet[];
}

export interface WorkoutSet {
    id?: number;
    set_number: number;
    weight: number | null;
    reps: number;
    completed: boolean;
}

export interface PreviousSet {
    set_number: number;
    weight: number | null;
    reps: number;
}

export interface RepsPresetItem {
    count: number;
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

// InBody Log types
export interface InBodyLog {
    id: number;
    weight: number;
    smm: number;
    pbf: number;
    bmi: number;
    bmr: number;
    body_water: number | null;
    lean_body_mass: number | null;
    visceral_fat: number | null;
    waist_hip_ratio: number | null;
    measured_at: string;
    measured_at_formatted: string;
    notes: string | null;
}

export interface InBodyDelta {
    weight: InBodyChangeMetric;
    smm: InBodyChangeMetric;
    pbf: InBodyChangeMetric;
    bmi: InBodyChangeMetric;
    bmr: InBodyChangeMetric;
    body_water: InBodyChangeMetric | null;
    lean_body_mass: InBodyChangeMetric | null;
    visceral_fat: InBodyChangeMetric | null;
    days_between: number;
    weekly_rate: {
        weight: number;
        smm: number;
        pbf: number;
    } | null;
}

export interface InBodyChangeMetric {
    current: number;
    previous: number;
    absolute: number;
    percentage: number;
    trend: 'up' | 'down' | 'stable';
}

export interface InBodyIndicators {
    muscle_trend: 'gaining' | 'losing' | 'maintaining';
    fat_trend: 'gaining' | 'losing' | 'maintaining';
    weight_trend: 'gaining' | 'losing' | 'stable';
    smm_change_kg: number;
    pbf_change_pct: number;
    weight_change_kg: number;
}

export interface InBodyAnalysisResult {
    status: 'excellent' | 'positive' | 'neutral' | 'negative';
    classification: 'recomposition' | 'lean_bulk' | 'bulk' | 'cutting' | 'aggressive_cut' | 'fat_gain' | 'muscle_loss' | 'maintenance';
    description: string;
    indicators: InBodyIndicators;
    score: number;
    recommendations: string[];
}

export interface InBodyTrends {
    insufficient_data?: boolean;
    period_days?: number;
    total_entries?: number;
    overall?: {
        weight: InBodyChangeMetric;
        smm: InBodyChangeMetric;
        pbf: InBodyChangeMetric;
        bmi: InBodyChangeMetric;
    };
    average_per_week?: {
        weight: number;
        smm: number;
        pbf: number;
    } | null;
}

export interface InBodyStatistic {
    min: number;
    max: number;
    avg: number;
    current: number;
}

export interface InBodyStatistics {
    weight: InBodyStatistic;
    smm: InBodyStatistic;
    pbf: InBodyStatistic;
    bmi: InBodyStatistic;
    bmr: InBodyStatistic;
}

export interface InBodyHistoryEntry {
    date: string;
    fullDate: string;
    weight: number;
    smm: number;
    pbf: number;
    bmi: number;
    bmr: number;
}

export interface InBodyAnalysis {
    latest: InBodyLog | null;
    previous: InBodyLog | null;
    delta: InBodyDelta | null;
    bodyCompositionAnalysis: InBodyAnalysisResult | null;
    trends: InBodyTrends;
    statistics: InBodyStatistics | null;
    history: InBodyHistoryEntry[];
}

// Progression & Analytics Types
export interface ProgressionChartDataPoint {
    date: string;
    volume: number | null;
    estimated_1rm: number | null;
    body_weight: number | null;
}

export interface MuscleHeatmapItem {
    muscle: string;
    volume: number;
    percentage: number;
}

export interface IntensityDelta {
    current_volume: number;
    previous_volume: number;
    delta_percentage: number;
    trend: 'up' | 'down' | 'stable';
}

export interface ConsistencyScore {
    completed_sessions: number;
    target_sessions: number;
    percentage: number;
    weeks: number;
}

export interface PersonalBestDetails {
    new_record: number;
    previous_record: number;
    improvement: number;
    improvement_percentage: number;
}

export interface PersonalBests {
    has_volume_pb: boolean;
    has_strength_pb: boolean;
    volume_pb_details: PersonalBestDetails | null;
    strength_pb_details: PersonalBestDetails | null;
    today_volume: number;
    today_max_1rm: number;
}

export interface VolumeDataPoint {
    session_date: string;
    total_volume: number;
    total_sets: number;
    total_reps: number;
}

export interface OneRmDataPoint {
    date: string;
    estimated_1rm: number;
    best_weight: number | null;
    best_reps: number | null;
}

export interface RelativeStrengthDataPoint extends OneRmDataPoint {
    body_weight: number | null;
    relative_strength: number | null;
}

export interface WorkoutAnalytics {
    workout_id: number;
    workout_name: string;
    muscles: string[];
    volume_trend: VolumeDataPoint[];
    one_rm_trend: OneRmDataPoint[];
    personal_bests: PersonalBests;
    relative_strength_trend: RelativeStrengthDataPoint[];
}

export interface InBodyTrendPoint {
    date: string;
    weight: number;
    smm: number;
    pbf: number;
}

export interface AnalyticsWorkout {
    id: number;
    name: string;
    muscles: string[];
}

export interface ProgressionDashboardProps {
    workouts: AnalyticsWorkout[];
    selectedWorkoutId: number | null;
    chartData: ProgressionChartDataPoint[];
    muscleHeatmap: MuscleHeatmapItem[];
    intensityDelta: IntensityDelta;
    consistencyScore: ConsistencyScore;
    workoutAnalytics: WorkoutAnalytics[];
    inbodyTrend: InBodyTrendPoint[];
}
