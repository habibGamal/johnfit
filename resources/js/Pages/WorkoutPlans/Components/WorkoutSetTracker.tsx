import { useState, useEffect } from 'react';
import { Workout, WorkoutSet, PreviousSet } from '@/types';
import { Input } from '@/Components/ui/input';
import { cn } from '@/lib/utils';
import { Check, Link2 } from 'lucide-react';
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
    isLocked?: boolean;
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
    isLocked = false,
}: WorkoutSetTrackerProps) {
    const [sets, setSets] = useState<LocalSet[]>([]);
    const [isInitialized, setIsInitialized] = useState(false);

    useEffect(() => {
        if (isInitialized) return;

        if (workout.sets && workout.sets.length > 0) {
            setSets(workout.sets.map(s => ({ ...s, isNew: false, isSaving: false })));
        } else if (workout.reps_preset && workout.reps_preset.length > 0) {
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

            setSets(prev => prev.map(s =>
                s.set_number === set.set_number
                    ? { ...response.data.set, isNew: false, isSaving: false }
                    : s
            ));
        } catch (error) {
            console.error('Failed to save set:', error);
            setSets(prev => prev.map(s =>
                s.set_number === set.set_number
                    ? { ...s, completed: !completed, isSaving: false }
                    : s
            ));
        }
    };

    const toggleSetCompletion = async (set: LocalSet) => {
        const newCompleted = !set.completed;
        if (set.reps > 0) {
            await saveSet({ ...set, completed: newCompleted }, newCompleted);
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

    const completedSets = sets.filter(s => s.completed).length;
    const totalSets = sets.length;

    return (
        <div className="space-y-3">
            {/* Header with workout name and actions */}
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                    <h3 className="font-bold text-white uppercase tracking-wide text-sm">Set Tracker</h3>
                    {isLocked && (
                        <span className="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-green-500/20 text-green-500 border border-green-500/30">
                            Session Completed
                        </span>
                    )}
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
                                        className="p-1.5 hover:bg-white/10 rounded-full transition-colors"
                                    >
                                        <Link2 className="h-4 w-4 text-yello-500" />
                                    </a>
                                </TooltipTrigger>
                                <TooltipContent>Watch Demo Video</TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    )}
                </div>
            </div>

            {/* Sets Table */}
            <div className="rounded-xl overflow-hidden border border-white/5 bg-zinc-950/30">
                {/* Table Header */}
                <div className={cn(
                    "grid gap-2 px-3 py-3 text-[10px] font-bold text-muted-foreground uppercase tracking-widest bg-zinc-900/50",
                    workout.requires_weight ? "grid-cols-[40px_1fr_1fr_1fr_50px]" : "grid-cols-[40px_1fr_1fr_50px]"
                )}>
                    <div className="text-center">Set</div>
                    <div className="text-center">Prev</div>
                    {workout.requires_weight && <div className="text-center">lbs</div>}
                    <div className="text-center">Reps</div>
                    <div className="text-center">Done</div>
                </div>

                {/* Sets Rows */}
                <div className="space-y-0 divide-y divide-white/5">
                    {sets.map((set) => {
                        const previous = getPreviousData(set.set_number);

                        return (
                            <div
                                key={set.set_number}
                                className={cn(
                                    "grid gap-2 px-3 py-2 transition-all duration-300",
                                    set.completed
                                        ? "bg-green-900/10"
                                        : "hover:bg-white/5",
                                    workout.requires_weight ? "grid-cols-[40px_1fr_1fr_1fr_50px]" : "grid-cols-[40px_1fr_1fr_50px]"
                                )}
                            >
                                {/* Set Number */}
                                <div className="flex items-center justify-center font-bold text-xs text-muted-foreground">
                                    {set.set_number}
                                </div>

                                {/* Previous */}
                                <div className="flex items-center justify-center text-xs font-medium text-muted-foreground/70">
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
                                            placeholder={previous?.weight?.toString() || '-'}
                                            className={cn(
                                                "h-9 w-full text-center bg-zinc-900/50 border-white/10 focus:border-yellow-500/50 focus:ring-yellow-500/20 text-white font-bold [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none",
                                                isLocked && "opacity-60 cursor-not-allowed"
                                            )}
                                            disabled={set.isSaving || isLocked}
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
                                        className={cn(
                                            "h-9 w-full text-center bg-zinc-900/50 border-white/10 focus:border-yellow-500/50 focus:ring-yellow-500/20 text-white font-bold [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none",
                                            isLocked && "opacity-60 cursor-not-allowed"
                                        )}
                                        disabled={set.isSaving || isLocked}
                                    />
                                </div>

                                {/* Complete Toggle */}
                                <div className="flex items-center justify-center">
                                    <button
                                        onClick={() => toggleSetCompletion(set)}
                                        disabled={set.isSaving || set.reps <= 0 || isLocked}
                                        className={cn(
                                            "w-9 h-9 rounded-lg flex items-center justify-center transition-all duration-300",
                                            set.completed
                                                ? "bg-green-500 text-white shadow-[0_0_15px_-3px_rgba(34,197,94,0.4)] hover:bg-green-600"
                                                : "bg-zinc-800 text-muted-foreground hover:bg-zinc-700 hover:text-white",
                                            (set.isSaving || set.reps <= 0 || isLocked) && "opacity-50 cursor-not-allowed"
                                        )}
                                    >
                                        <Check className={cn(
                                            "h-5 w-5 transition-transform duration-300",
                                            set.completed && "scale-110"
                                        )} />
                                    </button>
                                </div>
                            </div>
                        );
                    })}
                </div>

                {/* Add Set Button */}
                {!isLocked && (
                    <button
                        onClick={addSet}
                        className="w-full py-3 bg-zinc-900/50 hover:bg-zinc-900 text-xs font-bold uppercase tracking-widest text-muted-foreground hover:text-white transition-all border-t border-white/5"
                    >
                        + Add New Set
                    </button>
                )}
            </div>
        </div>
    );
}
