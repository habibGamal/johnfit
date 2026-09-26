import React, { useState } from 'react';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/Components/ui/dialog';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { WaterCalculation } from '@/types/water';
import {
    Droplets,
    Dumbbell,
    Lock,
    Sparkles,
    CheckCircle2,
    RotateCcw,
    MessageSquare,
    Scale,
} from 'lucide-react';
import axios from 'axios';

interface HydrationCalculatorModalProps {
    isOpen: boolean;
    onClose: () => void;
    calculation: WaterCalculation;
    customTargetMl: number | null;
    effectiveTargetMl: number;
    onTargetUpdated: (newCustomTarget: number | null, newEffectiveTarget: number) => void;
}

export default function HydrationCalculatorModal({
    isOpen,
    onClose,
    calculation,
    customTargetMl,
    effectiveTargetMl,
    onTargetUpdated,
}: HydrationCalculatorModalProps) {
    const [targetInput, setTargetInput] = useState<string>(
        customTargetMl ? customTargetMl.toString() : calculation.target_ml.toString()
    );
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const handleSaveCustomTarget = async (e: React.FormEvent) => {
        e.preventDefault();
        const parsed = parseInt(targetInput, 10);
        if (isNaN(parsed) || parsed < 1000 || parsed > 8000) {
            setError('Please enter a target between 1,000ml and 8,000ml.');
            return;
        }

        setIsSubmitting(true);
        setError(null);

        try {
            const response = await axios.post('/water/target', {
                custom_target_ml: parsed,
            });

            if (response.data?.success) {
                onTargetUpdated(parsed, parsed);
                onClose();
            }
        } catch (err: any) {
            setError(err?.response?.data?.message || 'Failed to update custom target.');
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleResetToAuto = async () => {
        setIsSubmitting(true);
        setError(null);

        try {
            const response = await axios.post('/water/target', {
                custom_target_ml: null,
            });

            if (response.data?.success) {
                onTargetUpdated(null, calculation.target_ml);
                setTargetInput(calculation.target_ml.toString());
                onClose();
            }
        } catch (err: any) {
            setError(err?.response?.data?.message || 'Failed to reset to auto target.');
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <Dialog open={isOpen} onOpenChange={(open) => !open && onClose()}>
            <DialogContent className="sm:max-w-[480px] bg-card border-border text-foreground">
                <DialogHeader>
                    <div className="flex items-center gap-2.5">
                        <div className="p-2 rounded-xl bg-sky-500/10 text-sky-400">
                            <Droplets className="w-5 h-5" />
                        </div>
                        <div>
                            <DialogTitle className="text-lg font-bold">Hydration Calculator & Goals</DialogTitle>
                            <DialogDescription className="text-xs text-muted-foreground mt-0.5">
                                Scientifically calculated water needs based on your body and training.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <div className="space-y-4 py-2">
                    {/* Active Strategy Badge */}
                    <div className="p-3.5 rounded-xl border border-border bg-secondary/30 flex items-center justify-between">
                        <div className="flex items-center gap-2.5">
                            <Sparkles className="w-4 h-4 text-sky-400 shrink-0" />
                            <div>
                                <p className="text-xs text-muted-foreground">Active Target Strategy</p>
                                <p className="text-sm font-bold text-foreground">{calculation.tier_name}</p>
                            </div>
                        </div>
                        <span className="inline-flex items-center rounded-full bg-sky-500/10 px-2.5 py-0.5 text-xs font-bold text-sky-400 border border-sky-500/20">
                            {effectiveTargetMl.toLocaleString()} ml
                        </span>
                    </div>

                    {/* Breakdown Details */}
                    <div className="rounded-xl border border-border p-4 space-y-3 bg-secondary/10">
                        <h4 className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                            Formula Breakdown
                        </h4>

                        <div className="space-y-2 text-sm">
                            <div className="flex items-center justify-between py-1 border-b border-border">
                                <span className="flex items-center gap-2 text-muted-foreground">
                                    <Scale className="w-4 h-4 text-muted-foreground" />
                                    Base Requirement
                                </span>
                                <span className="font-semibold text-foreground">
                                    {calculation.base_ml.toLocaleString()} ml
                                </span>
                            </div>

                            {calculation.workout_bonus_ml > 0 && (
                                <div className="flex items-center justify-between py-1 border-b border-border">
                                    <span className="flex items-center gap-2 text-emerald-400">
                                        <Dumbbell className="w-4 h-4" />
                                        Training Day Bonus
                                    </span>
                                    <span className="font-semibold text-emerald-400">
                                        +{calculation.workout_bonus_ml.toLocaleString()} ml
                                    </span>
                                </div>
                            )}

                            <div className="flex items-center justify-between pt-1">
                                <span className="flex items-center gap-2 font-medium text-foreground">
                                    <CheckCircle2 className="w-4 h-4 text-sky-400" />
                                    Total Recommended Target
                                </span>
                                <span className="text-base font-extrabold text-sky-400">
                                    {calculation.target_ml.toLocaleString()} ml
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Coach Notes Callout */}
                    {calculation.admin_notes && (
                        <div className="p-3 rounded-xl border border-primary/20 bg-primary/5 text-xs">
                            <div className="flex items-start gap-2.5">
                                <MessageSquare className="w-4 h-4 text-primary mt-0.5 shrink-0" />
                                <div className="space-y-0.5 leading-relaxed">
                                    <strong className="font-semibold text-primary">
                                        Coach Guidance:{' '}
                                    </strong>
                                    <span className="text-muted-foreground">
                                        {calculation.admin_notes}
                                    </span>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Custom Override Form */}
                    <div className="pt-2 border-t border-border">
                        {calculation.allow_user_override ? (
                            <form onSubmit={handleSaveCustomTarget} className="space-y-3">
                                <div>
                                    <label className="text-xs font-semibold text-foreground block mb-1">
                                        Personal Custom Target (Optional Override)
                                    </label>
                                    <div className="flex gap-2">
                                        <Input
                                            type="number"
                                            value={targetInput}
                                            onChange={(e) => setTargetInput(e.target.value)}
                                            placeholder="e.g. 3000"
                                            min={1000}
                                            max={8000}
                                            step={50}
                                            className="h-9 text-sm bg-background border-border"
                                        />
                                        <Button
                                            type="submit"
                                            disabled={isSubmitting}
                                            size="sm"
                                            className="h-9 px-4 shrink-0 bg-sky-500 hover:bg-sky-400 text-black font-semibold"
                                        >
                                            {isSubmitting ? 'Saving...' : 'Set Goal'}
                                        </Button>
                                    </div>
                                    <p className="text-[11px] text-muted-foreground mt-1">
                                        Enter your personal daily goal between 1,000ml and 8,000ml.
                                    </p>
                                </div>

                                {customTargetMl !== null && (
                                    <div className="flex items-center justify-between pt-1">
                                        <span className="text-xs text-muted-foreground">
                                            Using custom override ({customTargetMl.toLocaleString()} ml)
                                        </span>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            onClick={handleResetToAuto}
                                            disabled={isSubmitting}
                                            className="h-7 text-xs text-sky-400 hover:text-sky-300 hover:bg-sky-500/10 p-1.5 flex items-center gap-1"
                                        >
                                            <RotateCcw className="w-3.5 h-3.5" />
                                            Reset to Auto
                                        </Button>
                                    </div>
                                )}
                            </form>
                        ) : (
                            <div className="p-3 rounded-lg bg-muted/60 flex items-center gap-2 text-xs text-muted-foreground">
                                <Lock className="w-4 h-4 shrink-0 text-muted-foreground" />
                                <span>
                                    Target is locked and managed exclusively by your coach for your current protocol.
                                </span>
                            </div>
                        )}

                        {error && <p className="text-xs text-rose-400 mt-2">{error}</p>}
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}
