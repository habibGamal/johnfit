import { router } from '@inertiajs/react';
import { UserDailyItem } from '@/types';
import { Card, CardContent } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Checkbox } from '@/Components/ui/checkbox';
import { Dumbbell, CheckCircle2, Video, ChevronRight, Layers } from 'lucide-react';
import { cn } from '@/lib/utils';

interface WorkoutItemCardProps {
    item: UserDailyItem;
    isLocked: boolean;
    onOpenSetsModal: (item: UserDailyItem) => void;
}

export default function WorkoutItemCard({
    item,
    isLocked,
    onOpenSetsModal,
}: WorkoutItemCardProps) {
    const handleToggle = (e: React.MouseEvent) => {
        e.stopPropagation();
        if (isLocked) return;

        router.post(
            route('schedule.items.toggle', item.id),
            { completed: !item.is_completed },
            { preserveScroll: true, preserveState: true }
        );
    };

    const targetDetails = item.target_details || {};
    const options = (targetDetails.options || []) as any[];
    const hasOptions = options.length > 1;

    const selectedWorkoutId = item.execution_payload?.selected_workout_id || item.reference_id;
    const activeOption = options.find((opt: any) => opt.workout_id === selectedWorkoutId) || options[0] || {};

    const activeMuscles = activeOption.muscles || targetDetails.muscles || '';
    const muscles = Array.isArray(activeMuscles)
        ? activeMuscles.join(', ')
        : activeMuscles;

    const setsCount = activeOption.sets_count || targetDetails.sets_count || 3;
    const targetReps = activeOption.target_reps || targetDetails.target_reps || [];
    const setsDescription = targetReps.length > 0
        ? `${setsCount} sets × ${targetReps.join('-')} reps`
        : `${setsCount} sets`;

    // Sets logged in execution payload
    const loggedSets = item.execution_payload?.sets || [];
    const completedSetsCount = loggedSets.filter((s: any) => s.completed).length;

    return (
        <Card
            onClick={() => onOpenSetsModal(item)}
            className={cn(
                'group relative cursor-pointer border transition-all duration-200 hover:shadow-md overflow-hidden',
                item.is_completed
                    ? 'bg-primary/[0.03] border-primary/40 hover:border-primary/60'
                    : 'bg-card border-border hover:border-primary/40'
            )}
        >
            <CardContent className="p-4 flex items-center justify-between gap-3">
                {/* Left: Checkbox + Exercise Info */}
                <div className="flex items-center gap-3.5 min-w-0">
                    <button
                        type="button"
                        disabled={isLocked}
                        onClick={handleToggle}
                        className={cn(
                            'h-7 w-7 rounded-lg flex items-center justify-center transition-colors shrink-0',
                            item.is_completed
                                ? 'bg-primary text-primary-foreground shadow-sm'
                                : 'border-2 border-border hover:border-primary/60 bg-secondary/50 text-transparent'
                        )}
                        aria-label={item.is_completed ? 'Mark incomplete' : 'Mark complete'}
                    >
                        <CheckCircle2 className="h-4 w-4 fill-current stroke-background" />
                    </button>

                    <div className="space-y-1 min-w-0">
                        <div className="flex items-center gap-2 flex-wrap">
                            <h4
                                className={cn(
                                    'text-sm sm:text-base font-bold truncate text-foreground',
                                    item.is_completed && 'line-through text-muted-foreground font-semibold'
                                )}
                            >
                                {item.item_name}
                            </h4>
                            <Badge variant="outline" className="text-[10px] px-1.5 py-0 font-medium text-muted-foreground">
                                +{item.points} pts
                            </Badge>
                            {hasOptions && (
                                <Badge variant="secondary" className="text-[10px] px-1.5 py-0 font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20">
                                    ⚡ {options.length} Options (OR)
                                </Badge>
                            )}
                        </div>

                        <div className="flex items-center gap-2 text-xs text-muted-foreground flex-wrap">
                            <span className="flex items-center gap-1 font-medium text-foreground/80">
                                <Layers className="h-3 w-3 text-primary" /> {setsDescription}
                            </span>
                            {muscles && (
                                <>
                                    <span>•</span>
                                    <span className="truncate max-w-[140px]">{muscles}</span>
                                </>
                            )}
                            {loggedSets.length > 0 && (
                                <>
                                    <span>•</span>
                                    <span className="text-primary font-medium">
                                        {completedSetsCount}/{loggedSets.length} sets done
                                    </span>
                                </>
                            )}
                        </div>
                    </div>
                </div>

                {/* Right: Action / Open sets button */}
                <div className="flex items-center gap-2 shrink-0">
                    <Button
                        variant="ghost"
                        size="sm"
                        className="h-8 text-xs font-semibold gap-1 text-muted-foreground group-hover:text-primary group-hover:bg-primary/10"
                    >
                        <span className="hidden sm:inline">Log Sets</span>
                        <ChevronRight className="h-4 w-4" />
                    </Button>
                </div>
            </CardContent>
        </Card>
    );
}
