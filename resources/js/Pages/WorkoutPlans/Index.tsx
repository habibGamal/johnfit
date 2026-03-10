import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { WorkoutPlan } from '@/types';
import EmptyState from './Components/EmptyState';
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from "@/Components/ui/card";
import { CalendarDays, ChevronRight, Dumbbell, Calendar, Activity, Zap } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import WorkoutPlanCard from './Components/WorkoutPlanCard';

interface WorkoutPlansProps {
    workoutPlans: WorkoutPlan[];
}

export default function Index({ workoutPlans }: WorkoutPlansProps) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-2xl font-black uppercase tracking-wider text-foreground">
                            My Workout Plans
                        </h2>
                        <p className="text-sm text-muted-foreground mt-1">
                            Your roadmap to strength and conditioning
                        </p>
                    </div>

                    <div className="flex items-center gap-2 px-3 py-1 rounded-full bg-yellow-500/10 border border-yellow-500/20">
                        <Activity className="w-4 h-4 text-yellow-500" />
                        <span className="text-sm font-bold text-yellow-500">
                            {workoutPlans.length} {workoutPlans.length === 1 ? 'Active Plan' : 'Active Plans'}
                        </span>
                    </div>
                </div>
            }
        >
            <Head title="My Workout Plans" />

            <div className="py-12 relative">
                {/* Background Ambient Glow */}
                <div className="absolute top-0 left-1/2 -translate-x-1/2 w-full h-[500px] bg-yellow-500/5 blur-[120px] pointer-events-none rounded-full mix-blend-screen" />

                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">

                    {workoutPlans.length > 0 ? (
                        <div className="space-y-8">
                            {/* Featured / Hero Section for the most recent plan if available */}
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                {workoutPlans.map((plan) => (
                                    <WorkoutPlanCard key={plan.id} plan={plan} />
                                ))}
                            </div>
                        </div>
                    ) : (
                        <div className="mt-8">
                            <EmptyState
                                icon={<Dumbbell className="h-16 w-16 text-yellow-500/80 mb-4" />}
                                title="NO ACTIVE PLANS"
                                description="You don't have any workout routines assigned yet. Your coach is crafting the perfect plan for you."
                                action={{
                                    label: "Request New Plan",
                                    icon: <Zap className="h-4 w-4" />,
                                    href: "#"
                                }}
                            />
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
