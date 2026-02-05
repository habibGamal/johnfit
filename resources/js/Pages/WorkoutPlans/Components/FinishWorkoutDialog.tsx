import { useState } from 'react';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/Components/ui/alert-dialog';
import { Button } from '@/Components/ui/button';
import { router } from '@inertiajs/react';
import axios from 'axios';

interface FinishWorkoutDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    workoutPlanId: number;
    day: string;
    incompleteSets: number;
    onFinish?: () => void;
}

export default function FinishWorkoutDialog({
    open,
    onOpenChange,
    workoutPlanId,
    day,
    incompleteSets,
    onFinish,
}: FinishWorkoutDialogProps) {
    const [isLoading, setIsLoading] = useState(false);

    const handleFinish = async (action: 'complete' | 'discard') => {
        setIsLoading(true);
        try {
            await axios.post(route('workout-plans.finish-day'), {
                workout_plan_id: workoutPlanId,
                day: day,
                action: action,
            });

            onOpenChange(false);
            onFinish?.();
            router.reload();
        } catch (error) {
            console.error('Failed to finish workout:', error);
        } finally {
            setIsLoading(false);
        }
    };

    if (incompleteSets === 0) {
        // No incomplete sets - simple finish dialog
        return (
            <AlertDialog open={open} onOpenChange={onOpenChange}>
                <AlertDialogContent className="max-w-sm">
                    <AlertDialogHeader>
                        <AlertDialogTitle>Workout Complete! 🎉</AlertDialogTitle>
                        <AlertDialogDescription>
                            Great job! You've completed all your sets for {day}.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogAction onClick={() => onOpenChange(false)}>
                            Done
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        );
    }

    return (
        <AlertDialog open={open} onOpenChange={onOpenChange}>
            <AlertDialogContent className="max-w-md">
                <AlertDialogHeader>
                    <AlertDialogTitle>Finish workout?</AlertDialogTitle>
                    <AlertDialogDescription className="text-base">
                        There are some valid sets in this workout that have not been marked as complete.
                        Invalid or empty sets will be removed.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <div className="flex flex-col gap-2 py-2">
                    <Button
                        onClick={() => handleFinish('complete')}
                        disabled={isLoading}
                        className="w-full py-6 text-base font-semibold"
                    >
                        Complete Unfinished Sets
                    </Button>
                    <Button
                        onClick={() => handleFinish('discard')}
                        disabled={isLoading}
                        variant="secondary"
                        className="w-full py-6 text-base font-semibold"
                    >
                        Discard Unfinished Sets
                    </Button>
                    <Button
                        onClick={() => onOpenChange(false)}
                        disabled={isLoading}
                        variant="ghost"
                        className="w-full"
                    >
                        Cancel
                    </Button>
                </div>
            </AlertDialogContent>
        </AlertDialog>
    );
}
