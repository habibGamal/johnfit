import { useState } from 'react';
import { Workout } from '@/types';
import { Button } from '@/Components/ui/button';
import { Video, Dumbbell, Repeat, ChevronDown, ChevronUp } from 'lucide-react';
import { router } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import WorkoutSetTracker from './WorkoutSetTracker';

interface WorkoutItemProps {
    workout: Workout;
    workoutPlanId: number;
    day: string;
}

export default function WorkoutItem({ workout, workoutPlanId, day }: WorkoutItemProps) {
    const [isExpanded, setIsExpanded] = useState<boolean>(false);
    const [localCompletedCount, setLocalCompletedCount] = useState<number>(0);

    const handleSetChange = () => {
        // Don't reload immediately - let the local state handle updates
        // Only reload when user collapses or navigates away
        setLocalCompletedCount(prev => prev + 1);
    };

    const completedSets = workout.sets?.filter(s => s.completed).length || 0;
    const totalSets = workout.sets?.length || (workout.reps_preset?.length || 3);

    return (
        <div className={cn(
            "group relative overflow-hidden rounded-xl border bg-card text-card-foreground shadow-sm transition-all",
            workout.completed && "bg-muted/30 border-primary/20",
            isExpanded && "shadow-lg"
        )}>
            {/* Completion Overlay Effect */}
            <div className={cn(
                "absolute inset-0 bg-primary/5 pointer-events-none transition-opacity duration-300",
                workout.completed ? "opacity-100" : "opacity-0"
            )} />

            {/* Collapsible Header */}
            <button
                onClick={() => setIsExpanded(!isExpanded)}
                className="w-full p-4 sm:p-5 relative z-10 text-left"
            >
                <div className="flex items-start gap-4">
                    {/* Thumbnail */}
                    <div className="relative h-16 w-16 sm:h-20 sm:w-20 flex-shrink-0 overflow-hidden rounded-lg bg-muted shadow-sm ring-1 ring-border/50">
                        {workout.thumb ? (
                            <img
                                src={workout.thumb}
                                alt={workout.name}
                                className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                            />
                        ) : (
                            <div className="flex h-full w-full items-center justify-center text-muted-foreground/50">
                                <Dumbbell className="h-6 w-6 sm:h-8 sm:w-8" />
                            </div>
                        )}
                        {/* Mobile Video Play Button Overlay */}
                        {workout.video_url && (
                            <a
                                href={workout.video_url}
                                target="_blank"
                                rel="noopener noreferrer"
                                onClick={(e) => e.stopPropagation()}
                                className="absolute inset-0 flex items-center justify-center bg-black/20 opacity-0 transition-opacity hover:bg-black/40 group-hover:opacity-100 sm:hidden"
                            >
                                <Video className="h-6 w-6 text-white drop-shadow-md" />
                            </a>
                        )}
                    </div>

                    <div className="flex-1 min-w-0 py-0.5">
                        <div className="flex items-start justify-between gap-2">
                            <div>
                                <h4 className={cn(
                                    "font-semibold text-base leading-tight md:text-lg transition-colors",
                                    workout.completed ? "text-muted-foreground" : "text-foreground"
                                )}>
                                    {workout.name}
                                </h4>
                                {workout.muscles && (
                                    <p className="mt-1 text-xs text-muted-foreground line-clamp-1">
                                        Target: {workout.muscles}
                                    </p>
                                )}
                            </div>

                            <div className="flex items-center gap-2">
                                {/* Sets Progress Indicator */}
                                <div className={cn(
                                    "px-2 py-1 rounded-md text-xs font-medium",
                                    completedSets === totalSets && completedSets > 0
                                        ? "bg-green-500/20 text-green-600 dark:text-green-400"
                                        : completedSets > 0
                                            ? "bg-primary/10 text-primary"
                                            : "bg-muted text-muted-foreground"
                                )}>
                                    {completedSets}/{totalSets}
                                </div>

                                {/* Expand/Collapse Icon */}
                                <div className={cn(
                                    "rounded-full p-1 transition-colors",
                                    isExpanded ? "bg-primary/10 text-primary" : "text-muted-foreground"
                                )}>
                                    {isExpanded ? (
                                        <ChevronUp className="h-5 w-5" />
                                    ) : (
                                        <ChevronDown className="h-5 w-5" />
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Metrics Grid - Collapsed View */}
                        {!isExpanded && (
                            <div className="mt-2 flex flex-wrap gap-2 text-sm">
                                <div className="flex items-center gap-1.5 rounded-md bg-muted/50 px-2 py-1">
                                    <Repeat className="h-3.5 w-3.5 text-muted-foreground" />
                                    <span className="font-medium text-foreground">{workout.reps}</span>
                                    <span className="text-xs text-muted-foreground">target</span>
                                </div>
                                {workout.tools && (
                                    <div className="flex items-center gap-1.5 rounded-md bg-muted/50 px-2 py-1">
                                        <span className="text-xs text-muted-foreground">with</span>
                                        <span className="font-medium text-foreground truncate max-w-[100px]">{workout.tools}</span>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </button>

            {/* Expanded Set Tracker */}
            {isExpanded && (
                <div className="px-4 pb-4 sm:px-5 sm:pb-5 relative z-10 border-t border-border/50">
                    <div className="pt-4">
                        <WorkoutSetTracker
                            workout={workout}
                            workoutPlanId={workoutPlanId}
                            day={day}
                            onSetChange={handleSetChange}
                        />
                    </div>

                    {/* PC Video Link */}
                    {workout.video_url && (
                        <div className="mt-4 hidden sm:block">
                            <Button
                                variant="ghost"
                                size="sm"
                                className="h-8 w-full justify-start text-muted-foreground hover:text-primary"
                                asChild
                            >
                                <a href={workout.video_url} target="_blank" rel="noopener noreferrer">
                                    <Video className="mr-2 h-3.5 w-3.5" />
                                    Watch Demonstration Video
                                </a>
                            </Button>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
