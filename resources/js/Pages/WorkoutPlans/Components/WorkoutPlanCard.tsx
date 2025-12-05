import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from "@/Components/ui/accordion";
import WorkoutItem from "./WorkoutItem";
import { WorkoutPlan } from "@/types";
import { Badge } from "@/Components/ui/badge";

interface WorkoutPlanCardProps {
    plan: WorkoutPlan;
}

export default function WorkoutPlanCard({ plan }: WorkoutPlanCardProps) {
    return (
        <Card className="overflow-hidden border-none shadow-md bg-gradient-to-br from-card to-muted/20">
            <CardHeader className="border-b bg-muted/30 pb-4">
                <div className="flex items-center justify-between">
                    <CardTitle className="text-xl font-bold text-foreground">{plan.name}</CardTitle>
                    <Badge variant="secondary" className="font-normal">
                        {plan.plan.length} Days / Week
                    </Badge>
                </div>
            </CardHeader>
            <CardContent className="p-0">
                <Accordion type="single" collapsible className="w-full">
                    {plan.plan.map((day, index) => (
                        <AccordionItem key={day.day} value={`day-${index}`} className="border-b last:border-0 px-6">
                            <AccordionTrigger className="text-base py-4 hover:no-underline hover:text-primary transition-colors">
                                <div className="flex items-center gap-3">
                                    <span className="font-semibold text-lg">{day.day}</span>
                                    <span className="text-sm text-muted-foreground font-normal">
                                        ({day.workouts.length} {day.workouts.length === 1 ? "exercise" : "exercises"})
                                    </span>
                                </div>
                            </AccordionTrigger>
                            <AccordionContent>
                                <div className="space-y-4 pt-2 pb-6">
                                    {day.workouts.map((workout) => (
                                        <WorkoutItem
                                            key={workout.id}
                                            workout={workout}
                                            workoutPlanId={plan.id}
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
    );
}
