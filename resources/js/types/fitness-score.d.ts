export interface PointCategory {
    points: number;
    label: string;
    icon: string;
}

export interface PointsSummaryData {
    level: number;
    title: string;
    total_points: number;
    level_min_points: number;
    level_max_points: number;
    points_in_level: number;
    points_needed_in_level: number;
    points_to_next_level: number;
    progress_percent: number;
    components: {
        workout: PointCategory;
        meal: PointCategory;
        hydration: PointCategory;
    };
    updated_at: string;
}

// Alias for backwards-compatibility in existing dashboard imports
export type FitnessScoreData = PointsSummaryData;

export interface PointsHistoryItem {
    date: string;
    dayName?: string;
    fullDate: string;
    isToday?: boolean;
    workout_points: number;
    meal_points: number;
    hydration_points: number;
    points_earned: number;
    total_points: number;
    total_score?: number;
    level: number;
}

// Alias for backwards-compatibility
export type FitnessScoreHistory = PointsHistoryItem;

export interface FitnessScoreWidgetProps {
    data?: PointsSummaryData;
    isLoading?: boolean;
}

export interface FitnessScoreTrendProps {
    history?: PointsHistoryItem[];
    weeks?: number;
    isLoading?: boolean;
}
