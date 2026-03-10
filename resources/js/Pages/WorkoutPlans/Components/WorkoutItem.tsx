import { useState } from 'react';
import { Workout } from '@/types';
import { Button } from '@/Components/ui/button';
import { Video, Dumbbell, Repeat, ChevronDown, ChevronUp, Target } from 'lucide-react';
import { cn } from '@/lib/utils';
import WorkoutSetTracker from './WorkoutSetTracker';
import { Badge } from '@/Components/ui/badge';

interface WorkoutItemProps {
    workout: Workout;
    workoutPlanId: number;
    day: string;
    isLocked?: boolean;
}

export default function WorkoutItem({ workout, workoutPlanId, day, isLocked = false }: WorkoutItemProps) {
    const [isExpanded, setIsExpanded] = useState<boolean>(false);
    const [localCompletedCount, setLocalCompletedCount] = useState<number>(0);

    const handleSetChange = () => {
        setLocalCompletedCount(prev => prev + 1);
    };

    const completedSets = workout.sets?.filter(s => s.completed).length || 0;
    const totalSets = workout.sets?.length || (workout.reps_preset?.length || 3);

    return (
        <div className={cn(
            "group relative overflow-hidden rounded-xl border transition-all duration-300",
            workout.completed
                ? "bg-zinc-900/40 border-green-500/20"
                : isExpanded
                    ? "bg-zinc-900/80 border-yellow-500/50 shadow-lg shadow-yellow-500/10"
                    : "bg-zinc-900/40 border-white/5 hover:border-white/10"
        )}>
            {/* Completion Overlay Effect */}
            <div className={cn(
                "absolute inset-0 bg-gradient-to-r from-green-500/5 to-transparent pointer-events-none transition-opacity duration-300",
                workout.completed ? "opacity-100" : "opacity-0"
            )} />

            {/* Collapsible Header */}
            <button
                onClick={() => setIsExpanded(!isExpanded)}
                className="w-full p-4 relative z-10 text-left outline-none"
            >
                <div className="flex items-start gap-4">
                    {/* Thumbnail */}
                    <div className="relative h-16 w-16 sm:h-20 sm:w-20 flex-shrink-0 overflow-hidden rounded-lg bg-zinc-950 border border-white/10 shadow-inner">
                        {workout.thumb ? (
                            <img
                                src={workout.thumb}
                                alt={workout.name}
                                className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110 opacity-80 group-hover:opacity-100"
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
                                className="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity hover:bg-black/60 group-hover:opacity-100 sm:hidden"
                            >
                                <Video className="h-6 w-6 text-white drop-shadow-md" />
                            </a>
                        )}
                    </div>

                    <div className="flex-1 min-w-0 py-0.5">
                        <div className="flex items-start justify-between gap-2">
                            <div>
                                <h4 className={cn(
                                    "font-bold text-base leading-tight md:text-lg transition-colors uppercase tracking-wide",
                                    workout.completed ? "text-muted-foreground line-through decoration-green-500/50" : isExpanded ? "text-yellow-500" : "text-white"
                                )}>
                                    {workout.name}
                                </h4>
                                {workout.muscles && (
                                    <div className="flex items-center gap-1.5 mt-1.5">
                                        <Badge variant="outline" className="border-white/5 bg-white/5 text-muted-foreground text-[10px] uppercase tracking-wider px-1.5 py-0">
                                            {workout.muscles}
                                        </Badge>
                                    </div>
                                )}
                            </div>

                            <div className="flex items-center gap-2">
                                {/* Sets Progress Indicator */}
                                <div className={cn(
                                    "px-2.5 py-1 rounded-md text-xs font-bold font-mono tracking-wider border",
                                    completedSets === totalSets && completedSets > 0
                                        ? "bg-green-500/10 text-green-500 border-green-500/20"
                                        : completedSets > 0
                                            ? "bg-yellow-500/10 text-yellow-500 border-yellow-500/20"
                                            : "bg-zinc-800 text-muted-foreground border-white/5"
                                )}>
                                    {completedSets}/{totalSets}
                                </div>

                                {/* Expand/Collapse Icon */}
                                <div className={cn(
                                    "rounded-full p-1 transition-colors",
                                    isExpanded ? "bg-yellow-500/10 text-yellow-500" : "text-muted-foreground group-hover:text-white"
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
                            <div className="mt-3 flex flex-wrap gap-2 text-sm">
                                <div className="flex items-center gap-1.5 px-2 py-1">
                                    <Target className="h-3.5 w-3.5 text-yellow-500" />
                                    <span className="font-bold text-white text-xs uppercase tracking-wide">{workout.reps} <span className="text-muted-foreground font-medium normal-case">reps</span></span>
                                </div>
                                {workout.tools && (
                                    <div className="flex items-center gap-1.5 px-2 py-1 border-l border-white/10 pl-3">
                                        <Dumbbell className="h-3.5 w-3.5 text-blue-500" />
                                        <span className="font-bold text-white text-xs uppercase tracking-wide truncate max-w-[100px]">{workout.tools}</span>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </button>

            {/* Expanded Set Tracker */}
            {isExpanded && (
                <div className="px-4 pb-4 sm:px-5 sm:pb-5 relative z-10 border-t border-white/5">
                    <div className="pt-4">
                        <WorkoutSetTracker
                            workout={workout}
                            workoutPlanId={workoutPlanId}
                            day={day}
                            onSetChange={handleSetChange}
                            isLocked={isLocked}
                        />
                    </div>

                    {/* PC Video Link */}
                    {workout.video_url && (
                        <div className="mt-4 hidden sm:block">
                            <Button
                                variant="ghost"
                                size="sm"
                                className="h-9 w-full justify-start text-muted-foreground hover:text-white hover:bg-white/5"
                                asChild
                            >
                                <a href={workout.video_url} target="_blank" rel="noopener noreferrer" className="flex items-center gap-2">
                                    <Video className="h-4 w-4 text-red-500" />
                                    Watch Demonstration
                                </a>
                            </Button>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
