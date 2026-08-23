import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { UserDailyItem, ItemWorkoutSet } from '@/types';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from '@/Components/ui/dialog';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Checkbox } from '@/Components/ui/checkbox';
import { Badge } from '@/Components/ui/badge';
import { Dumbbell, Plus, Trash2, CheckCircle2, Video, ExternalLink } from 'lucide-react';

interface WorkoutSetModalProps {
    item: UserDailyItem | null;
    isOpen: boolean;
    isLocked: boolean;
    onClose: () => void;
}

export default function WorkoutSetModal({
    item,
    isOpen,
    isLocked,
    onClose,
}: WorkoutSetModalProps) {
    const targetDetails = item?.target_details || {};
    const options = (targetDetails.options || []) as any[];
    const hasOptions = options.length > 1;

    const [selectedWorkoutId, setSelectedWorkoutId] = useState<number | null>(null);
    const [sets, setSets] = useState<ItemWorkoutSet[]>([]);
    const [isSaving, setIsSaving] = useState(false);

    useEffect(() => {
        if (!item) return;

        const defaultWorkoutId = item.execution_payload?.selected_workout_id || item.reference_id || options[0]?.workout_id || null;
        setSelectedWorkoutId(defaultWorkoutId);

        const payloadSets = item.execution_payload?.sets;
        if (payloadSets && Array.isArray(payloadSets) && payloadSets.length > 0) {
            setSets(payloadSets);
        } else {
            // Find active option preset
            const activeOpt = options.find((opt: any) => opt.workout_id === defaultWorkoutId) || options[0];
            const count = activeOpt?.sets_count || targetDetails.sets_count || 3;
            const targetReps = activeOpt?.target_reps || targetDetails.target_reps || [];

            const initial: ItemWorkoutSet[] = [];
            for (let s = 1; s <= count; s++) {
                initial.push({
                    set_number: s,
                    target_reps: targetReps[s - 1] ?? 10,
                    reps: targetReps[s - 1] ?? 10,
                    weight: null,
                    completed: item.is_completed,
                });
            }
            setSets(initial);
        }
    }, [item, isOpen]);

    if (!item) return null;

    const activeOption = options.find((opt: any) => opt.workout_id === selectedWorkoutId) || options[0] || {};
    const activeName = activeOption.name || item.item_name;
    const activeMuscles = activeOption.muscles || targetDetails.muscles || '';
    const muscles = Array.isArray(activeMuscles) ? activeMuscles.join(', ') : activeMuscles;
    const activeVideoUrl = activeOption.video_url || targetDetails.video_url;

    const handleSwitchOption = (option: any) => {
        if (isLocked) return;
        setSelectedWorkoutId(option.workout_id);

        const count = option.sets_count || 3;
        const targetReps = option.target_reps || [];
        const initial: ItemWorkoutSet[] = [];
        for (let s = 1; s <= count; s++) {
            initial.push({
                set_number: s,
                target_reps: targetReps[s - 1] ?? 10,
                reps: targetReps[s - 1] ?? 10,
                weight: null,
                completed: false,
            });
        }
        setSets(initial);
    };

    const handleSetChange = (index: number, field: keyof ItemWorkoutSet, value: any) => {
        if (isLocked) return;
        const updated = [...sets];
        updated[index] = {
            ...updated[index],
            [field]: value,
        };
        setSets(updated);
    };

    const handleAddSet = () => {
        if (isLocked) return;
        const nextNum = sets.length + 1;
        const lastSet = sets[sets.length - 1];
        setSets([
            ...sets,
            {
                set_number: nextNum,
                target_reps: lastSet?.target_reps ?? 10,
                reps: lastSet?.reps ?? 10,
                weight: lastSet?.weight ?? null,
                completed: false,
            },
        ]);
    };

    const handleRemoveSet = (index: number) => {
        if (isLocked || sets.length <= 1) return;
        const filtered = sets.filter((_, i) => i !== index).map((s, idx) => ({
            ...s,
            set_number: idx + 1,
        }));
        setSets(filtered);
    };

    const handleSave = () => {
        if (isLocked) return;
        setIsSaving(true);

        router.post(
            route('schedule.items.workout-sets', item.id),
            {
                sets,
                selected_workout_id: selectedWorkoutId,
            },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    setIsSaving(false);
                    onClose();
                },
                onError: () => {
                    setIsSaving(false);
                },
            }
        );
    };

    return (
        <Dialog open={isOpen} onOpenChange={(open) => !open && onClose()}>
            <DialogContent className="sm:max-w-[480px] p-0 overflow-hidden bg-card border-border">
                <DialogHeader className="p-5 pb-3 border-b border-border">
                    <div className="flex items-start justify-between gap-3">
                        <div className="space-y-1">
                            <div className="flex items-center gap-2">
                                <Dumbbell className="h-5 w-5 text-primary" />
                                <DialogTitle className="text-lg font-bold text-foreground">
                                    {activeName}
                                </DialogTitle>
                            </div>
                            {muscles && (
                                <DialogDescription className="text-xs text-muted-foreground">
                                    Target: {muscles}
                                </DialogDescription>
                            )}
                        </div>
                        <Badge variant="secondary" className="text-xs font-semibold shrink-0">
                            +{item.points} pts
                        </Badge>
                    </div>

                    {activeVideoUrl && (
                        <div className="pt-2">
                            <a
                                href={activeVideoUrl}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-1.5 text-xs text-primary hover:underline font-medium"
                            >
                                <Video className="h-3.5 w-3.5" /> Watch Exercise Demonstration
                                <ExternalLink className="h-3 w-3" />
                            </a>
                        </div>
                    )}

                    {/* Option Switcher if alternatives exist */}
                    {hasOptions && (
                        <div className="pt-3 border-t border-border/60">
                            <span className="block text-[11px] font-semibold uppercase tracking-wider text-muted-foreground mb-2">
                                Choose Exercise Alternative (OR):
                            </span>
                            <div className="grid grid-cols-2 gap-2">
                                {options.map((opt: any, idx: number) => {
                                    const isSelected = (opt.workout_id === selectedWorkoutId);
                                    return (
                                        <button
                                            key={opt.workout_id || idx}
                                            type="button"
                                            disabled={isLocked}
                                            onClick={() => handleSwitchOption(opt)}
                                            className={`p-2.5 rounded-lg border text-left text-xs font-medium transition-all ${
                                                isSelected
                                                    ? 'bg-primary/10 border-primary text-primary font-bold shadow-sm'
                                                    : 'bg-secondary/40 border-border text-muted-foreground hover:bg-secondary'
                                            }`}
                                        >
                                            <span className="block truncate">{opt.name}</span>
                                            <span className="block text-[10px] opacity-75 mt-0.5">
                                                {opt.sets_count} sets × {opt.target_reps?.join('-') || '10'} reps
                                            </span>
                                        </button>
                                    );
                                })}
                            </div>
                        </div>
                    )}
                </DialogHeader>

                {/* Sets Table */}
                <div className="p-5 space-y-3 max-h-[60vh] overflow-y-auto">
                    <div className="grid grid-cols-12 gap-2 text-xs font-semibold text-muted-foreground px-1 pb-1">
                        <span className="col-span-2 text-center">Set</span>
                        <span className="col-span-4 text-center">Weight (kg)</span>
                        <span className="col-span-3 text-center">Reps</span>
                        <span className="col-span-2 text-center">Done</span>
                        <span className="col-span-1"></span>
                    </div>

                    {sets.map((set, idx) => (
                        <div
                            key={idx}
                            className={`grid grid-cols-12 gap-2 items-center p-2 rounded-lg border transition-colors ${
                                set.completed
                                    ? 'bg-primary/5 border-primary/30'
                                    : 'bg-secondary/40 border-border/60'
                            }`}
                        >
                            <span className="col-span-2 text-center text-sm font-bold text-foreground">
                                #{set.set_number}
                            </span>

                            <div className="col-span-4">
                                <Input
                                    type="number"
                                    min="0"
                                    step="0.5"
                                    placeholder="0 kg"
                                    disabled={isLocked}
                                    value={set.weight !== null ? set.weight : ''}
                                    onChange={(e) =>
                                        handleSetChange(
                                            idx,
                                            'weight',
                                            e.target.value === '' ? null : parseFloat(e.target.value)
                                        )
                                    }
                                    className="h-9 text-center text-sm font-medium bg-background"
                                />
                            </div>

                            <div className="col-span-3">
                                <Input
                                    type="number"
                                    min="0"
                                    disabled={isLocked}
                                    value={set.reps}
                                    onChange={(e) =>
                                        handleSetChange(idx, 'reps', parseInt(e.target.value) || 0)
                                    }
                                    className="h-9 text-center text-sm font-medium bg-background"
                                />
                            </div>

                            <div className="col-span-2 flex items-center justify-center">
                                <Checkbox
                                    disabled={isLocked}
                                    checked={set.completed}
                                    onCheckedChange={(checked) =>
                                        handleSetChange(idx, 'completed', Boolean(checked))
                                    }
                                    className="h-5 w-5 rounded-md"
                                />
                            </div>

                            <div className="col-span-1 flex items-center justify-center">
                                {!isLocked && sets.length > 1 && (
                                    <button
                                        type="button"
                                        onClick={() => handleRemoveSet(idx)}
                                        className="text-muted-foreground hover:text-destructive transition-colors p-1"
                                        aria-label="Remove set"
                                    >
                                        <Trash2 className="h-3.5 w-3.5" />
                                    </button>
                                )}
                            </div>
                        </div>
                    ))}

                    {!isLocked && (
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={handleAddSet}
                            className="w-full gap-1.5 text-xs border-dashed border-border hover:bg-secondary"
                        >
                            <Plus className="h-3.5 w-3.5" /> Add Another Set
                        </Button>
                    )}
                </div>

                <DialogFooter className="p-4 bg-secondary/30 border-t border-border flex flex-row items-center justify-between sm:justify-between gap-2">
                    <Button variant="ghost" size="sm" onClick={onClose}>
                        Cancel
                    </Button>
                    {!isLocked && (
                        <Button
                            onClick={handleSave}
                            disabled={isSaving}
                            className="gap-1.5 font-semibold"
                        >
                            <CheckCircle2 className="h-4 w-4" />
                            {isSaving ? 'Saving...' : 'Save Sets & Complete'}
                        </Button>
                    )}
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
