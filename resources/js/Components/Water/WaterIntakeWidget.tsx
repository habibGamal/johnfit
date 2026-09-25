import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Input } from '@/Components/ui/input';
import {
    Droplets,
    Plus,
    RotateCcw,
    Info,
    CheckCircle2,
    Calendar,
    MessageSquare,
    Sparkles,
    GlassWater,
    CupSoda,
    TrendingUp
} from 'lucide-react';
import { UserDailyWaterLog, WaterCalculation, WaterWeeklyStats, UserWaterEntry } from '@/types/water';
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
                    showToast('🎉 Goal Reached! Excellent hydration today!');
                } else {
                    showToast(`+${amountMl}ml logged successfully!`);
                }

                // Refresh weekly stats
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

    return (
        <>
            <Card className={`relative overflow-hidden border border-border/70 shadow-sm bg-gradient-to-b from-card to-card/90 ${className}`}>
                {/* Subtle Water Shimmer Background */}
                <div
                    className="absolute -top-24 -right-24 w-52 h-52 rounded-full bg-blue-500/5 blur-3xl pointer-events-none"
                    aria-hidden="true"
                />

                <CardHeader className="pb-3 pt-5 px-5 flex flex-row items-center justify-between">
                    <div className="flex items-center gap-2.5">
                        <div className="p-2 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                            <Droplets className="w-5 h-5" />
                        </div>
                        <div>
                            <CardTitle className="text-base font-bold flex items-center gap-2">
                                Water Intake
                                {log.is_completed && (
                                    <Badge className="bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/30 text-[11px] font-semibold py-0.5">
                                        Goal Achieved
                                    </Badge>
                                )}
                            </CardTitle>
                            <p className="text-xs text-muted-foreground mt-0.5">
                                {calcData.tier_name}
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-1.5">
                        {/* Tab Switcher */}
                        <div className="flex p-0.5 rounded-lg bg-muted/60 border border-border/40 text-xs">
                            <button
                                type="button"
                                onClick={() => setActiveTab('today')}
                                className={`px-2.5 py-1 rounded-md font-medium transition-all ${
                                    activeTab === 'today'
                                        ? 'bg-background text-foreground shadow-xs'
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                Today
                            </button>
                            <button
                                type="button"
                                onClick={() => setActiveTab('history')}
                                className={`px-2.5 py-1 rounded-md font-medium transition-all ${
                                    activeTab === 'history'
                                        ? 'bg-background text-foreground shadow-xs'
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                History
                            </button>
                        </div>

                        {/* Info / Target Modal Trigger */}
                        <Button
                            variant="ghost"
                            size="icon"
                            onClick={() => setIsModalOpen(true)}
                            className="h-8 w-8 text-muted-foreground hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/40"
                            title="Hydration Breakdown & Goal Settings"
                        >
                            <Info className="w-4 h-4" />
                        </Button>
                    </div>
                </CardHeader>

                <CardContent className="px-5 pb-5 pt-1 space-y-4">
                    {/* Toast Notification */}
                    <AnimatePresence>
                        {toastMessage && (
                            <motion.div
                                initial={{ opacity: 0, y: -6 }}
                                animate={{ opacity: 1, y: 0 }}
                                exit={{ opacity: 0, y: -6 }}
                                className="text-xs font-medium text-center py-1.5 px-3 rounded-lg bg-blue-600 text-white shadow-sm"
                            >
                                {toastMessage}
                            </motion.div>
                        )}
                    </AnimatePresence>

                    {activeTab === 'today' ? (
                        <>
                            {/* Water Progress Gauge Bar / Liquid Display */}
                            <div className="p-4 rounded-2xl bg-gradient-to-br from-blue-50/60 via-background to-blue-50/30 dark:from-blue-950/20 dark:via-card dark:to-blue-950/10 border border-blue-100/80 dark:border-blue-900/30">
                                <div className="flex items-end justify-between mb-2">
                                    <div>
                                        <span className="text-xs text-muted-foreground font-medium block">
                                            Consumed Today
                                        </span>
                                        <div className="flex items-baseline gap-1 mt-0.5">
                                            <span className="text-2xl font-black text-foreground tracking-tight">
                                                {consumed.toLocaleString()}
                                            </span>
                                            <span className="text-xs text-muted-foreground font-semibold">
                                                / {target.toLocaleString()} ml
                                            </span>
                                        </div>
                                    </div>
                                    <div className="text-right">
                                        <span className="text-xl font-bold text-blue-600 dark:text-blue-400">
                                            {percentage}%
                                        </span>
                                        <span className="text-[11px] text-muted-foreground block">
                                            {remaining > 0 ? `${remaining.toLocaleString()} ml left` : 'Completed!'}
                                        </span>
                                    </div>
                                </div>

                                {/* Animated Fluid Progress Reservoir */}
                                <div className="relative h-3 w-full bg-blue-100/80 dark:bg-blue-950/60 rounded-full overflow-hidden">
                                    <motion.div
                                        className={`h-full rounded-full ${
                                            percentage >= 100
                                                ? 'bg-gradient-to-r from-emerald-500 to-teal-400'
                                                : 'bg-gradient-to-r from-blue-500 via-sky-400 to-blue-600'
                                        }`}
                                        initial={{ width: 0 }}
                                        animate={{ width: `${percentage}%` }}
                                        transition={{ duration: 0.6, ease: 'easeOut' }}
                                    />
                                </div>
                            </div>

                            {/* Coach Notes Banner (if provided) */}
                            {calcData.admin_notes && (
                                <div className="flex items-start gap-2 p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-900 dark:text-amber-200">
                                    <MessageSquare className="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 mt-0.5 shrink-0" />
                                    <span className="line-clamp-2">
                                        <strong className="font-semibold text-amber-700 dark:text-amber-300">Coach: </strong>
                                        {calcData.admin_notes}
                                    </span>
                                </div>
                            )}

                            {/* One-Tap Quick Log Presets */}
                            <div>
                                <div className="flex items-center justify-between mb-2">
                                    <span className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                                        Quick Add
                                    </span>
                                    {lastEntry && (
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            disabled={isUndoing}
                                            onClick={handleUndoLast}
                                            className="h-6 text-[11px] text-muted-foreground hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 px-2 flex items-center gap-1"
                                            title="Undo last logged intake"
                                        >
                                            <RotateCcw className="w-3 h-3" />
                                            Undo {lastEntry.amount_ml}ml
                                        </Button>
                                    )}
                                </div>

                                <div className="grid grid-cols-4 gap-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        disabled={isLogging !== null}
                                        onClick={() => handleLogIntake(250, 'cup')}
                                        className="h-14 flex-col gap-1 border-border/70 hover:border-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-950/30 transition-all rounded-xl"
                                    >
                                        <GlassWater className="w-4 h-4 text-blue-500" />
                                        <span className="text-xs font-bold">+250 ml</span>
                                        <span className="text-[10px] text-muted-foreground font-normal">Glass</span>
                                    </Button>

                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        disabled={isLogging !== null}
                                        onClick={() => handleLogIntake(500, 'bottle')}
                                        className="h-14 flex-col gap-1 border-border/70 hover:border-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-950/30 transition-all rounded-xl"
                                    >
                                        <Droplets className="w-4 h-4 text-sky-500" />
                                        <span className="text-xs font-bold">+500 ml</span>
                                        <span className="text-[10px] text-muted-foreground font-normal">Bottle</span>
                                    </Button>

                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        disabled={isLogging !== null}
                                        onClick={() => handleLogIntake(750, 'shaker')}
                                        className="h-14 flex-col gap-1 border-border/70 hover:border-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-950/30 transition-all rounded-xl"
                                    >
                                        <CupSoda className="w-4 h-4 text-indigo-500" />
                                        <span className="text-xs font-bold">+750 ml</span>
                                        <span className="text-[10px] text-muted-foreground font-normal">Shaker</span>
                                    </Button>

                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onClick={() => setShowCustomInput(!showCustomInput)}
                                        className={`h-14 flex-col gap-1 border-border/70 transition-all rounded-xl ${
                                            showCustomInput
                                                ? 'border-blue-500 bg-blue-50/70 dark:bg-blue-950/40 text-blue-600'
                                                : 'hover:border-blue-300 hover:bg-blue-50/60 dark:hover:bg-blue-950/30'
                                        }`}
                                    >
                                        <Plus className="w-4 h-4 text-slate-500" />
                                        <span className="text-xs font-bold">Custom</span>
                                        <span className="text-[10px] text-muted-foreground font-normal">Amount</span>
                                    </Button>
                                </div>

                                {/* Custom Amount Pop-in */}
                                <AnimatePresence>
                                    {showCustomInput && (
                                        <motion.form
                                            initial={{ opacity: 0, height: 0 }}
                                            animate={{ opacity: 1, height: 'auto' }}
                                            exit={{ opacity: 0, height: 0 }}
                                            onSubmit={handleCustomSubmit}
                                            className="pt-2 flex gap-2 overflow-hidden"
                                        >
                                            <Input
                                                type="number"
                                                placeholder="Amount in ml (e.g. 330)"
                                                value={customAmount}
                                                onChange={(e) => setCustomAmount(e.target.value)}
                                                min={50}
                                                max={2000}
                                                step={25}
                                                autoFocus
                                                className="h-9 text-xs"
                                            />
                                            <Button
                                                type="submit"
                                                size="sm"
                                                disabled={isLogging !== null || !customAmount}
                                                className="h-9 px-4 text-xs bg-blue-600 hover:bg-blue-700 text-white shrink-0"
                                            >
                                                Log
                                            </Button>
                                        </motion.form>
                                    )}
                                </AnimatePresence>
                            </div>
                        </>
                    ) : (
                        /* 7-Day Adherence Chart Tab */
                        <WaterWeeklyChart stats={weeklyStats} targetMl={target} />
                    )}
                </CardContent>
            </Card>

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
