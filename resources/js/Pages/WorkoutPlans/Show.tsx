import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { WorkoutPlan } from '@/types';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/Components/ui/accordion';
import WorkoutItem from './Components/WorkoutItem';
import { ArrowLeft, CalendarDays, Trophy, Activity, Timer } from 'lucide-react';
import { Progress } from '@/Components/ui/progress';

interface ShowProps {
    workoutPlan: WorkoutPlan;
}

export default function Show({ workoutPlan }: ShowProps) {
    const completedWorkouts = workoutPlan.plan.reduce((total, day) => {
        return total + day.workouts.filter(workout => workout.completed).length;
    }, 0);

    const totalWorkouts = workoutPlan.plan.reduce((total, day) => {
        return total + day.workouts.length;
    }, 0);

    const completionPercentage = totalWorkouts > 0
        ? Math.round((completedWorkouts / totalWorkouts) * 100)
        : 0;

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
                                    <Accordion type="single" collapsible className="w-full">
                                        {workoutPlan.plan.map((day, index) => (
                                            <AccordionItem key={day.day} value={`day-${index}`} className="border-b last:border-0 px-6">
                                                <AccordionTrigger className="text-base py-4 hover:no-underline hover:text-primary transition-colors">
                                                    <div className="flex items-center gap-3">
                                                        <span className="font-semibold text-lg">{day.day}</span>
                                                        <span className="text-sm text-muted-foreground font-normal">
                                                            ({day.workouts.length} {day.workouts.length === 1 ? 'workout' : 'workouts'})
                                                        </span>
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
                                                    </div>
                                                </AccordionContent>
                                            </AccordionItem>
                                        ))}
                                    </Accordion>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
