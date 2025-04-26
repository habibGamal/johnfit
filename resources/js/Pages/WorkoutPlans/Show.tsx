import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { WorkoutPlan } from '@/types';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/Components/ui/accordion';
import WorkoutItem from './Components/WorkoutItem';
import { ArrowLeft, CalendarDays } from 'lucide-react';

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
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        {workoutPlan.name}
                    </h2>
                    <Button variant="outline" size="sm" asChild>
                        <Link href={route('workout-plans.index')}>
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back to All Plans
                        </Link>
                    </Button>
                </div>
            }
        >
            <Head title={`Workout Plan: ${workoutPlan.name}`} />

            <div className="py-12">
                <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <Card>
                            <CardHeader className="pb-2">
                                <CardTitle className="text-lg">Plan Summary</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2">
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <CalendarDays className="h-4 w-4" />
                                        <span>{workoutPlan.plan.length} days per week</span>
                                    </div>
                                    <div>
                                        <p className="text-sm font-medium">Workout days:</p>
                                        <div className="mt-1 flex flex-wrap gap-1">
                                            {workoutPlan.plan.map(day => (
                                                <span key={day.day} className="rounded-md bg-muted px-2 py-1 text-xs">
                                                    {day.day}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="pb-2">
                                <CardTitle className="text-lg">Your Progress</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-2">
                                    <div className="flex items-end justify-between">
                                        <p className="text-sm font-medium">Completion</p>
                                        <p className="text-xl font-bold">{completionPercentage}%</p>
                                    </div>
                                    <div className="h-2 w-full overflow-hidden rounded-full bg-muted">
                                        <div
                                            className="h-full bg-primary"
                                            style={{ width: `${completionPercentage}%` }}
                                        />
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        {completedWorkouts} of {totalWorkouts} workouts completed
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>Workout Schedule</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Accordion type="single" collapsible className="w-full">
                                {workoutPlan.plan.map((day, index) => (
                                    <AccordionItem key={day.day} value={`day-${index}`}>
                                        <AccordionTrigger className="text-base">
                                            {day.day} ({day.workouts.length} {day.workouts.length === 1 ? 'workout' : 'workouts'})
                                        </AccordionTrigger>
                                        <AccordionContent>
                                            <div className="space-y-4 pt-2">
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
        </AuthenticatedLayout>
    );
}
