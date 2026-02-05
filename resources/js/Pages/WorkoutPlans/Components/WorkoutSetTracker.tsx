import { useState, useEffect, useCallback } from 'react';
import { Workout, WorkoutSet, PreviousSet } from '@/types';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { cn } from '@/lib/utils';
import { Check, Plus, Trash2, Minus, Link2, MoreHorizontal, Video } from 'lucide-react';
import axios from 'axios';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/Components/ui/tooltip';

interface WorkoutSetTrackerProps {
    workout: Workout;
    workoutPlanId: number;
    day: string;
    onSetChange?: () => void;
}

interface LocalSet extends WorkoutSet {
    isNew?: boolean;
    isSaving?: boolean;
}

export default function WorkoutSetTracker({
    workout,
    workoutPlanId,
    day,
    onSetChange,
}: WorkoutSetTrackerProps) {
    const [sets, setSets] = useState<LocalSet[]>([]);
    const [isLoading, setIsLoading] = useState(false);
    const [isInitialized, setIsInitialized] = useState(false);

    // Initialize sets from workout data or create default sets from reps_preset
    useEffect(() => {
        // Only initialize once when component mounts
        if (isInitialized) return;

        if (workout.sets && workout.sets.length > 0) {
            setSets(workout.sets.map(s => ({ ...s, isNew: false, isSaving: false })));
        } else if (workout.reps_preset && workout.reps_preset.length > 0) {
            // Create sets from reps preset as guide
            const initialSets: LocalSet[] = workout.reps_preset.map((preset, index) => ({
                set_number: index + 1,
                weight: workout.previous_sets[index]?.weight ?? null,
                reps: preset.count,
                completed: false,
                isNew: true,
                isSaving: false,
            }));
            setSets(initialSets);
        } else {
            // Default to 3 sets with 10 reps
            setSets([
                { set_number: 1, weight: null, reps: 10, completed: false, isNew: true, isSaving: false },
                { set_number: 2, weight: null, reps: 10, completed: false, isNew: true, isSaving: false },
                { set_number: 3, weight: null, reps: 10, completed: false, isNew: true, isSaving: false },
            ]);
        }

        setIsInitialized(true);
    }, [workout.sets, workout.reps_preset, workout.previous_sets, isInitialized]);

    const getPreviousData = (setNumber: number): PreviousSet | null => {
        return workout.previous_sets?.find(p => p.set_number === setNumber) ?? null;
    };

    const formatPrevious = (previous: PreviousSet | null): string => {
        if (!previous) return '—';
        if (previous.weight !== null && previous.weight > 0) {
            return `${previous.weight} × ${previous.reps}`;
        }
        return `${previous.reps}`;
    };

    const saveSet = async (set: LocalSet, completed: boolean = set.completed) => {
        // Optimistically update UI first
        setSets(prev => prev.map(s =>
            s.set_number === set.set_number
                ? { ...s, completed, isSaving: true }
                : s
        ));

        try {
            const response = await axios.post(route('workout-plans.save-set'), {
                workout_plan_id: workoutPlanId,
                day: day,
                workout_id: workout.id,
                set_number: set.set_number,
                weight: set.weight,
                reps: set.reps,
                completed: completed,
            });

            // Update with server response
            setSets(prev => prev.map(s =>
                s.set_number === set.set_number
                    ? { ...response.data.set, isNew: false, isSaving: false }
                    : s
            ));
        } catch (error) {
            console.error('Failed to save set:', error);
            // Revert optimistic update on error
            setSets(prev => prev.map(s =>
                s.set_number === set.set_number
                    ? { ...s, completed: !completed, isSaving: false }
                    : s
            ));
        }
    };

    const toggleSetCompletion = async (set: LocalSet) => {
        const newCompleted = !set.completed;

        // If set is new or has valid reps, save it
        if (set.reps > 0) {
            await saveSet({ ...set, completed: newCompleted }, newCompleted);
            // Notify parent after successful save
            onSetChange?.();
        }
    };

    const updateSetField = (setNumber: number, field: 'weight' | 'reps', value: number | null) => {
        setSets(prev => prev.map(s =>
            s.set_number === setNumber ? { ...s, [field]: value } : s
        ));
    };

    const addSet = () => {
        const newSetNumber = sets.length + 1;
        const prevSet = sets[sets.length - 1];
        const previousData = getPreviousData(newSetNumber);

        setSets(prev => [...prev, {
            set_number: newSetNumber,
            weight: prevSet?.weight ?? previousData?.weight ?? null,
            reps: prevSet?.reps ?? previousData?.reps ?? 10,
            completed: false,
            isNew: true,
            isSaving: false,
        }]);
    };

    const deleteSet = async (setNumber: number) => {
        const set = sets.find(s => s.set_number === setNumber);

        if (set && !set.isNew && set.id) {
            try {
                await axios.delete(route('workout-plans.delete-set'), {
                    data: {
                        workout_plan_id: workoutPlanId,
                        day: day,
                        workout_id: workout.id,
                        set_number: setNumber,
                    },
                });
                onSetChange?.();
            } catch (error) {
                console.error('Failed to delete set:', error);
            }
        }

        // Renumber remaining sets
        setSets(prev => {
            const filtered = prev.filter(s => s.set_number !== setNumber);
            return filtered.map((s, index) => ({ ...s, set_number: index + 1 }));
        });
    };

    const completedSets = sets.filter(s => s.completed).length;
    const totalSets = sets.length;

    return (
        <div className="space-y-3">
            {/* Header with workout name and actions */}
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                    <h3 className="font-semibold text-primary text-lg">{workout.name}</h3>
                </div>
                <div className="flex items-center gap-2">
                    {workout.video_url && (
                        <TooltipProvider>
                            <Tooltip>
                                <TooltipTrigger asChild>
                                    <a
                                        href={workout.video_url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="p-2 hover:bg-muted rounded-full transition-colors"
                                    >
                                        <Link2 className="h-5 w-5 text-primary" />
                                    </a>
                                </TooltipTrigger>
                                <TooltipContent>Watch Demo Video</TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    )}
                </div>
            </div>

            {/* Sets Table */}
            <div className="rounded-lg overflow-hidden">
                {/* Table Header */}
                <div className={cn(
                    "grid gap-2 px-2 py-2 text-xs font-medium text-muted-foreground uppercase tracking-wider",
                    workout.requires_weight ? "grid-cols-[50px_1fr_1fr_1fr_50px]" : "grid-cols-[50px_1fr_1fr_50px]"
                )}>
                    <div className="text-center">Set</div>
                    <div className="text-center">Previous</div>
                    {workout.requires_weight && <div className="text-center">LB</div>}
                    <div className="text-center">Reps</div>
                    <div className="text-center"><Check className="h-4 w-4 mx-auto" /></div>
                </div>

                {/* Sets Rows */}
                <div className="space-y-2">
                    {sets.map((set) => {
                        const previous = getPreviousData(set.set_number);

                        return (
                            <div
                                key={set.set_number}
                                className={cn(
                                    "grid gap-2 px-2 py-1.5 rounded-lg transition-colors",
                                    set.completed
                                        ? "bg-primary/10 border border-primary/20"
                                        : "bg-muted/50",
                                    workout.requires_weight ? "grid-cols-[50px_1fr_1fr_1fr_50px]" : "grid-cols-[50px_1fr_1fr_50px]"
                                )}
                            >
                                {/* Set Number */}
                                <div className="flex items-center justify-center font-semibold text-foreground">
                                    {set.set_number}
                                </div>

                                {/* Previous */}
                                <div className="flex items-center justify-center text-sm text-muted-foreground">
                                    {formatPrevious(previous)}
                                </div>

                                {/* Weight Input (if required) */}
                                {workout.requires_weight && (
                                    <div className="flex items-center justify-center">
                                        <Input
                                            type="number"
                                            inputMode="decimal"
                                            value={set.weight ?? ''}
                                            onChange={(e) => updateSetField(
                                                set.set_number,
                                                'weight',
                                                e.target.value ? parseFloat(e.target.value) : null
                                            )}
                                            onBlur={() => {
                                                if (set.reps > 0 && !set.isNew) {
                                                    saveSet(set);
                                                }
                                            }}
                                            placeholder={previous?.weight?.toString() || '0'}
                                            className="h-10 w-full text-center bg-background/80 font-medium [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                            disabled={set.isSaving}
                                        />
                                    </div>
                                )}

                                {/* Reps Input */}
                                <div className="flex items-center justify-center">
                                    <Input
                                        type="number"
                                        inputMode="numeric"
                                        value={set.reps}
                                        onChange={(e) => updateSetField(
                                            set.set_number,
                                            'reps',
                                            parseInt(e.target.value) || 0
                                        )}
                                        onBlur={() => {
                                            if (set.reps > 0 && !set.isNew) {
                                                saveSet(set);
                                            }
                                        }}
                                        placeholder={previous?.reps?.toString() || '10'}
                                        className="h-10 w-full text-center bg-background/80 font-medium [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                        disabled={set.isSaving}
                                    />
                                </div>

                                {/* Complete Toggle */}
                                <div className="flex items-center justify-center">
                                    <button
                                        onClick={() => toggleSetCompletion(set)}
                                        disabled={set.isSaving || set.reps <= 0}
                                        className={cn(
                                            "w-10 h-10 rounded-lg flex items-center justify-center transition-all",
                                            set.completed
                                                ? "bg-green-500 text-white shadow-md"
                                                : "bg-muted hover:bg-muted/80 text-muted-foreground",
                                            (set.isSaving || set.reps <= 0) && "opacity-50 cursor-not-allowed"
                                        )}
                                    >
                                        <Check className={cn(
                                            "h-5 w-5 transition-transform",
                                            set.completed && "scale-110"
                                        )} />
                                    </button>
                                </div>
                            </div>
                        );
                    })}
                </div>

                {/* Add Set Button */}
                <button
                    onClick={addSet}
                    className="w-full py-3 text-primary font-semibold text-sm uppercase tracking-wide hover:bg-muted/50 transition-colors rounded-lg mt-2"
                >
                    + Add Set
                </button>
            </div>

            {/* Progress indicator */}
            {totalSets > 0 && (
                <div className="text-center text-sm text-muted-foreground">
                    {completedSets} / {totalSets} sets completed
                </div>
            )}
        </div>
    );
}
