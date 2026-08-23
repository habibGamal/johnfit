import { UserDailySchedule } from '@/types';
import { Flame, Lock, Trophy, CheckCircle, Sparkles } from 'lucide-react';
import { Progress } from '@/Components/ui/progress';
import { Badge } from '@/Components/ui/badge';
import { cn } from '@/lib/utils';

interface ScheduleScoreBannerProps {
    schedule: UserDailySchedule | null;
    selectedDate: string;
    currentStreak: number;
}

export default function ScheduleScoreBanner({
    schedule,
    selectedDate,
    currentStreak,
}: ScheduleScoreBannerProps) {
    const formattedDate = new Date(selectedDate).toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'short',
        day: 'numeric',
    });

    const isToday = new Date().toISOString().split('T')[0] === selectedDate;
    const targetScore = schedule?.target_score ?? 0;
    const earnedScore = schedule?.earned_score ?? 0;
    const adherence = schedule?.adherence_percentage ?? 0;
    const isLocked = schedule?.is_locked ?? false;
    const isCompleted = schedule?.is_completed ?? false;

    return (
        <div className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-card via-card to-secondary/30 border border-border p-5 shadow-sm">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                {/* Left: Date & Status Badges */}
                <div className="space-y-1.5">
                    <div className="flex items-center gap-2 flex-wrap">
                        <h2 className="text-xl font-bold tracking-tight text-foreground">{formattedDate}</h2>
                        {isToday && (
                            <Badge variant="default" className="bg-primary/90 text-primary-foreground text-xs">
                                Today
                            </Badge>
                        )}
                        {isLocked && (
                            <Badge variant="outline" className="text-amber-500 border-amber-500/30 gap-1 text-xs">
                                <Lock className="h-3 w-3" /> Locked
                            </Badge>
                        )}
                        {isCompleted && (
                            <Badge variant="outline" className="text-emerald-500 border-emerald-500/30 gap-1 text-xs">
                                <CheckCircle className="h-3 w-3" /> Target Met
                            </Badge>
                        )}
                    </div>
                    <p className="text-xs sm:text-sm text-muted-foreground">
                        {isLocked
                            ? 'This past schedule is locked to preserve historical progress records.'
                            : targetScore > 0
                            ? 'Complete your scheduled workouts and meals to reach 100% adherence.'
                            : 'No plan targets scheduled for this date.'}
                    </p>
                </div>

                {/* Right: Quick Streak & Points Summary */}
                <div className="flex items-center gap-3 shrink-0">
                    {/* Streak Badge */}
                    <div className="flex items-center gap-2 bg-secondary/80 border border-border/80 px-3.5 py-2 rounded-xl">
                        <div className="h-8 w-8 rounded-lg bg-orange-500/10 flex items-center justify-center text-orange-500">
                            <Flame className="h-5 w-5 fill-orange-500" />
                        </div>
                        <div>
                            <span className="block text-xs text-muted-foreground font-medium">Streak</span>
                            <span className="block text-sm font-bold text-foreground">{currentStreak} Days</span>
                        </div>
                    </div>

                    {/* Score Ratio Badge */}
                    <div className="flex items-center gap-2 bg-secondary/80 border border-border/80 px-3.5 py-2 rounded-xl">
                        <div className="h-8 w-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                            <Trophy className="h-5 w-5" />
                        </div>
                        <div>
                            <span className="block text-xs text-muted-foreground font-medium">Points</span>
                            <span className="block text-sm font-bold text-foreground">
                                {earnedScore} / {targetScore} <span className="text-xs text-muted-foreground">pts</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {/* Adherence Progress Bar */}
            {targetScore > 0 && (
                <div className="mt-4 pt-3 border-t border-border/60 space-y-1.5">
                    <div className="flex justify-between items-center text-xs font-semibold">
                        <span className="text-muted-foreground flex items-center gap-1">
                            <Sparkles className="h-3.5 w-3.5 text-primary" /> Daily Adherence
                        </span>
                        <span className={cn(
                            adherence >= 100 ? 'text-emerald-500' : 'text-primary'
                        )}>
                            {adherence}%
                        </span>
                    </div>
                    <Progress value={Math.min(100, adherence)} className="h-2.5 rounded-full" />
                </div>
            )}
        </div>
    );
}
