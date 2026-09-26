import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import {
    Droplets,
    Plus,
    RotateCcw,
    Info,
    CheckCircle2,
    MessageSquare,
    GlassWater,
    CupSoda,
    Sparkles,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { UserDailyWaterLog, WaterCalculation, WaterWeeklyStats } from '@/types/water';
import ProgressRing from '@/Components/Dashboard/ProgressRing';
import HydrationCalculatorModal from './HydrationCalculatorModal';
import WaterWeeklyChart from './WaterWeeklyChart';
import axios from 'axios';

interface WaterIntakeWidgetProps {
    initialLog: UserDailyWaterLog;
    calculation: WaterCalculation;
    weeklyStats: WaterWeeklyStats;
    className?: string;
}

export default function WaterIntakeWidget({
    initialLog,
    calculation,
    weeklyStats: initialWeeklyStats,
    className = '',
}: WaterIntakeWidgetProps) {
    const [log, setLog] = useState<UserDailyWaterLog>(initialLog);
    const [weeklyStats, setWeeklyStats] = useState<WaterWeeklyStats>(initialWeeklyStats);
    const [calcData, setCalcData] = useState<WaterCalculation>(calculation);
    const [activeTab, setActiveTab] = useState<'today' | 'history'>('today');
    const [isLogging, setIsLogging] = useState<number | null>(null);
    const [isUndoing, setIsUndoing] = useState(false);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [customAmount, setCustomAmount] = useState('');
    const [showCustomInput, setShowCustomInput] = useState(false);
    const [toastMessage, setToastMessage] = useState<string | null>(null);

    const showToast = (msg: string) => {
        setToastMessage(msg);
        setTimeout(() => setToastMessage(null), 3000);
    };

    const handleLogIntake = async (amountMl: number, containerType: 'cup' | 'bottle' | 'shaker' | 'custom' = 'cup') => {
        setIsLogging(amountMl);
        try {
            const response = await axios.post('/water/log', {
                amount_ml: amountMl,
                container_type: containerType,
            });

            if (response.data?.success && response.data?.log) {
                const updatedLog: UserDailyWaterLog = response.data.log;
                setLog(updatedLog);

                if (updatedLog.is_completed && !log.is_completed) {
                    showToast('🎉 Goal Smashed! Great hydration today!');
                } else {
                    showToast(`+${amountMl}ml logged successfully!`);
                }

                refreshWeeklyStats();
            }
        } catch (error: any) {
            showToast(error?.response?.data?.message || 'Failed to log water');
        } finally {
            setIsLogging(null);
            setShowCustomInput(false);
            setCustomAmount('');
        }
    };

    const handleUndoLast = async () => {
        if (!log.entries || log.entries.length === 0) return;
        const lastEntry = log.entries[0];

        setIsUndoing(true);
        try {
            const response = await axios.delete(`/water/entries/${lastEntry.id}`);
            if (response.data?.success && response.data?.log) {
                setLog(response.data.log);
                showToast(`Undid -${lastEntry.amount_ml}ml`);
                refreshWeeklyStats();
            }
        } catch (error: any) {
            showToast(error?.response?.data?.message || 'Failed to undo entry');
        } finally {
            setIsUndoing(false);
        }
    };

    const refreshWeeklyStats = async () => {
        try {
            const res = await axios.get('/water');
            if (res.data?.weekly_stats) {
                setWeeklyStats(res.data.weekly_stats);
            }
        } catch (e) {
            // ignore silent sync error
        }
    };

    const handleCustomSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        const amt = parseInt(customAmount, 10);
        if (amt && amt >= 50 && amt <= 2000) {
            handleLogIntake(amt, 'custom');
        }
    };

    const handleTargetUpdated = (newCustomTarget: number | null, newEffectiveTarget: number) => {
        setLog((prev) => {
            const newConsumed = prev.consumed_ml;
            const isCompleted = newEffectiveTarget > 0 && newConsumed >= newEffectiveTarget;
            const newPercentage = newEffectiveTarget > 0 ? Math.min(100, Math.round((newConsumed / newEffectiveTarget) * 100)) : 0;

            return {
                ...prev,
                custom_target_ml: newCustomTarget,
                effective_target_ml: newEffectiveTarget,
                is_completed: isCompleted,
                percentage: newPercentage,
                remaining_ml: Math.max(0, newEffectiveTarget - newConsumed),
            };
        });
        showToast('Daily target updated!');
        refreshWeeklyStats();
    };

    const target = log.effective_target_ml || 2500;
    const consumed = log.consumed_ml || 0;
    const percentage = Math.min(100, Math.round((consumed / target) * 100));
    const remaining = Math.max(0, target - consumed);
    const lastEntry = log.entries && log.entries.length > 0 ? log.entries[0] : null;

    let statusText: string;
    let statusColor = 'text-muted-foreground';

    if (percentage >= 100) {
        statusText = 'Hydration goal achieved today!';
        statusColor = 'text-emerald-400 font-semibold';
    } else if (consumed === 0) {
        statusText = `${target.toLocaleString()} ml remaining today. First sip awaits.`;
    } else {
        statusText = `${remaining.toLocaleString()} ml more to hit your daily target.`;
    }

    return (
        <>
            <motion.div
                initial={{ opacity: 0, y: 16 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.45, ease: 'easeOut', delay: 0.1 }}
                className={cn('rounded-2xl border border-border bg-card/60 p-4 shadow-sm sm:p-5', className)}
            >
                {/* Header */}
                <div className="mb-4 flex items-center justify-between">
                    <div className="flex items-center gap-2.5">
                        <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-500/10 text-sky-400">
                            <Droplets className="h-5 w-5" />
                        </div>
                        <div>
                            <div className="flex items-center gap-2">
                                <h3 className="text-base font-bold text-foreground sm:text-lg">
                                    Water Intake
                                </h3>
                                {log.is_completed ? (
                                    <span className="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-0.5 text-[11px] font-bold text-emerald-400 border border-emerald-500/20">
                                        <CheckCircle2 className="h-3 w-3" />
                                        Goal Achieved
                                    </span>
                                ) : (
                                    <span className="inline-flex items-center gap-1 rounded-full bg-sky-500/10 px-2 py-0.5 text-[11px] font-bold text-sky-400 border border-sky-500/20">
                                        <Droplets className="h-3 w-3" />
                                        In Progress
                                    </span>
                                )}
                            </div>
                            <p className="text-xs text-muted-foreground mt-0.5">
                                {calcData.tier_name}
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        {/* Sub-Tabs: Today vs 7 Days */}
                        <div className="flex rounded-lg bg-muted/60 p-0.5 border border-border text-xs">
                            <button
                                type="button"
                                onClick={() => setActiveTab('today')}
                                className={cn(
                                    'px-2.5 py-1 rounded-md text-xs font-semibold transition-all',
                                    activeTab === 'today'
                                        ? 'bg-card text-foreground shadow-xs'
                                        : 'text-muted-foreground hover:text-foreground'
                                )}
                            >
                                Today
                            </button>
                            <button
                                type="button"
                                onClick={() => setActiveTab('history')}
                                className={cn(
                                    'px-2.5 py-1 rounded-md text-xs font-semibold transition-all',
                                    activeTab === 'history'
                                        ? 'bg-card text-foreground shadow-xs'
                                        : 'text-muted-foreground hover:text-foreground'
                                )}
                            >
                                7 Days
                            </button>
                        </div>

                        {/* Modal Settings Trigger */}
                        <Button
                            variant="ghost"
                            size="icon"
                            onClick={() => setIsModalOpen(true)}
                            className="h-8 w-8 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted"
                            title="Hydration Breakdown & Settings"
                        >
                            <Info className="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                {/* Toast Feedback */}
                <AnimatePresence>
                    {toastMessage && (
                        <motion.div
                            initial={{ opacity: 0, y: -6 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0, y: -6 }}
                            className="mb-4 rounded-xl border border-sky-500/30 bg-sky-500/10 px-3 py-1.5 text-center text-xs font-semibold text-sky-400 shadow-sm"
                        >
                            {toastMessage}
                        </motion.div>
                    )}
                </AnimatePresence>

                {activeTab === 'today' ? (
                    <div className="space-y-4">
                        {/* Headline Progress + Ring */}
                        <div className="flex items-center gap-4">
                            <ProgressRing
                                value={percentage}
                                size={92}
                                strokeWidth={9}
                                trackClassName="text-sky-500/20"
                                progressClassName="text-sky-400"
                            >
                                <span className="text-xl font-extrabold leading-none text-foreground">
                                    {Math.round(percentage)}%
                                </span>
                                <span className="mt-1 text-[10px] font-medium uppercase tracking-wide text-muted-foreground">
                                    done
                                </span>
                            </ProgressRing>

                            <div className="min-w-0 flex-1">
                                <p className="text-2xl font-extrabold leading-none text-foreground sm:text-3xl">
                                    {consumed.toLocaleString()}
                                    <span className="text-base font-semibold text-muted-foreground">
                                        {' '}
                                        / {target.toLocaleString()} ml
                                    </span>
                                </p>
                                <p className={`mt-2 text-xs font-semibold leading-snug ${statusColor}`}>
                                    {statusText}
                                </p>

                                {/* Slim Liquid Progress Bar */}
                                <div className="mt-3 relative h-2 w-full rounded-full bg-muted overflow-hidden">
                                    <motion.div
                                        className={cn(
                                            'h-full rounded-full',
                                            percentage >= 100
                                                ? 'bg-gradient-to-r from-emerald-500 to-teal-400'
                                                : 'bg-gradient-to-r from-sky-500 to-cyan-400'
                                        )}
                                        initial={{ width: 0 }}
                                        animate={{ width: `${percentage}%` }}
                                        transition={{ duration: 0.6, ease: 'easeOut' }}
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Coach Guidance Banner (if available) */}
                        {calcData.admin_notes && (
                            <div className="flex items-start gap-2.5 rounded-xl border border-primary/20 bg-primary/5 p-3 text-xs text-foreground">
                                <MessageSquare className="h-4 w-4 text-primary shrink-0 mt-0.5" />
                                <div className="leading-relaxed">
                                    <strong className="font-semibold text-primary">Coach Guidance: </strong>
                                    <span className="text-muted-foreground">{calcData.admin_notes}</span>
                                </div>
                            </div>
                        )}

                        {/* Quick Add Section */}
                        <div className="border-t border-border pt-4">
                            <div className="mb-3 flex items-center justify-between">
                                <span className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                    Quick Log
                                </span>
                                {lastEntry && (
                                    <button
                                        type="button"
                                        disabled={isUndoing}
                                        onClick={handleUndoLast}
                                        className="inline-flex items-center gap-1 text-[11px] font-semibold text-muted-foreground hover:text-destructive transition-colors disabled:opacity-50"
                                        title="Undo last logged intake"
                                    >
                                        <RotateCcw className="h-3 w-3" />
                                        Undo {lastEntry.amount_ml}ml
                                    </button>
                                )}
                            </div>

                            <div className="grid grid-cols-4 gap-2 sm:gap-3">
                                <button
                                    type="button"
                                    disabled={isLogging !== null}
                                    onClick={() => handleLogIntake(250, 'cup')}
                                    className="flex flex-col items-center justify-center p-3 rounded-xl border border-border bg-secondary/30 hover:bg-secondary/70 hover:border-sky-500/40 text-foreground transition-all duration-200 group active:scale-95 text-center disabled:opacity-50"
                                >
                                    <GlassWater className="h-4 w-4 text-sky-400 group-hover:scale-110 transition-transform" />
                                    <span className="mt-1 text-xs font-bold text-foreground">+250 ml</span>
                                    <span className="text-[10px] text-muted-foreground">Glass</span>
                                </button>

                                <button
                                    type="button"
                                    disabled={isLogging !== null}
                                    onClick={() => handleLogIntake(500, 'bottle')}
                                    className="flex flex-col items-center justify-center p-3 rounded-xl border border-border bg-secondary/30 hover:bg-secondary/70 hover:border-sky-500/40 text-foreground transition-all duration-200 group active:scale-95 text-center disabled:opacity-50"
                                >
                                    <Droplets className="h-4 w-4 text-sky-400 group-hover:scale-110 transition-transform" />
                                    <span className="mt-1 text-xs font-bold text-foreground">+500 ml</span>
                                    <span className="text-[10px] text-muted-foreground">Bottle</span>
                                </button>

                                <button
                                    type="button"
                                    disabled={isLogging !== null}
                                    onClick={() => handleLogIntake(750, 'shaker')}
                                    className="flex flex-col items-center justify-center p-3 rounded-xl border border-border bg-secondary/30 hover:bg-secondary/70 hover:border-sky-500/40 text-foreground transition-all duration-200 group active:scale-95 text-center disabled:opacity-50"
                                >
                                    <CupSoda className="h-4 w-4 text-sky-400 group-hover:scale-110 transition-transform" />
                                    <span className="mt-1 text-xs font-bold text-foreground">+750 ml</span>
                                    <span className="text-[10px] text-muted-foreground">Shaker</span>
                                </button>

                                <button
                                    type="button"
                                    onClick={() => setShowCustomInput(!showCustomInput)}
                                    className={cn(
                                        'flex flex-col items-center justify-center p-3 rounded-xl border transition-all duration-200 group active:scale-95 text-center',
                                        showCustomInput
                                            ? 'border-sky-500 bg-sky-500/10 text-sky-400'
                                            : 'border-border bg-secondary/30 hover:bg-secondary/70 hover:border-sky-500/40 text-foreground'
                                    )}
                                >
                                    <Plus className="h-4 w-4 text-sky-400 group-hover:scale-110 transition-transform" />
                                    <span className="mt-1 text-xs font-bold">Custom</span>
                                    <span className="text-[10px] text-muted-foreground">Amount</span>
                                </button>
                            </div>

                            {/* Custom Amount Form */}
                            <AnimatePresence>
                                {showCustomInput && (
                                    <motion.form
                                        initial={{ opacity: 0, height: 0 }}
                                        animate={{ opacity: 1, height: 'auto' }}
                                        exit={{ opacity: 0, height: 0 }}
                                        onSubmit={handleCustomSubmit}
                                        className="mt-3 flex gap-2 overflow-hidden"
                                    >
                                        <Input
                                            type="number"
                                            placeholder="Enter ml (e.g. 350)"
                                            value={customAmount}
                                            onChange={(e) => setCustomAmount(e.target.value)}
                                            min={50}
                                            max={2000}
                                            step={25}
                                            autoFocus
                                            className="h-9 text-xs bg-background border-border"
                                        />
                                        <Button
                                            type="submit"
                                            size="sm"
                                            disabled={isLogging !== null || !customAmount}
                                            className="h-9 px-4 text-xs font-semibold bg-sky-500 hover:bg-sky-400 text-black shrink-0"
                                        >
                                            Log
                                        </Button>
                                    </motion.form>
                                )}
                            </AnimatePresence>
                        </div>
                    </div>
                ) : (
                    /* 7-Day Adherence Chart Tab */
                    <WaterWeeklyChart stats={weeklyStats} targetMl={target} />
                )}
            </motion.div>

            {/* Hydration Calculator & Goal Setting Modal */}
            <HydrationCalculatorModal
                isOpen={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                calculation={calcData}
                customTargetMl={log.custom_target_ml}
                effectiveTargetMl={target}
                onTargetUpdated={handleTargetUpdated}
            />
        </>
    );
}
