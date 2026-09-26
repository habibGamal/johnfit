// Achievement Badges & Streaks Types
import { PageProps } from '@/types';

export type BadgeTier = 'bronze' | 'silver' | 'gold' | 'platinum' | 'diamond';

export interface BadgeRequirementView {
    metric: string;
    metric_label: string;
    operator: 'gte' | 'lte';
    threshold: number;
}

export interface AchievementBadge {
    badge_id: number;
    slug: string;
    name: string;
    description: string | null;
    icon: string;
    tier: BadgeTier;
    category: string;
    sort_order: number;
    earned: boolean;
    progress: number;
    per_metric: Record<string, number | null>;
    requirements: BadgeRequirementView[];
    unlocked: boolean;
    unlocked_at: string | null;
}

export interface AchievementJourney {
    badges: AchievementBadge[];
    unlocked_count: number;
    total_count: number;
    points: {
        workout_points: number;
        meal_points: number;
        hydration_points: number;
        total_points: number;
        level: number;
    };
    scores: {
        workout?: number | null;
        meal?: number | null;
        hydration?: number | null;
        total?: number | null;
        level?: number | null;
        workout_points?: number | null;
        meal_points?: number | null;
        hydration_points?: number | null;
        total_points?: number | null;
    };
    streaks: Record<string, number>;
}

export interface AchievementPageProps extends PageProps {
    journey: AchievementJourney;
}
