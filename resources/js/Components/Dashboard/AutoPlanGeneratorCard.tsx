import { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Link } from '@inertiajs/react';
import axios from 'axios';
import {
    Sparkles,
    Zap,
    CheckCircle2,
    Loader2,
    ArrowRight,
    RefreshCw,
    AlertCircle
} from 'lucide-react';

interface GenerationResult {
    workout_plan_id: number;
    meal_plan_id: number;
    summary: {
        goal: string;
        weight: number;
        target_calories: number;
        workout_days: string;
    };
}

export interface AiPlanEligibility {
    can_generate: boolean;
    remaining: number | null;
    reason: string | null;
}

interface AutoPlanGeneratorCardProps {
    eligibility?: AiPlanEligibility;
}

export default function AutoPlanGeneratorCard({ eligibility }: AutoPlanGeneratorCardProps) {
    const [isGenerating, setIsGenerating] = useState(false);
    const [stepMessage, setStepMessage] = useState('Generating...');
    const [result, setResult] = useState<GenerationResult | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [canGenerate, setCanGenerate] = useState(eligibility ? eligibility.can_generate : true);

    const handleGenerate = async () => {
        if (!canGenerate) {
            setError(eligibility?.reason || 'The AI Plan Generator can only be used once per account.');
            return;
        }

        setIsGenerating(true);
        setError(null);
        setStepMessage('Analyzing Assessment & InBody metrics...');

        try {
            // Simulated step message progression for rich user feedback
            setTimeout(() => setStepMessage('Structuring custom workout split...'), 800);
            setTimeout(() => setStepMessage('Balancing macro & meal requirements...'), 1600);

            // Axios request to Laravel route
            const response = await axios.post(route('plans.generate'));
            const data = response.data;

            if (data.success) {
                setResult({
                    workout_plan_id: data.workout_plan_id,
                    meal_plan_id: data.meal_plan_id,
                    summary: data.summary,
                });
                setCanGenerate(false);
            } else {
                setError(data.message || 'Failed to generate plan. Please try again.');
            }
        } catch (err: any) {
            if (err?.response?.status === 403 || err?.response?.data?.code === 'AI_PLAN_LIMIT_REACHED') {
                setCanGenerate(false);
            }
            setError(err?.response?.data?.message || err?.message || 'An unexpected error occurred.');
        } finally {
            setIsGenerating(false);
        }
    };

    return (
        <motion.div
            initial={{ opacity: 0, y: 15 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, ease: "easeOut" }}
            className="relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-sm mb-8"
        >
            <div className="relative z-10">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    {/* Header Info */}
                    <div className="min-w-0">
                        <div className="flex items-center gap-2">
                            <h2 className="text-lg font-bold tracking-tight text-foreground flex items-center gap-2">
                                <Sparkles className="h-4.5 w-4.5 text-primary shrink-0" />
                                AI Plan Generator
                            </h2>
                            {!canGenerate && (
                                <span className="inline-flex items-center rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-semibold text-primary">
                                    1/1 Used
                                </span>
                            )}
                        </div>
                        <p className="mt-1 text-sm text-muted-foreground leading-snug">
                            {canGenerate
                                ? 'Tailored 7-day workout + meal plan from your InBody & assessment data.'
                                : 'You have already generated your 1-time personalized AI plan.'}
                        </p>
                    </div>

                    {/* Action Area */}
                    <div className="flex items-center gap-3 shrink-0">
                        {result && canGenerate && (
                            <button
                                onClick={handleGenerate}
                                disabled={isGenerating}
                                className="text-xs text-muted-foreground hover:text-primary transition-colors flex items-center gap-1"
                            >
                                <RefreshCw className="w-3 h-3" /> Regenerate
                            </button>
                        )}
                        <button
                            onClick={handleGenerate}
                            disabled={isGenerating || !canGenerate}
                            className={`group relative rounded-xl px-5 py-2.5 font-semibold text-sm shadow-sm transition-all duration-200 ${
                                canGenerate
                                    ? 'bg-primary hover:bg-primary/90 text-primary-foreground hover:scale-[1.02] active:scale-[0.98]'
                                    : 'bg-muted text-muted-foreground cursor-not-allowed opacity-75'
                            } disabled:pointer-events-none`}
                        >
                            <div className="flex items-center gap-2">
                                {isGenerating ? (
                                    <>
                                        <Loader2 className="h-4 w-4 animate-spin" />
                                        <span>{stepMessage}</span>
                                    </>
                                ) : !canGenerate ? (
                                    <>
                                        <CheckCircle2 className="h-4 w-4 text-emerald-500" />
                                        <span>Plan Generated</span>
                                    </>
                                ) : (
                                    <>
                                        <Zap className="h-4 w-4 fill-black" />
                                        <span>Auto-Generate Plans</span>
                                        <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
                                    </>
                                )}
                            </div>
                        </button>
                    </div>
                </div>

                {/* Error Banner */}
                {error && (
                    <motion.div
                        initial={{ opacity: 0, height: 0 }}
                        animate={{ opacity: 1, height: 'auto' }}
                        className="mt-4 rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-2 text-sm text-red-400 flex items-center gap-2"
                    >
                        <AlertCircle className="w-4 h-4 flex-shrink-0" />
                        <span>{error}</span>
                    </motion.div>
                )}

                {/* Result Panel */}
                <AnimatePresence>
                    {result && (
                        <motion.div
                            initial={{ opacity: 0, y: 10, height: 0 }}
                            animate={{ opacity: 1, y: 0, height: 'auto' }}
                            exit={{ opacity: 0, y: -10, height: 0 }}
                            transition={{ duration: 0.3 }}
                            className="mt-4 rounded-xl border border-primary/40 bg-card/90 p-4 shadow-inner"
                        >
                            <div className="flex flex-col sm:flex-row sm:items-center gap-3">
                                <div className="flex items-center gap-1.5 text-primary text-sm font-semibold">
                                    <CheckCircle2 className="w-4 h-4 text-emerald-400" />
                                    Plans Ready
                                </div>
                                <div className="flex flex-wrap items-center gap-x-4 gap-y-1 sm:border-l sm:border-border sm:pl-4 text-xs">
                                    <span className="text-muted-foreground">Goal <span className="font-semibold text-foreground">{result.summary.goal}</span></span>
                                    <span className="text-muted-foreground">Weight <span className="font-semibold text-foreground">{result.summary.weight} kg</span></span>
                                    <span className="text-muted-foreground">Calories <span className="font-semibold text-primary">{result.summary.target_calories} kcal</span></span>
                                    <span className="text-muted-foreground">Split <span className="font-semibold text-foreground">{result.summary.workout_days}</span></span>
                                </div>
                                <Link
                                    href={route('schedule.index')}
                                    className="sm:ml-auto flex items-center justify-center gap-1.5 rounded-lg bg-primary hover:bg-primary/90 text-primary-foreground font-semibold text-xs px-3.5 py-2 transition-all shadow-sm whitespace-nowrap"
                                >
                                    <Sparkles className="w-3.5 h-3.5" />
                                    Open Schedule
                                </Link>
                            </div>
                        </motion.div>
                    )}
                </AnimatePresence>
            </div>
        </motion.div>
    );
}
