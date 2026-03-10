import { Link } from '@inertiajs/react';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from "@/Components/ui/card";
import { Badge } from "@/Components/ui/badge";
import { Button } from '@/Components/ui/button';
import { CalendarDays, ChevronRight, Activity, Dumbbell, Trophy } from 'lucide-react';
import { WorkoutPlan } from "@/types";

interface WorkoutPlanCardProps {
    plan: WorkoutPlan;
}

export default function WorkoutPlanCard({ plan }: WorkoutPlanCardProps) {
    const totalExercises = plan.plan.reduce((acc, day) => acc + day.workouts.length, 0);

    return (
        <Card className="group relative overflow-hidden border border-white/10 bg-zinc-900/40 backdrop-blur-xl transition-all duration-300 hover:border-yellow-500/50 hover:shadow-[0_0_30px_-10px_rgba(234,179,8,0.3)]">
            {/* Hover Gradient Overlay */}
            <div className="absolute inset-0 bg-gradient-to-br from-yellow-500/5 via-transparent to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100" />

            <CardHeader className="relative z-10 space-y-1 pb-4">
                <div className="flex items-start justify-between">
                    <div className="space-y-2">
                        <Badge variant="outline" className="border-yellow-500/20 bg-yellow-500/10 text-yellow-500 hover:bg-yellow-500/20 px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wide">
                            Active Plan
                        </Badge>
                        <CardTitle className="text-xl font-black text-white uppercase tracking-wide group-hover:text-yellow-500 transition-colors">
                            {plan.name}
                        </CardTitle>
                    </div>
                    <div className="rounded-full bg-zinc-800 p-2.5 text-yellow-500 shadow-inner">
                        <Trophy className="h-5 w-5" />
                    </div>
                </div>
            </CardHeader>

            <CardContent className="relative z-10 space-y-4 pb-6">
                <div className="grid grid-cols-2 gap-3">
                    <div className="flex flex-col rounded-lg bg-zinc-950/50 p-3 border border-white/5">
                        <div className="flex items-center gap-2 text-muted-foreground mb-1">
                            <CalendarDays className="h-3.5 w-3.5" />
                            <span className="text-xs uppercase font-bold tracking-wider">Frequency</span>
                        </div>
                        <span className="text-lg font-bold text-white">
                            {plan.plan.length} <span className="text-xs font-normal text-muted-foreground">days/wk</span>
                        </span>
                    </div>

                    <div className="flex flex-col rounded-lg bg-zinc-950/50 p-3 border border-white/5">
                        <div className="flex items-center gap-2 text-muted-foreground mb-1">
                            <Dumbbell className="h-3.5 w-3.5" />
                            <span className="text-xs uppercase font-bold tracking-wider">Volume</span>
                        </div>
                        <span className="text-lg font-bold text-white">
                            {totalExercises} <span className="text-xs font-normal text-muted-foreground">exercises</span>
                        </span>
                    </div>
                </div>

                <div className="flex flex-wrap gap-1.5">
                    {plan.plan.slice(0, 4).map((day) => (
                        <Badge key={day.day} variant="secondary" className="bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-medium px-2 py-1">
                            {day.day}
                        </Badge>
                    ))}
                    {plan.plan.length > 4 && (
                        <Badge variant="secondary" className="bg-zinc-800 hover:bg-zinc-700 text-zinc-300 px-2 py-1">
                            +{plan.plan.length - 4}
                        </Badge>
                    )}
                </div>
            </CardContent>

            <CardFooter className="relative z-10 pt-0">
                <Button
                    asChild
                    className="w-full bg-white text-black hover:bg-yellow-400 hover:text-black font-bold uppercase tracking-wider transition-all duration-300 group-hover:translate-x-1"
                >
                    <Link href={route('workout-plans.show', plan.id)} className="flex items-center justify-between">
                        <span>View Routine</span>
                        <ChevronRight className="h-4 w-4" />
                    </Link>
                </Button>
            </CardFooter>
        </Card>
    );
}
