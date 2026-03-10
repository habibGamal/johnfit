import { SVGProps } from 'react';

export interface FitnessScoreData {
    total_score: number;
    level: string;
    trend: 'up' | 'down' | 'stable' | null;
    period: {
        start: string;
        end: string;
        days: number;
    };
    components: {
        workout: {
            score: number;
            weight: number;
            metrics: {
                completion_rate: number;
                volume_progression: number;
                streak_days: number;
                total_sets: number;
                completed_sets: number;
                current_volume: number;
                previous_volume: number;
            };
        };
        meal: {
            score: number;
            weight: number;
            metrics: {
                completion_rate: number;
                consistency: number;
                quantity_accuracy: number;
                total_meals: number;
                completed_meals: number;
                perfect_days: number;
                total_days: number;
            };
        };
        inbody: {
            score: number;
            weight: number;
            metrics: {
                smm_change: number;
                pbf_change: number;
                classification: string;
                muscle_trend: string;
                fat_trend: string;
                status: string;
                raw_progress_score: number;
            };
        } | null;
    };
    updated_at: string;
}

export interface FitnessScoreHistory {
    date: string;
    fullDate: string;
    total_score: number;
    workout_score: number;
    meal_score: number;
    inbody_score: number | null;
    level: string;
}

export interface FitnessScoreWidgetProps {
    data?: FitnessScoreData;
    isLoading?: boolean;
}

export interface FitnessScoreTrendProps {
    history?: FitnessScoreHistory[];
    weeks?: number;
    isLoading?: boolean;
}
