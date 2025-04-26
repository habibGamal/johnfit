import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { WorkoutPlan } from '@/types';
import EmptyState from '@/Components/EmptyState';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from "@/Components/ui/card";
import { CalendarDays, ChevronRight, Dumbbell, Calendar } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';

interface WorkoutPlansProps {
    workoutPlans: WorkoutPlan[];
}

export default function Index({ workoutPlans }: WorkoutPlansProps) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    My Workout Plans
                </h2>
            }
        >
            <Head title="My Workout Plans" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="overflow-hidden rounded-lg border bg-background shadow">
                        <div className="p-6">
                            {workoutPlans.length > 0 ? (
                                <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                                    {workoutPlans.map((plan) => (
                                        <Card key={plan.id} className="overflow-hidden">
                                            <CardHeader className="pb-2">
                                                <CardTitle className="text-xl">{plan.name}</CardTitle>
                                                <CardDescription className="flex items-center gap-1">
                                                    <CalendarDays className="h-4 w-4" />
                                                    {plan.plan.length} {plan.plan.length === 1 ? 'day' : 'days'} per week
                                                </CardDescription>
                                            </CardHeader>
                                            <CardContent className="pb-2">
                                                <div className="flex flex-wrap gap-2">
                                                    {plan.plan.map((day) => (
                                                        <Badge key={day.day} variant="outline" className="flex items-center gap-1">
                                                            <Dumbbell className="h-3 w-3" /> {day.day}
                                                        </Badge>
                                                    ))}
                                                </div>
                                            </CardContent>
                                            <CardFooter>
                                                <Button
                                                    variant="default"
                                                    className="w-full"
                                                    asChild
                                                >
                                                    <Link href={route('workout-plans.show', plan.id)}>
                                                        View Workout Plan
                                                        <ChevronRight className="ml-2 h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            </CardFooter>
                                        </Card>
                                    ))}
                                </div>
                            ) : (
                                <EmptyState
                                    icon={<Dumbbell className="h-10 w-10 text-primary" />}
                                    title="No workout plans assigned"
                                    description="You don't have any workout plans assigned to you yet. Contact your coach to get started on your fitness journey."
                                    action={{
                                        label: "Request Workout Plan",
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
