import { SVGProps } from 'react';

export interface PointsBreakdown {
    target: number;
    earned: number;
}

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
            metrics: PointsBreakdown;
        };
        meal: {
            score: number;
            weight: number;
            metrics: PointsBreakdown;
        };
        inbody: {
            score: number;
            weight: number;
            metrics: { earned_points: number };
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
