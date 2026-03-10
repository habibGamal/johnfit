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
import { Dumbbell, CheckCircle2, AlertTriangle, AlertCircle } from 'lucide-react';
import { cn } from '@/lib/utils';

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

    const LoadingSpinner = () => (
        <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    );

    if (incompleteSets === 0) {
        // No incomplete sets - simple finish dialog
        return (
            <AlertDialog open={open} onOpenChange={onOpenChange}>
                <AlertDialogContent className="max-w-sm border border-white/10 bg-zinc-950/90 backdrop-blur-xl">
                    <AlertDialogHeader className="text-center items-center">
                        <div className="h-16 w-16 rounded-full bg-green-500/10 flex items-center justify-center mb-4 ring-1 ring-green-500/20 shadow-[0_0_20px_-5px_rgba(34,197,94,0.3)]">
                            <CheckCircle2 className="h-8 w-8 text-green-500" />
                        </div>
                        <AlertDialogTitle className="text-xl font-black uppercase tracking-wider text-white">Workout Complete!</AlertDialogTitle>
                        <AlertDialogDescription className="text-muted-foreground text-center max-w-[260px]">
                            Great job! You've successfully completed all your sets for {day}.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter className="sm:justify-center pt-2">
                        <AlertDialogAction
                            onClick={() => onOpenChange(false)}
                            className="w-full bg-green-600 hover:bg-green-500 text-white font-bold uppercase tracking-wide"
                        >
                            Awesome, Close
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        );
    }

    return (
        <AlertDialog open={open} onOpenChange={onOpenChange}>
            <AlertDialogContent className="max-w-md border border-white/10 bg-zinc-950/90 backdrop-blur-xl">
                <AlertDialogHeader>
                    <div className="flex items-center gap-3 mb-2">
                        <div className="p-2 rounded-full bg-yellow-500/10 border border-yellow-500/20">
                            <AlertTriangle className="h-5 w-5 text-yellow-500" />
                        </div>
                        <AlertDialogTitle className="text-xl font-black uppercase tracking-wider text-white">Finish workout?</AlertDialogTitle>
                    </div>
                    <AlertDialogDescription className="text-base text-muted-foreground/90">
                        You have <span className="font-bold text-yellow-500">{incompleteSets} sets</span> that are marked as valid range but not completed.
                        <br className="mb-2" />
                        How would you like to proceed?
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <div className="flex flex-col gap-3 py-4">
                    <Button
                        onClick={() => handleFinish('complete')}
                        disabled={isLoading}
                        className="w-full h-14 text-base font-bold uppercase tracking-wide bg-green-600 hover:bg-green-500 text-white shadow-lg shadow-green-900/20 relative overflow-hidden group"
                    >
                        <div className="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000" />
                        {isLoading ? <LoadingSpinner /> : (
                            <div className="flex flex-col items-center leading-none gap-1">
                                <span>Complete All</span>
                                <span className="text-[10px] font-medium opacity-80 normal-case tracking-normal">Mark unfinished sets as completed</span>
                            </div>
                        )}
                    </Button>

                    <Button
                        onClick={() => handleFinish('discard')}
                        disabled={isLoading}
                        variant="ghost"
                        className="w-full h-14 text-base font-bold uppercase tracking-wide bg-red-500/10 hover:bg-red-500/20 text-red-500 border border-red-500/20 hover:border-red-500/40"
                    >
                        {isLoading ? <LoadingSpinner /> : (
                            <div className="flex flex-col items-center leading-none gap-1">
                                <span>Discard Unfinished</span>
                                <span className="text-[10px] font-medium opacity-80 normal-case tracking-normal">Ignore incomplete sets</span>
                            </div>
                        )}
                    </Button>

                    <Button
                        onClick={() => onOpenChange(false)}
                        disabled={isLoading}
                        variant="ghost"
                        className="w-full h-12 text-sm font-medium text-muted-foreground hover:text-white"
                    >
                        Go Back
                    </Button>
                </div>
            </AlertDialogContent>
        </AlertDialog>
    );
}
