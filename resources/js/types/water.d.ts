export interface UserWaterEntry {
    id: number;
    water_log_id: number;
    amount_ml: number;
    container_type: 'cup' | 'bottle' | 'shaker' | 'custom';
    logged_at: string;
    created_at: string;
}

export interface UserDailyWaterLog {
    id: number;
    user_id: number;
    date: string;
    target_ml: number;
    consumed_ml: number;
    custom_target_ml: number | null;
    is_completed: boolean;
    completed_at: string | null;
    effective_target_ml: number;
    percentage: number;
    remaining_ml: number;
    entries: UserWaterEntry[];
}

export interface WaterCalculation {
    target_ml: number;
    formula_tier: 'admin_fixed' | 'admin_multiplier' | 'tier_2_inbody' | 'tier_1_base';
    tier_name: string;
    base_ml: number;
    workout_bonus_ml: number;
    weight_kg?: number;
    admin_notes?: string | null;
    is_admin_override: boolean;
    allow_user_override: boolean;
}

export interface WaterDayHistory {
    date: string;
    full_date: string;
    day_name: string;
    target_ml: number;
    consumed_ml: number;
    percentage: number;
    is_completed: boolean;
}

export interface WaterWeeklyStats {
    history: WaterDayHistory[];
    total_consumed_ml: number;
    average_daily_ml: number;
    completed_days: number;
    completion_rate: number;
}

export interface WaterData {
    log: UserDailyWaterLog;
    calculation: WaterCalculation;
    weekly_stats: WaterWeeklyStats;
}
