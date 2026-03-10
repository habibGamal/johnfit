import { useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { WorkoutPlan } from '@/types';
import { Button } from '@/Components/ui/button';
import axios from 'axios';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/Components/ui/accordion';
import WorkoutItem from './Components/WorkoutItem';
import FinishWorkoutDialog from './Components/FinishWorkoutDialog';
import { ArrowLeft, CalendarDays, Trophy, Activity, Timer, CheckCircle2, Flame, Dumbbell } from 'lucide-react';
import { Progress } from '@/Components/ui/progress';
import { cn } from '@/lib/utils';
import { Badge } from '@/Components/ui/badge';

interface ShowProps {
    workoutPlan: WorkoutPlan;
}

export default function Show({ workoutPlan }: ShowProps) {
    const [activeDay, setActiveDay] = useState<string | null>(null);
    const [showFinishDialog, setShowFinishDialog] = useState(false);
    const [finishingDay, setFinishingDay] = useState<string>('');
    const [incompleteSets, setIncompleteSets] = useState(0);

    const completedWorkouts = workoutPlan.plan.reduce((total, day) => {
        return total + day.workouts.filter(workout => workout.completed).length;
    }, 0);

    const totalWorkouts = workoutPlan.plan.reduce((total, day) => {
        return total + day.workouts.length;
    }, 0);

    const completionPercentage = totalWorkouts > 0
        ? Math.round((completedWorkouts / totalWorkouts) * 100)
        : 0;

    const getDayProgress = (day: typeof workoutPlan.plan[0]) => {
        const completed = day.workouts.filter(w => w.completed).length;
        const total = day.workouts.length;
        return { completed, total, percentage: total > 0 ? Math.round((completed / total) * 100) : 0 };
    };

    const handleFinishDay = async (day: string) => {
        try {
            const response = await axios.get(route('workout-plans.session-summary', { workoutPlan: workoutPlan.id, day }));

            setFinishingDay(day);
            setIncompleteSets(response.data.incomplete_sets || 0);
            setShowFinishDialog(true);
        } catch (error) {
            console.error('Failed to get session summary:', error);
            setFinishingDay(day);
            setIncompleteSets(0);
            setShowFinishDialog(true);
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="icon" asChild className="rounded-full hover:bg-yellow-500/20 hover:text-yellow-500 transition-colors">
                            <Link href={route('workout-plans.index')}>
                                <ArrowLeft className="h-5 w-5" />
                            </Link>
                        </Button>
                        <div>
                            <h2 className="text-xl font-black uppercase tracking-wider text-foreground">
                                {workoutPlan.name}
                            </h2>
                            <p className="text-xs font-medium text-yellow-500 uppercase tracking-widest">Active Plan</p>
                        </div>
                    </div>
                </div>
            }
        >
            <Head title={`Workout Plan: ${workoutPlan.name}`} />

            <div className="py-8 relative">
                {/* Background Glow */}
                <div className="fixed top-20 right-0 w-[300px] h-[300px] bg-yellow-500/5 blur-[100px] pointer-events-none rounded-full" />

                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
                    <div className="grid grid-cols-1 gap-8 md:grid-cols-12 items-start">

                        {/* Sidebar */}
                        <div className="md:col-span-4 space-y-6 sticky top-8">
                            {/* Stats Card */}
                            <Card className="border border-white/10 bg-zinc-900/60 backdrop-blur-xl shadow-xl overflow-hidden relative">
                                <div className="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-yellow-500 to-transparent opacity-50" />
                                <CardHeader className="pb-2">
                                    <div className="flex items-center justify-between">
                                        <CardTitle className="text-sm uppercase font-bold tracking-wider text-muted-foreground">
                                            Plan Progress
                                        </CardTitle>
                                        <Trophy className="h-5 w-5 text-yellow-500" />
                                    </div>
                                </CardHeader>
                                <CardContent className="pt-4">
                                    <div className="relative flex items-center justify-center py-6">
                                        {/* Radial Progress Placeholder - simplified with CSS/SVG */}
                                        <div className="relative h-40 w-40">
                                            <svg className="h-full w-full -rotate-90 transform" viewBox="0 0 100 100">
                                                <circle cx="50" cy="50" r="40" fill="transparent" stroke="currentColor" strokeWidth="8" className="text-zinc-800" />
                                                <circle
                                                    cx="50" cy="50" r="40"
                                                    fill="transparent"
                                                    stroke="currentColor"
                                                    strokeWidth="8"
                                                    strokeDasharray={`${251.2 * (completionPercentage / 100)} 251.2`}
                                                    strokeLinecap="round"
                                                    className="text-yellow-500 transition-all duration-1000 ease-out"
                                                />
                                            </svg>
                                            <div className="absolute inset-0 flex flex-col items-center justify-center">
                                                <span className="text-4xl font-black text-white">{completionPercentage}%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="mt-4 grid grid-cols-2 gap-4">
                                        <div className="bg-zinc-950/50 rounded-lg p-3 text-center border border-white/5">
                                            <div className="text-xs text-muted-foreground uppercase tracking-wider mb-1">Completed</div>
                                            <div className="text-xl font-bold text-white">{completedWorkouts}</div>
                                        </div>
                                        <div className="bg-zinc-950/50 rounded-lg p-3 text-center border border-white/5">
                                            <div className="text-xs text-muted-foreground uppercase tracking-wider mb-1">Total</div>
                                            <div className="text-xl font-bold text-white">{totalWorkouts}</div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Plan Details Card */}
                            <Card className="border border-white/5 bg-zinc-900/40 backdrop-blur-md">
                                <CardHeader>
                                    <CardTitle className="text-sm uppercase font-bold tracking-wider text-muted-foreground flex items-center gap-2">
                                        <Activity className="h-4 w-4" />
                                        Plan Overview
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="flex items-center gap-4">
                                        <div className="p-2.5 rounded-lg bg-zinc-800 text-yellow-500">
                                            <CalendarDays className="h-5 w-5" />
                                        </div>
                                        <div>
                                            <p className="text-sm font-bold text-white">Frequency</p>
                                            <p className="text-xs text-muted-foreground">{workoutPlan.plan.length} days / week</p>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-4">
                                        <div className="p-2.5 rounded-lg bg-zinc-800 text-rose-500">
                                            <Flame className="h-5 w-5" />
                                        </div>
                                        <div>
                                            <p className="text-sm font-bold text-white">Intensity</p>
                                            <p className="text-xs text-muted-foreground">Hypertrophy Focus</p>
                                        </div>
                                    </div>

                                    <div className="pt-4 border-t border-white/5">
                                        <p className="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-3">Schedule</p>
                                        <div className="flex flex-wrap gap-2">
                                            {workoutPlan.plan.map(day => (
                                                <Badge key={day.day} variant="outline" className="border-white/10 bg-zinc-950/30">
                                                    {day.day}
                                                </Badge>
                                            ))}
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Main Content */}
                        <div className="md:col-span-8 space-y-6">
                            <div className="flex items-center justify-between">
                                <h3 className="text-2xl font-black uppercase tracking-wide text-white">
                                    Weekly Schedule
                                </h3>
                                <div className="text-sm font-medium text-yellow-500 bg-yellow-500/10 px-3 py-1 rounded-full border border-yellow-500/20">
                                    Week 1
                                </div>
                            </div>

                            <Card className="border-none bg-transparent shadow-none">
                                <CardContent className="p-0 space-y-4">
                                    <Accordion
                                        type="single"
                                        collapsible
                                        className="w-full space-y-4"
                                        value={activeDay ?? undefined}
                                        onValueChange={(value) => setActiveDay(value || null)}
                                    >
                                        {workoutPlan.plan.map((day, index) => {
                                            const dayProgress = getDayProgress(day);
                                            const isActive = activeDay === `day-${index}`;
                                            const isCompleted = dayProgress.percentage === 100;

                                            return (
                                                <AccordionItem
                                                    key={day.day}
                                                    value={`day-${index}`}
                                                    className={cn(
                                                        "border border-white/5 rounded-2xl bg-zinc-900/40 backdrop-blur-md overflow-hidden transition-all duration-300",
                                                        isActive && "border-yellow-500/30 bg-zinc-900/60 ring-1 ring-yellow-500/10 shadow-lg"
                                                    )}
                                                >
                                                    <AccordionTrigger className="px-6 py-5 hover:no-underline group">
                                                        <div className="flex items-center justify-between w-full pr-4">
                                                            <div className="flex items-center gap-4">
                                                                <div className={cn(
                                                                    "flex h-10 w-10 items-center justify-center rounded-full border-2 transition-colors font-bold",
                                                                    isCompleted ? "border-green-500 bg-green-500/10 text-green-500" :
                                                                        isActive ? "border-yellow-500 bg-yellow-500 text-black" : "border-zinc-700 bg-zinc-800 text-muted-foreground group-hover:border-zinc-600"
                                                                )}>
                                                                    {isCompleted ? <CheckCircle2 className="h-5 w-5" /> : index + 1}
                                                                </div>
                                                                <div className="text-left">
                                                                    <div className={cn("font-bold text-lg uppercase tracking-wide", isActive ? "text-white" : "text-zinc-300")}>
                                                                        {day.day}
                                                                    </div>
                                                                    <div className="text-xs text-muted-foreground font-medium flex items-center gap-2">
                                                                        <span>{day.workouts.length} Assignments</span>
                                                                        {dayProgress.percentage > 0 && (
                                                                            <span className={cn(
                                                                                "px-1.5 py-0.5 rounded text-[10px] font-bold uppercase",
                                                                                isCompleted ? "bg-green-500/20 text-green-500" : "bg-yellow-500/20 text-yellow-500"
                                                                            )}>
                                                                                {dayProgress.percentage}% Done
                                                                            </span>
                                                                        )}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </AccordionTrigger>
                                                    <AccordionContent className="px-6 pb-6 pt-2">
                                                        <div className="space-y-4">
                                                            <div className="relative">
                                                                <div className="absolute left-6 top-0 bottom-0 w-px bg-zinc-800" />
                                                                <div className="space-y-6 relative pl-2">
                                                                    {day.workouts.map((workout) => (
                                                                        <WorkoutItem
                                                                            key={workout.id}
                                                                            workout={workout}
                                                                            workoutPlanId={workoutPlan.id}
                                                                            day={day.day}
                                                                            isLocked={isCompleted}
                                                                        />
                                                                    ))}
                                                                </div>
                                                            </div>

                                                            {/* Finish Day Button */}
                                                            <div className="pt-6 mt-6 border-t border-white/5">
                                                                <Button
                                                                    onClick={() => handleFinishDay(day.day)}
                                                                    className={cn(
                                                                        "w-full h-12 text-base font-bold uppercase tracking-wide transition-all shadow-lg",
                                                                        isCompleted
                                                                            ? "bg-green-600 hover:bg-green-500 text-white shadow-green-900/20"
                                                                            : "bg-yellow-500 hover:bg-yellow-400 text-black shadow-yellow-900/20 hover:scale-[1.01]"
                                                                    )}
                                                                >
                                                                    {isCompleted ? (
                                                                        <>
                                                                            <CheckCircle2 className="mr-2 h-5 w-5" />
                                                                            Session Completed
                                                                        </>
                                                                    ) : (
                                                                        <>
                                                                            <Dumbbell className="mr-2 h-5 w-5" />
                                                                            Complete Session
                                                                        </>
                                                                    )}
                                                                </Button>
                                                            </div>
                                                        </div>
                                                    </AccordionContent>
                                                </AccordionItem>
                                            );
                                        })}
                                    </Accordion>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>

            {/* Finish Workout Dialog */}
            <FinishWorkoutDialog
                open={showFinishDialog}
                onOpenChange={setShowFinishDialog}
                workoutPlanId={workoutPlan.id}
                day={finishingDay}
                incompleteSets={incompleteSets}
            />
        </AuthenticatedLayout>
    );
}
