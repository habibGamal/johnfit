import { useState } from 'react';
import { Workout } from '@/types';
import {
    Card,
    CardContent,
} from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
import { ExternalLink, Video, Dumbbell, Repeat, CheckCircle2, Circle } from 'lucide-react';
import { router } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import { Badge } from '@/Components/ui/badge';

interface WorkoutItemProps {
    workout: Workout;
    workoutPlanId: number;
    day: string;
}

export default function WorkoutItem({ workout, workoutPlanId, day }: WorkoutItemProps) {
    const [isLoading, setIsLoading] = useState<boolean>(false);

    const toggleCompletion = async () => {
        router.post(route('workout-plans.toggle-completion'), {
            workout_plan_id: workoutPlanId,
            day: day,
            workout_id: workout.id,
        },
            {
                onStart: () => setIsLoading(true),
                onFinish: () => setIsLoading(false),
                preserveScroll: true,
            }
        )
    };

    return (
        <div className={cn(
            "group relative overflow-hidden rounded-xl border bg-card text-card-foreground shadow-sm transition-all hover:shadow-md",
            workout.completed && "bg-muted/30 border-primary/20"
        )}>
            {/* Completion Overlay Effect */}
            <div className={cn(
                "absolute inset-0 bg-primary/5 pointer-events-none transition-opacity duration-300",
                workout.completed ? "opacity-100" : "opacity-0"
            )} />

            <div className="p-4 sm:p-5 relative z-10">
                <div className="flex items-start gap-4">
                    {/* Thumbnail */}
                    <div className="relative h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg bg-muted shadow-sm ring-1 ring-border/50">
                        {workout.thumb ? (
                            <img
                                src={workout.thumb}
                                alt={workout.name}
                                className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                            />
                        ) : (
                            <div className="flex h-full w-full items-center justify-center text-muted-foreground/50">
                                <Dumbbell className="h-8 w-8" />
                            </div>
                        )}
                        {/* Mobile Video Play Button Overlay */}
                        {workout.video_url && (
                            <a
                                href={workout.video_url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="absolute inset-0 flex items-center justify-center bg-black/20 opacity-0 transition-opacity hover:bg-black/40 group-hover:opacity-100 sm:hidden"
                            >
                                <Video className="h-8 w-8 text-white drop-shadow-md" />
                            </a>
                        )}
                    </div>

                    <div className="flex-1 min-w-0 py-0.5">
                        <div className="flex items-start justify-between gap-2">
                            <div>
                                <h4 className={cn(
                                    "font-semibold text-base leading-tight md:text-lg transition-colors",
                                    workout.completed ? "text-muted-foreground line-through decoration-primary/50 decoration-2" : "text-foreground"
                                )}>
                                    {workout.name}
                                </h4>
                                {workout.muscles && (
                                    <p className="mt-1 text-xs text-muted-foreground line-clamp-1">
                                        Target: {workout.muscles}
                                    </p>
                                )}
                            </div>

                            <button
                                onClick={toggleCompletion}
                                disabled={isLoading}
                                className={cn(
                                    "flex-shrink-0 rounded-full transition-all focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2",
                                    isLoading && "opacity-50 cursor-not-allowed"
                                )}
                            >
                                {workout.completed ? (
                                    <CheckCircle2 className="h-7 w-7 text-primary fill-primary/10" />
                                ) : (
                                    <Circle className="h-7 w-7 text-muted-foreground hover:text-primary transition-colors" />
                                )}
                            </button>
                        </div>

                        {/* Metrics Grid */}
                        <div className="mt-3 grid grid-cols-2 gap-2 text-sm sm:flex sm:gap-6">
                            <div className="flex items-center gap-1.5 rounded-md bg-muted/50 px-2 py-1">
                                <Repeat className="h-3.5 w-3.5 text-muted-foreground" />
                                <span className="font-medium text-foreground">{workout.reps}</span>
                                <span className="text-xs text-muted-foreground">reps</span>
                            </div>
                            {workout.tools && (
                                <div className="col-span-1 flex items-center gap-1.5 rounded-md bg-muted/50 px-2 py-1 line-clamp-1">
                                    <span className="text-xs text-muted-foreground">with</span>
                                    <span className="font-medium text-foreground truncate">{workout.tools}</span>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* PC Video Link - Only visible on desktop/hover actions usually, but let's make it actionable */}
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
        </div>
    );
}
