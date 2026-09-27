import { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { SchedulePageProps, UserDailyItem, WeeklyAdherenceDay } from '@/types';
import WorkoutSetModal from './Components/WorkoutSetModal';
import MealLoggerModal from './Components/MealLoggerModal';
import { cn } from '@/lib/utils';
import axios from 'axios';
import {
    Dumbbell,
    Play,
    Star,
    CheckCircle2,
    Flame,
    Sparkles,
    Plus,
    Droplets,
    Sunrise,
    Utensils,
    Cookie,
    Moon,
    Wheat,
    Droplet,
    Lock,
    ChevronLeft,
    ChevronRight,
} from 'lucide-react';

type ScheduleTab = 'workouts' | 'meals';

export default function ScheduleIndex({
    auth: _auth,
    selectedDate,
    schedule,
    weeklyAdherence,
    currentStreak,
    waterData,
    aiPlanEligibility,
}: SchedulePageProps) {
    const [activeTab, setActiveTab] = useState<ScheduleTab>('workouts');
    const [selectedWorkoutItem, setSelectedWorkoutItem] = useState<UserDailyItem | null>(null);
    const [isWorkoutModalOpen, setIsWorkoutModalOpen] = useState(false);

    const [selectedMealItem, setSelectedMealItem] = useState<UserDailyItem | null>(null);
    const [isMealModalOpen, setIsMealModalOpen] = useState(false);

    const [isGenerating, setIsGenerating] = useState(false);
    const [waterConsumed, setWaterConsumed] = useState(waterData?.log?.consumed_ml || 0);
    const [isLoggingWater, setIsLoggingWater] = useState(false);

    useEffect(() => {
        setWaterConsumed(waterData?.log?.consumed_ml || 0);
    }, [waterData, selectedDate]);

    const isLocked = schedule?.is_locked ?? false;
    const items = [...(schedule?.items ?? [])].sort((a, b) => a.order_index - b.order_index);

    // Past & future days are view-only: content is visible but cannot be edited.
    const todayStr = new Date().toISOString().split('T')[0];
    const isEditable = selectedDate === todayStr && !isLocked;
    const isViewOnly = !isEditable;
    const isPastDay = selectedDate < todayStr;

    const workoutItems = items.filter((i) => i.type === 'workout');
    const mealItems = items.filter((i) => i.type === 'meal');

    const completedWorkouts = workoutItems.filter((i) => i.is_completed).length;
    const completedMeals = mealItems.filter((i) => i.is_completed).length;

    const getMealOption = (item: UserDailyItem) => {
        const targetDetails = item.target_details || {};
        const options = (targetDetails.options || []) as any[];
        const consumedOptionId = item.execution_payload?.consumed_option_id || item.reference_id;
        return (
            options.find((opt: any) => opt.meal_id === consumedOptionId) ||
            targetDetails.primary_option ||
            options[0] ||
            {}
        );
    };

    const getWorkoutOption = (item: UserDailyItem) => {
        const targetDetails = item.target_details || {};
        const options = (targetDetails.options || []) as any[];
        const selectedWorkoutId = item.execution_payload?.selected_workout_id || item.reference_id;
        return (
            options.find((opt: any) => opt.workout_id === selectedWorkoutId) ||
            options[0] ||
            {}
        );
    };

    // Nutrition totals across today's meals
    const nutritionTotals = mealItems.reduce(
        (acc, item) => {
            const option = getMealOption(item);
            const ratio = item.is_completed
                ? (item.execution_payload?.consumed_quantity || option.quantity || 100) /
                (option.quantity || 100)
                : 0;
            acc.calories += Math.round((option.calories || 0) * ratio);
            acc.protein += Math.round((option.protein || 0) * ratio * 10) / 10;
            acc.carbs += Math.round((option.carbs || 0) * ratio * 10) / 10;
            acc.fat += Math.round((option.fat || 0) * ratio * 10) / 10;
            acc.targetCalories += Math.round(option.calories || 0);
            acc.targetProtein += Math.round(option.protein || 0);
            acc.targetCarbs += Math.round(option.carbs || 0);
            acc.targetFat += Math.round(option.fat || 0);
            return acc;
        },
        {
            calories: 0,
            protein: 0,
            carbs: 0,
            fat: 0,
            targetCalories: 0,
            targetProtein: 0,
            targetCarbs: 0,
            targetFat: 0,
        }
    );

    const caloriesConsumedPct =
        nutritionTotals.targetCalories > 0
            ? Math.min(
                100,
                Math.round((nutritionTotals.calories / nutritionTotals.targetCalories) * 100)
            )
            : 0;
    const caloriesRemaining = Math.max(0, nutritionTotals.targetCalories - nutritionTotals.calories);

    const macroPct = (consumed: number, target: number) =>
        target > 0 ? Math.min(100, Math.round((consumed / target) * 100)) : 0;

    const earnedScore = schedule?.earned_score ?? 0;
    const targetScore = schedule?.target_score ?? 0;
    const adherence = schedule?.adherence_percentage ?? 0;
    const workoutsLeft = workoutItems.length - completedWorkouts;

    const handleSelectDate = (dateStr: string) => {
        if (dateStr === selectedDate) return;
        router.get(route('schedule.index'), { date: dateStr }, { preserveState: true, preserveScroll: true });
    };

    const handleShiftWeek = (direction: 1 | -1) => {
        const current = new Date(selectedDate);
        current.setDate(current.getDate() + direction * 7);
        handleSelectDate(current.toISOString().split('T')[0]);
    };

    const handleGeneratePlans = () => {
        setIsGenerating(true);
        router.post(route('plans.generate'), {}, { onFinish: () => setIsGenerating(false) });
    };


    const handleOpenWorkoutModal = (item: UserDailyItem) => {
        if (!isEditable) return;
        setSelectedWorkoutItem(item);
        setIsWorkoutModalOpen(true);
    };

    const handleOpenMealModal = (item: UserDailyItem) => {
        if (!isEditable) return;
        setSelectedMealItem(item);
        setIsMealModalOpen(true);
    };

    const getMealSlotIcon = (slot: string) => {
        const s = slot.toLowerCase();
        if (s.includes('break') || s.includes('morn')) return Sunrise;
        if (s.includes('snack')) return Cookie;
        if (s.includes('dinner') || s.includes('even') || s.includes('night')) return Moon;
        return Utensils;
    };

    const renderDateStrip = () => {
        const isTodaySelected =
            new Date().toISOString().split('T')[0] === selectedDate;
        return (
            <div className="px-4 pt-6">
                <div className="bg-card/60 border border-border rounded-2xl p-2.5 space-y-2">
                    {/* Header: month + week shift controls */}
                    <div className="flex items-center justify-between px-1">
                        <span className="text-sm font-bold text-foreground">
                            {new Date(selectedDate).toLocaleDateString('en-US', {
                                month: 'long',
                                year: 'numeric',
                            })}
                            {isTodaySelected && (
                                <span className="ml-2 text-[10px] font-bold uppercase tracking-wider text-primary align-middle">
                                    This Week
                                </span>
                            )}
                        </span>
                        <div className="flex items-center gap-1">
                            <button
                                type="button"
                                onClick={() => handleShiftWeek(-1)}
                                className="h-7 w-7 rounded-lg bg-secondary flex items-center justify-center text-muted-foreground hover:text-primary hover:bg-secondary/80 transition-colors"
                                aria-label="Previous week"
                            >
                                <ChevronLeft className="h-4 w-4" />
                            </button>
                            <button
                                type="button"
                                onClick={() => handleShiftWeek(1)}
                                className="h-7 w-7 rounded-lg bg-secondary flex items-center justify-center text-muted-foreground hover:text-primary hover:bg-secondary/80 transition-colors"
                                aria-label="Next week"
                            >
                                <ChevronRight className="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    {/* Days strip */}
                    <div className="grid grid-cols-7 gap-1.5">
                        {weeklyAdherence.days.map((day: WeeklyAdherenceDay) => {
                            const isSelected = day.date === selectedDate;
                            const dayNumber = new Date(day.date).getDate();
                            return (
                                <button
                                    key={day.date}
                                    type="button"
                                    onClick={() => handleSelectDate(day.date)}
                                    className={cn(
                                        'flex flex-col items-center justify-center gap-1 py-2.5 rounded-xl border transition-all',
                                        isSelected
                                            ? 'bg-primary border-primary text-primary-foreground'
                                            : 'bg-secondary/40 border-border/60 hover:bg-secondary',
                                        !isSelected && day.is_today && 'ring-1 ring-primary/40'
                                    )}
                                >
                                    <span
                                        className={cn(
                                            'text-[10px] font-semibold uppercase tracking-wider',
                                            isSelected ? 'text-primary-foreground' : 'text-muted-foreground'
                                        )}
                                    >
                                        {day.day_name.slice(0, 3)}
                                    </span>
                                    <span
                                        className={cn(
                                            'text-base leading-none font-bold',
                                            isSelected ? 'text-lg text-primary-foreground' : 'text-foreground'
                                        )}
                                    >
                                        {dayNumber}
                                    </span>
                                    <span className="flex items-center justify-center h-2.5">
                                        {day.is_locked ? (
                                            <Lock className="h-2.5 w-2.5 text-muted-foreground/60" />
                                        ) : day.is_completed ? (
                                            <CheckCircle2
                                                className={cn(
                                                    'h-3 w-3',
                                                    isSelected ? 'text-primary-foreground' : 'text-primary'
                                                )}
                                            />
                                        ) : day.percentage > 0 ? (
                                            <span
                                                className={cn(
                                                    'text-[9px] font-semibold leading-none',
                                                    isSelected ? 'text-primary-foreground/90' : 'text-muted-foreground'
                                                )}
                                            >
                                                {day.percentage}%
                                            </span>
                                        ) : (
                                            <span className="block h-1 w-1 rounded-full bg-border" />
                                        )}
                                    </span>
                                </button>
                            );
                        })}
                    </div>
                </div>
            </div>
        );
    };

    const renderTabSwitcher = () => (
        <div className="mx-4 mt-4 bg-card border border-border rounded-xl p-1 grid grid-cols-2">
            <button
                type="button"
                onClick={() => setActiveTab('workouts')}
                className={cn(
                    'flex items-center justify-center gap-2 py-2.5 text-sm font-bold uppercase tracking-wider rounded-lg transition-all',
                    activeTab === 'workouts'
                        ? 'bg-primary text-primary-foreground shadow-[0_0_16px_rgba(255,189,51,0.25)]'
                        : 'text-muted-foreground hover:text-foreground'
                )}
            >
                <Dumbbell className="h-4 w-4" />
                Workouts ({completedWorkouts}/{workoutItems.length})
            </button>
            <button
                type="button"
                onClick={() => setActiveTab('meals')}
                className={cn(
                    'flex items-center justify-center gap-2 py-2.5 text-sm font-bold uppercase tracking-wider rounded-lg transition-all',
                    activeTab === 'meals'
                        ? 'bg-primary text-primary-foreground shadow-[0_0_16px_rgba(255,189,51,0.25)]'
                        : 'text-muted-foreground hover:text-foreground'
                )}
            >
                <Utensils className="h-4 w-4" />
                Meals ({completedMeals}/{mealItems.length})
            </button>
        </div>
    );

    return (
        <AuthenticatedLayout>
            <Head title="Daily Schedule & Plans - JohnFit" />

            <div className="min-h-screen bg-background pb-16">
                <div className="max-w-md mx-auto">
                    {/* Date strip */}
                    {renderDateStrip()}

                    {/* Tab switcher */}
                    {renderTabSwitcher()}

                    {/* View-only notice for past & future days */}
                    {isViewOnly && (
                        <div className="mx-4 mt-3 flex items-center justify-center gap-2 text-xs text-muted-foreground bg-card/60 border border-border rounded-xl px-4 py-2.5">
                            <Lock className="h-3.5 w-3.5 shrink-0" />
                            {isPastDay
                                ? 'You are viewing a past day — tracking is read-only.'
                                : 'You are viewing an upcoming day — tracking opens on the day itself.'}
                        </div>
                    )}

                    {/* ==================== WORKOUT TRACKER ==================== */}
                    {activeTab === 'workouts' && (
                        <>
                            {/* Weekly Goal Banner */}
                            <section className="px-4 mt-4">
                                <div className="bg-card border border-border rounded-2xl p-6 relative overflow-hidden">
                                    <div className="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full blur-2xl -mr-10 -mt-10 pointer-events-none" />
                                    <div className="flex flex-col gap-4 relative z-0">
                                        <div className="flex justify-between items-center">
                                            <div>
                                                <p className="text-lg font-bold text-foreground">Weekly Goal</p>
                                                <p className="text-muted-foreground text-xs flex items-center gap-1">
                                                    Keep the streak alive!
                                                    <Flame className="h-3 w-3 text-orange-500 fill-orange-500" />
                                                    {currentStreak} {currentStreak === 1 ? 'day' : 'days'}
                                                </p>
                                            </div>
                                            <div className="bg-secondary px-3 py-1 rounded-full border border-border backdrop-blur-sm">
                                                <span className="text-primary font-bold text-sm">
                                                    {earnedScore} / {targetScore}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="space-y-2">
                                            <div className="rounded-full bg-secondary h-3 overflow-hidden">
                                                <div
                                                    className="h-full rounded-full bg-primary shadow-[0_0_10px_rgba(255,189,51,0.5)] transition-all duration-500"
                                                    style={{ width: `${Math.min(100, adherence)}%` }}
                                                />
                                            </div>
                                            <p className="text-primary text-xs font-medium text-right">
                                                {targetScore > 0
                                                    ? workoutsLeft > 0
                                                        ? `${workoutsLeft} workout${workoutsLeft === 1 ? '' : 's'} left`
                                                        : 'All workouts done!'
                                                    : 'No targets scheduled'}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            {/* Today's Schedule */}
                            <section className="px-4 mt-6">
                                <h3 className="text-xl font-bold mb-4 text-foreground flex items-center gap-2">
                                    Today&apos;s Schedule
                                    {isLocked && <Lock className="h-4 w-4 text-muted-foreground" />}
                                </h3>
                                {workoutItems.length === 0 ? (
                                    /* Empty State */
                                    <div className="bg-card border border-dashed border-border rounded-2xl p-8 sm:p-12 text-center space-y-4">
                                        <div className="mx-auto w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                                            <Sparkles className="h-7 w-7" />
                                        </div>
                                        <div className="space-y-1.5 max-w-md mx-auto">
                                            <h3 className="text-lg font-bold text-foreground">
                                                No Plan Schedule for this Day
                                            </h3>
                                            <p className="text-xs sm:text-sm text-muted-foreground">
                                                You don&apos;t have active workout or meal plan items materialized for
                                                this date. Generate a personalized plan or check your upcoming active
                                                plan window.
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={handleGeneratePlans}
                                            disabled={isGenerating || (aiPlanEligibility?.can_generate === false)}
                                            className="w-full bg-primary hover:bg-[#e6aa2e] disabled:opacity-50 text-primary-foreground font-black tracking-wide py-4 text-center text-sm uppercase transition-colors rounded-lg flex items-center justify-center gap-2 disabled:cursor-not-allowed"
                                        >
                                            <Sparkles className="h-4 w-4" />
                                            {isGenerating
                                                ? 'GENERATING...'
                                                : aiPlanEligibility?.can_generate === false
                                                    ? 'AI PLAN ALREADY GENERATED'
                                                    : 'GENERATE AI PLAN'}
                                        </button>
                                        {aiPlanEligibility?.can_generate === false && (
                                            <p className="text-xs text-muted-foreground">
                                                Each account is eligible for one AI plan generation. You have already generated your custom plan.
                                            </p>
                                        )}
                                    </div>
                                ) : (
                                    <div className="flex flex-col gap-5">
                                        {workoutItems.map((item) => {
                                            const option = getWorkoutOption(item);
                                            const thumb = option.thumb || item.target_details?.thumb;
                                            const activeMuscles =
                                                option.muscles || item.target_details?.muscles || '';
                                            const muscles = Array.isArray(activeMuscles)
                                                ? activeMuscles.join(', ')
                                                : activeMuscles;
                                            const toolsRaw = option.tools || item.target_details?.tools || '';
                                            const tools = Array.isArray(toolsRaw)
                                                ? toolsRaw
                                                : String(toolsRaw).split(',');
                                            const category = (tools[0] || '').trim() || 'Training';
                                            const setsCount =
                                                option.sets_count || item.target_details?.sets_count || 3;
                                            const targetReps =
                                                option.target_reps || item.target_details?.target_reps || [];
                                            const setsDescription =
                                                targetReps.length > 0
                                                    ? `${setsCount} sets × ${targetReps.join('-')} reps`
                                                    : `${setsCount} sets`;

                                            return (
                                                <div
                                                    key={item.id}
                                                    onClick={() => handleOpenWorkoutModal(item)}
                                                    className={cn(
                                                        'bg-card border rounded-2xl p-0 overflow-hidden group transition-all',
                                                        !isEditable &&
                                                        'opacity-60 saturate-[0.6] pointer-events-none select-none',
                                                        isEditable && 'cursor-pointer hover:border-primary/40',
                                                        item.is_completed && isEditable
                                                            ? 'border-primary/30 shadow-lg shadow-black/50'
                                                            : 'border-border'
                                                    )}
                                                >
                                                    <div
                                                        className="p-5 flex flex-col gap-4 relative bg-cover bg-center"
                                                        style={
                                                            thumb
                                                                ? {
                                                                    backgroundImage:
                                                                        'linear-gradient(to right, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.4) 100%), url(\'' +
                                                                        thumb +
                                                                        '\')',
                                                                }
                                                                : undefined
                                                        }
                                                    >
                                                        <div className="flex justify-between items-start">
                                                            <div>
                                                                <div className="flex items-center gap-2 mb-2">
                                                                    <span
                                                                        className={cn(
                                                                            'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider',
                                                                            item.is_completed
                                                                                ? 'bg-primary text-black'
                                                                                : 'bg-secondary text-foreground/80'
                                                                        )}
                                                                    >
                                                                        {category}
                                                                    </span>
                                                                </div>
                                                                <h4
                                                                    className={cn(
                                                                        'text-xl font-bold text-foreground leading-tight',
                                                                        item.is_completed && 'line-through opacity-70'
                                                                    )}
                                                                >
                                                                    {item.item_name}
                                                                </h4>
                                                                <p className="text-muted-foreground text-sm mt-1 truncate max-w-[220px]">
                                                                    {[muscles, setsDescription]
                                                                        .filter(Boolean)
                                                                        .join(' • ')}
                                                                </p>
                                                            </div>
                                                            <div className="size-10 rounded-full bg-secondary flex items-center justify-center border border-border shrink-0">
                                                                {item.is_completed ? (
                                                                    <CheckCircle2 className="h-5 w-5 text-primary" />
                                                                ) : (
                                                                    <Dumbbell className="h-5 w-5 text-primary" />
                                                                )}
                                                            </div>
                                                        </div>
                                                        <div className="flex items-center justify-between pt-4 mt-1 border-t border-border/60">
                                                            <div className="flex items-center gap-1.5 text-primary">
                                                                <Star className="h-[18px] w-[18px] fill-primary" />
                                                                <span className="text-sm font-bold tracking-wider">
                                                                    +{item.points} PTS
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        disabled={!isEditable}
                                                        onClick={(e) => {
                                                            e.stopPropagation();
                                                            handleOpenWorkoutModal(item);
                                                        }}
                                                        className={cn(
                                                            'w-full font-bold tracking-wide py-4 text-center text-sm uppercase transition-all flex items-center justify-center gap-2 border-t',
                                                            item.is_completed
                                                                ? 'bg-primary/10 hover:bg-primary/20 text-primary border-border/60'
                                                                : 'bg-primary hover:bg-[#e6aa2e] text-primary-foreground border-transparent',
                                                            !isEditable &&
                                                            'opacity-50 cursor-not-allowed hover:bg-inherit'
                                                        )}
                                                    >
                                                        <Play className="h-4 w-4 fill-current" />
                                                        {item.is_completed ? 'VIEW SETS' : 'START WORKOUT'}
                                                    </button>
                                                </div>
                                            );
                                        })}
                                    </div>
                                )}
                            </section>
                        </>
                    )}

                    {/* ==================== MEAL & NUTRITION TRACKER ==================== */}
                    {activeTab === 'meals' && (
                        <>
                            {/* Daily Calories Remaining */}
                            {mealItems.length > 0 && (
                                <section className="mt-4">
                                    <div className="mx-4 bg-primary/10 border border-primary/20 rounded-xl p-6">
                                        <div className="flex flex-col gap-3">
                                            <div className="flex gap-6 justify-between items-end">
                                                <p className="text-base font-medium leading-normal text-foreground">
                                                    Daily Calories Remaining
                                                </p>
                                                <p className="text-primary text-2xl font-bold leading-none">
                                                    {caloriesRemaining.toLocaleString()}
                                                </p>
                                            </div>
                                            <div className="flex justify-between items-center text-xs text-muted-foreground">
                                                <span>
                                                    Goal: {nutritionTotals.targetCalories.toLocaleString()} kcal
                                                </span>
                                                <span>{caloriesConsumedPct}% Consumed</span>
                                            </div>
                                            <div className="rounded-full bg-secondary h-3 overflow-hidden">
                                                <div
                                                    className="h-full rounded-full bg-primary transition-all duration-500"
                                                    style={{ width: `${caloriesConsumedPct}%` }}
                                                />
                                            </div>
                                            <p className="text-primary/80 text-sm font-normal leading-normal">
                                                {nutritionTotals.calories.toLocaleString()} kcal logged •{' '}
                                                {completedMeals}/{mealItems.length} meals done
                                            </p>
                                        </div>
                                    </div>
                                </section>
                            )}

                            {/* Macro Cards */}
                            {mealItems.length > 0 && (
                                <section className="px-4 py-4 mt-2">
                                    <div className="flex gap-3 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                        {[
                                            {
                                                label: 'Protein',
                                                value: nutritionTotals.protein,
                                                target: nutritionTotals.targetProtein,
                                                unit: 'g',
                                                Icon: Flame,
                                            },
                                            {
                                                label: 'Carbs',
                                                value: nutritionTotals.carbs,
                                                target: nutritionTotals.targetCarbs,
                                                unit: 'g',
                                                Icon: Wheat,
                                            },
                                            {
                                                label: 'Fats',
                                                value: nutritionTotals.fat,
                                                target: nutritionTotals.targetFat,
                                                unit: 'g',
                                                Icon: Droplet,
                                            },
                                        ].map(({ label, value, target, unit, Icon }) => (
                                            <div
                                                key={label}
                                                className="flex min-w-[120px] flex-1 flex-col gap-2 rounded-xl p-4 border border-border bg-card"
                                            >
                                                <div className="flex justify-between items-start mb-1">
                                                    <p className="text-muted-foreground text-xs font-medium uppercase tracking-wider">
                                                        {label}
                                                    </p>
                                                    <Icon className="h-4 w-4 text-primary" />
                                                </div>
                                                <div className="flex flex-col gap-1">
                                                    <p className="text-xl font-bold tracking-tight text-foreground">
                                                        {value}
                                                        <span className="text-muted-foreground text-sm font-normal">
                                                            /{target}
                                                            {unit}
                                                        </span>
                                                    </p>
                                                    <div className="w-full bg-secondary h-1.5 rounded-full overflow-hidden">
                                                        <div
                                                            className="h-full bg-primary shadow-[0_0_8px_rgba(255,189,51,0.4)] transition-all duration-500"
                                                            style={{
                                                                width: `${macroPct(value, target)}%`,
                                                            }}
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </section>
                            )}

                            {/* Today's Timeline */}
                            <section className="mt-2">
                                <h2 className="text-xl font-bold leading-tight px-4 pb-4 text-foreground">
                                    Today&apos;s Timeline
                                </h2>
                                {mealItems.length === 0 ? (
                                    <div className="mx-4 bg-card border border-dashed border-border rounded-xl p-6 text-center text-sm text-muted-foreground">
                                        No meals scheduled for this day.
                                    </div>
                                ) : (
                                    <div className="grid grid-cols-[48px_1fr] gap-x-2 px-4">
                                        {mealItems.map((item, index) => {
                                            const option = getMealOption(item);
                                            const slot = item.target_details?.time_slot || 'Meal Time';
                                            const SlotIcon = getMealSlotIcon(slot);
                                            const isLast = index === mealItems.length - 1;
                                            const ratio = item.execution_payload?.consumed_quantity
                                                ? item.execution_payload.consumed_quantity /
                                                (option.quantity || 100)
                                                : 1;
                                            const optionCalories = Math.round((option.calories || 0) * ratio);
                                            const protein = Math.round((option.protein || 0) * ratio * 10) / 10;

                                            return (
                                                <div key={item.id} className="contents">
                                                    <div className="flex flex-col items-center gap-1 pt-3">
                                                        <div className="bg-primary/20 flex items-center justify-center rounded-full size-8">
                                                            <SlotIcon className="h-4 w-4 text-primary" />
                                                        </div>
                                                        {!isLast && (
                                                            <div className="w-[2px] bg-border h-full grow min-h-[24px]" />
                                                        )}
                                                    </div>
                                                    <div
                                                        onClick={() => handleOpenMealModal(item)}
                                                        className={cn(
                                                            'flex flex-1 flex-col py-3 transition-opacity',
                                                            !isLast && 'border-b border-border/60',
                                                            !isEditable &&
                                                            'opacity-60 saturate-[0.6] pointer-events-none select-none',
                                                            isEditable && 'cursor-pointer group'
                                                        )}
                                                    >
                                                        <div className="flex justify-between items-start">
                                                            <div>
                                                                <p className="text-base font-medium text-foreground group-hover:text-primary transition-colors">
                                                                    {slot}
                                                                </p>
                                                                <p className="text-muted-foreground text-sm">
                                                                    {optionCalories > 0 &&
                                                                        `${optionCalories.toLocaleString()} kcal`}
                                                                    {protein > 0 && ` • ${protein}g P`}
                                                                    {item.points > 0 && ` • +${item.points} pts`}
                                                                </p>
                                                                {option.name && (
                                                                    <p className="text-primary text-xs mt-1 line-clamp-1">
                                                                        {option.name}
                                                                    </p>
                                                                )}
                                                            </div>
                                                            {isEditable ? (
                                                                <button
                                                                    type="button"
                                                                    onClick={(e) => {
                                                                        e.stopPropagation();
                                                                        handleOpenMealModal(item);
                                                                    }}
                                                                    className={cn(
                                                                        'flex items-center gap-1.5 shrink-0 mt-1 text-xs font-semibold px-3 py-1.5 rounded-full border transition-colors',
                                                                        item.is_completed
                                                                            ? 'border-primary/40 text-primary bg-primary/10'
                                                                            : 'border-border text-muted-foreground hover:text-primary hover:border-primary/40'
                                                                    )}
                                                                >
                                                                    <CheckCircle2 className="h-3.5 w-3.5" />
                                                                    {item.is_completed ? 'Done' : 'Log Portion'}
                                                                </button>
                                                            ) : (
                                                                <span
                                                                    className={cn(
                                                                        'flex items-center gap-1.5 shrink-0 mt-1 text-xs font-semibold px-3 py-1.5 rounded-full border border-border text-muted-foreground',
                                                                        item.is_completed && 'text-primary/70 border-primary/20'
                                                                    )}
                                                                >
                                                                    {item.is_completed ? (
                                                                        <>
                                                                            <CheckCircle2 className="h-3.5 w-3.5" />
                                                                            Done
                                                                        </>
                                                                    ) : (
                                                                        'Pending'
                                                                    )}
                                                                </span>
                                                            )}
                                                        </div>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </div>
                                )}
                            </section>

                            {/* Water Intake */}
                            {mealItems.length > 0 && (() => {
                                const waterTarget = waterData?.log?.effective_target_ml || 2500;
                                const glassesCount = Math.max(1, Math.min(16, Math.round(waterTarget / 250)));
                                const filledGlasses = Math.min(glassesCount, Math.floor(waterConsumed / 250));

                                const handleAddGlass = async () => {
                                    if (isViewOnly || isLoggingWater) return;
                                    setIsLoggingWater(true);
                                    try {
                                        const res = await axios.post('/water/log', {
                                            amount_ml: 250,
                                            container_type: 'cup',
                                            date: selectedDate,
                                        });
                                        if (res.data?.success && res.data?.log) {
                                            setWaterConsumed(res.data.log.consumed_ml);
                                        }
                                    } catch (e) {
                                        // silent
                                    } finally {
                                        setIsLoggingWater(false);
                                    }
                                };

                                return (
                                    <section className="mt-6 px-4 pb-8">
                                        <div className="bg-card rounded-xl p-4 border border-border">
                                            <div className="flex justify-between items-center mb-4">
                                                <div className="flex items-center gap-2">
                                                    <Droplets className="h-5 w-5 text-primary" />
                                                    <div>
                                                        <h3 className="font-medium text-foreground">Water Intake</h3>
                                                        {waterData?.calculation?.tier_name && (
                                                            <p className="text-[11px] text-muted-foreground">
                                                                {waterData.calculation.tier_name}
                                                            </p>
                                                        )}
                                                    </div>
                                                </div>
                                                <div className="text-right">
                                                    <p className="text-primary font-bold">
                                                        {waterConsumed.toLocaleString()} / {waterTarget.toLocaleString()} ml
                                                    </p>
                                                    <span className="text-[11px] text-muted-foreground">
                                                        {Math.min(100, Math.round((waterConsumed / waterTarget) * 100))}% reached
                                                    </span>
                                                </div>
                                            </div>
                                            <div className="flex justify-between items-center gap-2">
                                                <div className="flex-1 flex justify-between px-2 overflow-x-auto py-1">
                                                    {Array.from({ length: glassesCount }).map((_, i) => (
                                                        <Droplets
                                                            key={i}
                                                            className={cn(
                                                                'h-5 w-5 shrink-0 transition-colors',
                                                                i < filledGlasses
                                                                    ? 'text-primary fill-primary'
                                                                    : 'text-primary/20'
                                                            )}
                                                        />
                                                    ))}
                                                </div>
                                                <button
                                                    type="button"
                                                    onClick={handleAddGlass}
                                                    disabled={isViewOnly || isLoggingWater}
                                                    className="bg-primary/20 p-2 rounded-full flex items-center justify-center hover:bg-primary/30 transition-colors disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-primary/20 shrink-0"
                                                    aria-label="Add 250ml glass of water"
                                                    title="Log +250ml"
                                                >
                                                    <Plus className="h-5 w-5 text-primary" />
                                                </button>
                                            </div>
                                        </div>
                                    </section>
                                );
                            })()}
                        </>
                    )}
                </div>
            </div>

            {/* Set Tracking Modal */}
            <WorkoutSetModal
                item={selectedWorkoutItem}
                isOpen={isWorkoutModalOpen}
                isLocked={isLocked}
                onClose={() => {
                    setIsWorkoutModalOpen(false);
                    setSelectedWorkoutItem(null);
                }}
            />

            {/* Meal Portion Modal */}
            <MealLoggerModal
                item={selectedMealItem}
                isOpen={isMealModalOpen}
                isLocked={isLocked}
                onClose={() => {
                    setIsMealModalOpen(false);
                    setSelectedMealItem(null);
                }}
            />
        </AuthenticatedLayout>
    );
}
