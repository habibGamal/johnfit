import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { WorkoutPlan } from '@/types';
import EmptyState from './Components/EmptyState';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from "@/Components/ui/card";
import { CalendarDays, ChevronRight, Dumbbell, Calendar, Activity } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';

interface WorkoutPlansProps {
    workoutPlans: WorkoutPlan[];
}

export default function Index({ workoutPlans }: WorkoutPlansProps) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-bold leading-tight text-gray-800 dark:text-gray-200">
                        My Workout Plans
                    </h2>
                    <span className="text-sm font-medium text-muted-foreground">
                        {workoutPlans.length} {workoutPlans.length === 1 ? 'Plan' : 'Plans'} Active
                    </span>
                </div>
            }
        >
            <Head title="My Workout Plans" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="relative overflow-hidden rounded-2xl border bg-background/50 p-6 shadow-sm backdrop-blur-xl transition-all duration-300 hover:shadow-md dark:bg-background/20">
                        {/* Ambient Background Gradient */}
                        <div className="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-blue-500/5 blur-3xl" />
                        <div className="pointer-events-none absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-violet-500/5 blur-3xl" />

                        <div className="relative">
                            {workoutPlans.length > 0 ? (
                                <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                                    {workoutPlans.map((plan) => (
                                        <Card key={plan.id} className="group overflow-hidden border-muted/60 transition-all hover:border-primary/50 hover:shadow-lg">
                                            <div className="absolute inset-0 bg-gradient-to-br from-transparent to-muted/20 opacity-0 transition-opacity group-hover:opacity-100" />
                                            <CardHeader className="pb-3 relative">
                                                <div className="flex justify-between items-start">
                                                    <div className="space-y-1">
                                                        <CardTitle className="text-xl font-bold">{plan.name}</CardTitle>
                                                        <CardDescription className="flex items-center gap-1.5">
                                                            <CalendarDays className="h-3.5 w-3.5" />
                                                            {plan.plan.length} days / week
                                                        </CardDescription>
                                                    </div>
                                                    <div className="rounded-full bg-primary/10 p-2 text-primary">
                                                        <Activity className="h-5 w-5" />
                                                    </div>
                                                </div>
                                            </CardHeader>
                                            <CardContent className="pb-4 relative">
                                                <div className="flex flex-wrap gap-2">
                                                    {plan.plan.slice(0, 4).map((day) => (
                                                        <Badge key={day.day} variant="outline" className="flex items-center gap-1 bg-background/50">
                                                            <div className="h-1.5 w-1.5 rounded-full bg-primary" />
                                                            {day.day}
                                                        </Badge>
                                                    ))}
                                                    {plan.plan.length > 4 && (
                                                        <Badge variant="outline" className="text-muted-foreground">
                                                            +{plan.plan.length - 4} more
                                                        </Badge>
                                                    )}
                                                </div>
                                            </CardContent>
                                            <CardFooter className="relative pt-2">
                                                <Button
                                                    className="w-full gap-2 shadow-sm transition-all group-hover:shadow-md"
                                                    asChild
                                                >
                                                    <Link href={route('workout-plans.show', plan.id)}>
                                                        View Workout Plan
                                                        <ChevronRight className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            </CardFooter>
                                        </Card>
                                    ))}
                                </div>
                            ) : (
                                <EmptyState
                                    icon={<Dumbbell className="h-12 w-12 text-primary/80" />}
                                    title="No workout plans yet"
                                    description="You don't have any workout plans assigned to you yet. Contact your coach to get started on your fitness journey."
                                    action={{
                                        label: "Request Plan",
                                        icon: <Calendar className="h-4 w-4" />,
                                        href: "#"
                                    }}
                                />
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
