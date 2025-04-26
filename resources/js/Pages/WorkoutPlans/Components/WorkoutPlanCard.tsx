import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from "@/Components/ui/accordion";
import WorkoutItem from "./WorkoutItem";
import { WorkoutPlan } from "@/types";

interface WorkoutPlanCardProps {
    plan: WorkoutPlan;
}

export default function WorkoutPlanCard({ plan }: WorkoutPlanCardProps) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>{plan.name}</CardTitle>
            </CardHeader>
            <CardContent>
                <Accordion type="single" collapsible className="w-full">
                    {plan.plan.map((day, index) => (
                        <AccordionItem key={day.day} value={`day-${index}`}>
                            <AccordionTrigger className="text-base">
                                {day.day} ({day.workouts.length}{" "}
                                {day.workouts.length === 1
                                    ? "workout"
                                    : "workouts"}
                                )
                            </AccordionTrigger>
                            <AccordionContent>
                                <div className="space-y-4 pt-2">
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
