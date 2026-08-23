import { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Link } from '@inertiajs/react';
import axios from 'axios';
import {
    Sparkles,
    Zap,
    Dumbbell,
    Utensils,
    CheckCircle2,
    Loader2,
    ArrowRight,
    Scale,
    Target,
    RefreshCw,
    AlertCircle
} from 'lucide-react';
import { Badge } from '@/Components/ui/badge';

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

export default function AutoPlanGeneratorCard() {
    const [isGenerating, setIsGenerating] = useState(false);
    const [stepMessage, setStepMessage] = useState('Generating...');
    const [result, setResult] = useState<GenerationResult | null>(null);
    const [error, setError] = useState<string | null>(null);

    const handleGenerate = async () => {
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
            } else {
                setError(data.message || 'Failed to generate plan. Please try again.');
            }
        } catch (err: any) {
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
            className="relative overflow-hidden rounded-3xl border border-border bg-card p-6 md:p-8 shadow-sm mb-10"
        >
            <div className="relative z-10">
                <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    {/* Header Info */}
                    <div className="space-y-3 max-w-2xl">
                        <div className="inline-flex items-center gap-2 rounded-full border border-primary/40 bg-primary/10 px-3.5 py-1 text-xs font-semibold text-primary">
                            <Sparkles className="h-3.5 w-3.5" />
                            <span>AI Plan Generator</span>
                        </div>

                        <h2 className="text-2xl md:text-3xl font-extrabold tracking-tight text-foreground flex items-center gap-3">
                            Smart Workout & Meal Generator
                        </h2>

                        <p className="text-sm md:text-base text-muted-foreground leading-relaxed">
                            Automatically create a tailored 7-day training schedule and targeted nutritional program based on your latest <strong className="text-foreground">InBody metrics</strong> and <strong className="text-foreground">assessment preferences</strong>.
                        </p>

                        {/* Feature Badges */}
                        <div className="flex flex-wrap items-center gap-2.5 pt-1">
                            <Badge variant="outline" className="bg-background/40 border-border text-foreground/80 gap-1.5 px-3 py-1 text-xs font-medium">
                                <Target className="w-3.5 h-3.5 text-primary" />
                                Assessment Answers
                            </Badge>
                            <Badge variant="outline" className="bg-background/40 border-border text-foreground/80 gap-1.5 px-3 py-1 text-xs font-medium">
                                <Scale className="w-3.5 h-3.5 text-amber-400" />
                                InBody Log Metrics
                            </Badge>
                            <Badge variant="outline" className="bg-background/40 border-border text-foreground/80 gap-1.5 px-3 py-1 text-xs font-medium">
                                <Zap className="w-3.5 h-3.5 text-yellow-400" />
                                Instant 7-Day Sync
                            </Badge>
                        </div>
                    </div>

                    {/* Action Area */}
                    <div className="flex flex-col items-start lg:items-end justify-center shrink-0 gap-3">
                        <button
                            onClick={handleGenerate}
                            disabled={isGenerating}
                            className="relative group overflow-hidden rounded-2xl bg-primary hover:bg-primary/90 px-7 py-4 text-primary-foreground font-bold text-base shadow-sm transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-75 disabled:pointer-events-none"
                        >
                            <div className="flex items-center gap-2.5 relative z-10">
                                {isGenerating ? (
                                    <>
                                        <Loader2 className="h-5 w-5 animate-spin" />
                                        <span>{stepMessage}</span>
                                    </>
                                ) : (
                                    <>
                                        <Zap className="h-5 w-5 fill-black" />
                                        <span>Auto-Generate My Plans</span>
                                        <ArrowRight className="h-5 w-5 transition-transform group-hover:translate-x-1" />
                                    </>
                                )}
                            </div>
                        </button>
                        {result && (
                            <button
                                onClick={handleGenerate}
                                disabled={isGenerating}
                                className="text-xs text-muted-foreground hover:text-primary transition-colors flex items-center gap-1 self-start lg:self-end"
                            >
                                <RefreshCw className="w-3 h-3" /> Re-generate new plans
                            </button>
                        )}
                    </div>
                </div>

                {/* Error Banner */}
                {error && (
                    <motion.div
                        initial={{ opacity: 0, height: 0 }}
                        animate={{ opacity: 1, height: 'auto' }}
                        className="mt-6 rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-400 flex items-center gap-3"
                    >
                        <AlertCircle className="w-5 h-5 flex-shrink-0" />
                        <span>{error}</span>
                    </motion.div>
                )}

                {/* Result Card Modal / Panel */}
                <AnimatePresence>
                    {result && (
                        <motion.div
                            initial={{ opacity: 0, y: 10, height: 0 }}
                            animate={{ opacity: 1, y: 0, height: 'auto' }}
                            exit={{ opacity: 0, y: -10, height: 0 }}
                            transition={{ duration: 0.4 }}
                            className="mt-6 rounded-2xl border border-primary/40 bg-card/90 p-5 md:p-6 shadow-inner"
                        >
                            <div className="flex items-center gap-2 text-primary font-bold text-base mb-4">
                                <CheckCircle2 className="w-5 h-5 text-emerald-400" />
                                <span>Plans Generated Successfully!</span>
                            </div>

                            {/* Summary Grid */}
                            <div className="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6 bg-background/50 p-4 rounded-xl border border-border/50">
                                <div>
                                    <span className="text-xs text-muted-foreground block">Goal Detected</span>
                                    <span className="text-sm font-semibold text-foreground truncate block">{result.summary.goal}</span>
                                </div>
                                <div>
                                    <span className="text-xs text-muted-foreground block">InBody Weight</span>
                                    <span className="text-sm font-semibold text-foreground block">{result.summary.weight} kg</span>
                                </div>
                                <div>
                                    <span className="text-xs text-muted-foreground block">Daily Calories</span>
                                    <span className="text-sm font-semibold text-primary block">{result.summary.target_calories} kcal</span>
                                </div>
                                <div>
                                    <span className="text-xs text-muted-foreground block">Weekly Split</span>
                                    <span className="text-sm font-semibold text-foreground block">{result.summary.workout_days}</span>
                                </div>
                            </div>

                            {/* Navigation Quick Links */}
                            <div className="flex flex-col sm:flex-row items-center gap-3">
                                <Link
                                    href={route('schedule.index')}
                                    className="w-full flex items-center justify-center gap-2 bg-primary hover:bg-primary/90 text-primary-foreground font-semibold text-sm px-5 py-3 rounded-xl transition-all shadow-md"
                                >
                                    <Sparkles className="w-4 h-4" />
                                    <span>Open Daily Schedule & Plans</span>
                                </Link>
                            </div>
                        </motion.div>
                    )}
                </AnimatePresence>
            </div>
        </motion.div>
    );
}
