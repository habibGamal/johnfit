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
import { ArrowLeft, CalendarDays, Trophy, Activity, Timer, CheckCircle2 } from 'lucide-react';
import { Progress } from '@/Components/ui/progress';
import { cn } from '@/lib/utils';

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
        // Fetch session summary to check for incomplete sets
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
                        <Button variant="ghost" size="icon" asChild className="rounded-full hover:bg-gray-100 dark:hover:bg-gray-800">
                            <Link href={route('workout-plans.index')}>
                                <ArrowLeft className="h-5 w-5" />
                            </Link>
                        </Button>
                        <div>
                            <h2 className="text-xl font-bold leading-tight text-gray-900 dark:text-gray-100">
                                {workoutPlan.name}
                            </h2>
                            <p className="text-xs text-muted-foreground">Your fitness roadmap</p>
                        </div>
                    </div>
                </div>
            }
        >
            <Head title={`Workout Plan: ${workoutPlan.name}`} />

            <div className="py-8">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 gap-8 md:grid-cols-12">

                        {/* Sidebar */}
                        <div className="md:col-span-4 space-y-6">
                            {/* Progress Card */}
                            <Card className="border-none shadow-lg bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-950">
                                <CardHeader className="pb-2">
                                    <CardTitle className="text-lg flex items-center gap-2">
                                        <Trophy className="h-5 w-5 text-yellow-500" />
                                        Plan Progress
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        <div className="flex items-end justify-between">
                                            <span className="text-4xl font-bold text-primary">{completionPercentage}%</span>
                                            <span className="text-sm text-muted-foreground mb-1">{completedWorkouts}/{totalWorkouts} exercises</span>
                                        </div>
                                        <Progress value={completionPercentage} className="h-3" />
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Plan Details Card */}
                            <Card className="border-none shadow-lg">
                                <CardHeader>
                                    <CardTitle className="text-lg flex items-center gap-2">
                                        <Activity className="h-5 w-5 text-primary" />
                                        Overview
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="flex items-center justify-between p-3 rounded-lg bg-background border">
                                        <div className="flex items-center gap-3">
                                            <div className="p-2 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/20">
                                                <CalendarDays className="h-4 w-4" />
                                            </div>
                                            <div>
                                                <p className="text-sm font-medium">Frequency</p>
                                                <p className="text-xs text-muted-foreground">{workoutPlan.plan.length} days / week</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex items-center justify-between p-3 rounded-lg bg-background border">
                                        <div className="flex items-center gap-3">
                                            <div className="p-2 rounded-full bg-green-50 text-green-600 dark:bg-green-900/20">
                                                <Timer className="h-4 w-4" />
                                            </div>
                                            <div>
                                                <p className="text-sm font-medium">Next Up</p>
                                                <p className="text-xs text-muted-foreground">Check schedule</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <p className="text-sm font-medium mb-3">Workout Days</p>
                                        <div className="flex flex-wrap gap-2">
                                            {workoutPlan.plan.map(day => (
                                                <span key={day.day} className="px-2.5 py-1 rounded-md bg-muted text-xs font-medium text-muted-foreground border">
                                                    {day.day}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Main Content */}
                        <div className="md:col-span-8">
                            <Card className="border-none shadow-md">
                                <CardHeader>
                                    <CardTitle>Workout Schedule</CardTitle>
                                    <CardDescription>Your prescribed routine for the week</CardDescription>
                                </CardHeader>
                                <CardContent className="p-0">
                                    <Accordion
                                        type="single"
                                        collapsible
                                        className="w-full"
                                        value={activeDay ?? undefined}
                                        onValueChange={(value) => setActiveDay(value || null)}
                                    >
                                        {workoutPlan.plan.map((day, index) => {
                                            const dayProgress = getDayProgress(day);
                                            const isActive = activeDay === `day-${index}`;

                                            return (
                                                <AccordionItem key={day.day} value={`day-${index}`} className="border-b last:border-0 px-6">
                                                    <AccordionTrigger className="text-base py-4 hover:no-underline hover:text-primary transition-colors">
                                                        <div className="flex items-center justify-between w-full pr-4">
                                                            <div className="flex items-center gap-3">
                                                                <span className="font-semibold text-lg">{day.day}</span>
                                                                <span className="text-sm text-muted-foreground font-normal">
                                                                    ({day.workouts.length} {day.workouts.length === 1 ? 'workout' : 'workouts'})
                                                                </span>
                                                            </div>
                                                            <div className={cn(
                                                                "px-2 py-1 rounded-full text-xs font-medium",
                                                                dayProgress.percentage === 100
                                                                    ? "bg-green-500/20 text-green-600 dark:text-green-400"
                                                                    : dayProgress.percentage > 0
                                                                        ? "bg-primary/10 text-primary"
                                                                        : "bg-muted text-muted-foreground"
                                                            )}>
                                                                {dayProgress.completed}/{dayProgress.total}
                                                            </div>
                                                        </div>
                                                    </AccordionTrigger>
                                                    <AccordionContent>
                                                        <div className="space-y-4 pt-2 pb-6">
                                                            {day.workouts.map((workout) => (
                                                                <WorkoutItem
                                                                    key={workout.id}
                                                                    workout={workout}
                                                                    workoutPlanId={workoutPlan.id}
                                                                    day={day.day}
                                                                />
                                                            ))}

                                                            {/* Finish Day Button */}
                                                            <div className="pt-4 border-t border-border/50">
                                                                <Button
                                                                    onClick={() => handleFinishDay(day.day)}
                                                                    className="w-full py-6 text-base font-semibold"
                                                                    variant={dayProgress.percentage === 100 ? "default" : "secondary"}
                                                                >
                                                                    <CheckCircle2 className="mr-2 h-5 w-5" />
                                                                    {dayProgress.percentage === 100 ? 'Complete Workout' : 'Finish Workout'}
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
