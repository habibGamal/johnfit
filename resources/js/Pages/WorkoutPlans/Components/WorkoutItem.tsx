import { useState } from 'react';
import { Workout } from '@/types';
import {
    Card,
    CardContent,
} from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
import { ExternalLink, Video } from 'lucide-react';
import { router } from '@inertiajs/react';

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
            }
        )
    };

    return (
        <Card className="overflow-hidden border">
            <CardContent className="p-4">
                {/* Top section with image and completion status - always horizontal */}
                <div className="flex items-start justify-between gap-4 mb-3">
                    <div className="flex items-start gap-3">
                        {/* Workout thumbnail */}
                        <div className="h-14 w-14 sm:h-16 sm:w-16 flex-shrink-0 overflow-hidden rounded-md bg-muted">
                            {workout.thumb ? (
                                <img
                                    src={workout.thumb}
                                    alt={workout.name}
                                    className="h-full w-full object-cover"
                                />
                            ) : (
                                <div className="flex h-full w-full items-center justify-center bg-muted-foreground/10 text-muted-foreground/50">
                                    <svg className="h-6 w-6 sm:h-8 sm:w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16m-7 6h7" />
                                    </svg>
                                </div>
                            )}
                        </div>

                        {/* Workout title */}
                        <div className="pt-1">
                            <h4 className="font-medium text-foreground line-clamp-2">{workout.name}</h4>
                        </div>
                    </div>

                    {/* Completion checkbox */}
                    <div className="flex items-center space-x-2 ml-2 flex-shrink-0">
                        <Checkbox
                            id={`workout-${workout.id}`}
                            checked={workout.completed}
                            disabled={isLoading}
                            onCheckedChange={toggleCompletion}
                        />
                        <label
                            htmlFor={`workout-${workout.id}`}
                            className="text-sm font-medium leading-none hidden sm:inline peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                            {workout.completed ? 'Completed' : 'Mark as done'}
                        </label>
                    </div>
                </div>

                {/* Workout details section - stacks on mobile */}
                <div className="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 mt-2">
                    {/* Workout details */}
                    <div className="flex-1 text-sm text-muted-foreground grid grid-cols-2 sm:flex sm:gap-4">
                        <p><span className="font-medium">Reps:</span> {workout.reps}</p>
                        <p><span className="font-medium">Muscles:</span> {workout.muscles}</p>
                        {workout.tools && <p className="col-span-2 sm:col-span-1"><span className="font-medium">Tools:</span> {workout.tools}</p>}
                    </div>

                    {/* Video link */}
                    {workout.video_url && (
                        <Button
                            variant="outline"
                            size="sm"
                            className="mt-2 sm:mt-0 flex-shrink-0 text-sm"
                            asChild
                        >
                            <a href={workout.video_url} target="_blank" rel="noopener noreferrer">
                                <Video className="mr-1.5 h-3.5 w-3.5" />
                                Watch Video
                            </a>
                        </Button>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}
