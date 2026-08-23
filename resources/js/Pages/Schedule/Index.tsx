import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { SchedulePageProps, UserDailyItem } from '@/types';
import DateNavigator from './Components/DateNavigator';
import ScheduleScoreBanner from './Components/ScheduleScoreBanner';
import WorkoutItemCard from './Components/WorkoutItemCard';
import MealItemCard from './Components/MealItemCard';
import WorkoutSetModal from './Components/WorkoutSetModal';
import MealLoggerModal from './Components/MealLoggerModal';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Dumbbell, Utensils, Sparkles, CheckCircle2, ListFilter } from 'lucide-react';

export default function ScheduleIndex({
    auth,
    selectedDate,
    schedule,
    weeklyAdherence,
    currentStreak,
}: SchedulePageProps) {
    const [selectedWorkoutItem, setSelectedWorkoutItem] = useState<UserDailyItem | null>(null);
    const [isWorkoutModalOpen, setIsWorkoutModalOpen] = useState(false);

    const [selectedMealItem, setSelectedMealItem] = useState<UserDailyItem | null>(null);
    const [isMealModalOpen, setIsMealModalOpen] = useState(false);

    const [isGenerating, setIsGenerating] = useState(false);

    const isLocked = schedule?.is_locked ?? false;
    const items = schedule?.items ?? [];

    const workoutItems = items.filter((i) => i.type === 'workout');
    const mealItems = items.filter((i) => i.type === 'meal');

    const completedWorkouts = workoutItems.filter((i) => i.is_completed).length;
    const completedMeals = mealItems.filter((i) => i.is_completed).length;

    const handleOpenWorkoutModal = (item: UserDailyItem) => {
        setSelectedWorkoutItem(item);
        setIsWorkoutModalOpen(true);
    };

    const handleOpenMealModal = (item: UserDailyItem) => {
        setSelectedMealItem(item);
        setIsMealModalOpen(true);
    };

    const handleGeneratePlans = () => {
        setIsGenerating(true);
        router.post(
            route('plans.generate'),
            {},
            {
                onFinish: () => setIsGenerating(false),
            }
        );
    };

    return (
        <AuthenticatedLayout>
            <Head title="Daily Schedule & Plans - JohnFit" />

            <div className="py-6 sm:py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                {/* Date Navigator */}
                <DateNavigator
                    selectedDate={selectedDate}
                    weeklyAdherence={weeklyAdherence}
                />

                {/* Daily Adherence & Streak Banner */}
                <ScheduleScoreBanner
                    schedule={schedule}
                    selectedDate={selectedDate}
                    currentStreak={currentStreak}
                />

                {/* Items & Planner Content */}
                {items.length === 0 ? (
                    <Card className="border-border border-dashed bg-card/50">
                        <CardContent className="p-8 sm:p-12 text-center space-y-4">
                            <div className="mx-auto w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                                <Sparkles className="h-7 w-7" />
                            </div>
                            <div className="space-y-1.5 max-w-md mx-auto">
                                <h3 className="text-lg font-bold text-foreground">
                                    No Plan Schedule for this Day
                                </h3>
                                <p className="text-xs sm:text-sm text-muted-foreground">
                                    You don't have active workout or meal plan items materialized for this date. Generate a personalized plan or check your upcoming active plan window.
                                </p>
                            </div>
                            <Button
                                onClick={handleGeneratePlans}
                                disabled={isGenerating}
                                className="gap-2 font-semibold shadow-md"
                            >
                                <Sparkles className="h-4 w-4" />
                                {isGenerating ? 'Generating Custom Plan...' : 'Generate AI Workout & Meal Plan'}
                            </Button>
                        </CardContent>
                    </Card>
                ) : (
                    <Tabs defaultValue="all" className="space-y-4">
                        <div className="flex items-center justify-between gap-2 flex-wrap">
                            <TabsList className="bg-secondary/70 p-1 border border-border">
                                <TabsTrigger value="all" className="text-xs font-semibold px-3.5">
                                    All Items ({items.length})
                                </TabsTrigger>
                                <TabsTrigger value="workouts" className="text-xs font-semibold px-3.5 gap-1.5">
                                    <Dumbbell className="h-3.5 w-3.5" />
                                    Workouts ({completedWorkouts}/{workoutItems.length})
                                </TabsTrigger>
                                <TabsTrigger value="meals" className="text-xs font-semibold px-3.5 gap-1.5">
                                    <Utensils className="h-3.5 w-3.5" />
                                    Meals ({completedMeals}/{mealItems.length})
                                </TabsTrigger>
                            </TabsList>
                        </div>

                        {/* All Items Stream */}
                        <TabsContent value="all" className="space-y-6 mt-0">
                            {workoutItems.length > 0 && (
                                <div className="space-y-3">
                                    <div className="flex items-center justify-between px-1">
                                        <div className="flex items-center gap-2">
                                            <Dumbbell className="h-4 w-4 text-primary" />
                                            <h3 className="text-sm font-bold uppercase tracking-wider text-foreground">
                                                Workouts ({completedWorkouts}/{workoutItems.length})
                                            </h3>
                                        </div>
                                    </div>
                                    <div className="grid grid-cols-1 gap-2.5">
                                        {workoutItems.map((item) => (
                                            <WorkoutItemCard
                                                key={item.id}
                                                item={item}
                                                isLocked={isLocked}
                                                onOpenSetsModal={handleOpenWorkoutModal}
                                            />
                                        ))}
                                    </div>
                                </div>
                            )}

                            {mealItems.length > 0 && (
                                <div className="space-y-3">
                                    <div className="flex items-center justify-between px-1">
                                        <div className="flex items-center gap-2">
                                            <Utensils className="h-4 w-4 text-emerald-500" />
                                            <h3 className="text-sm font-bold uppercase tracking-wider text-foreground">
                                                Meals ({completedMeals}/{mealItems.length})
                                            </h3>
                                        </div>
                                    </div>
                                    <div className="grid grid-cols-1 gap-2.5">
                                        {mealItems.map((item) => (
                                            <MealItemCard
                                                key={item.id}
                                                item={item}
                                                isLocked={isLocked}
                                                onOpenMealModal={handleOpenMealModal}
                                            />
                                        ))}
                                    </div>
                                </div>
                            )}
                        </TabsContent>

                        {/* Workouts Only */}
                        <TabsContent value="workouts" className="space-y-3 mt-0">
                            <div className="grid grid-cols-1 gap-2.5">
                                {workoutItems.map((item) => (
                                    <WorkoutItemCard
                                        key={item.id}
                                        item={item}
                                        isLocked={isLocked}
                                        onOpenSetsModal={handleOpenWorkoutModal}
                                    />
                                ))}
                            </div>
                        </TabsContent>

                        {/* Meals Only */}
                        <TabsContent value="meals" className="space-y-3 mt-0">
                            <div className="grid grid-cols-1 gap-2.5">
                                {mealItems.map((item) => (
                                    <MealItemCard
                                        key={item.id}
                                        item={item}
                                        isLocked={isLocked}
                                        onOpenMealModal={handleOpenMealModal}
                                    />
                                ))}
                            </div>
                        </TabsContent>
                    </Tabs>
                )}
            </div>

            {/* Set Tracking Modal */}
            <WorkoutSetModal
                item={selectedWorkoutItem}
                isOpen={isWorkoutModalOpen}
                isLocked={isLocked}
                onClose={() => {
                    setIsWorkoutModalOpen(false);
                    setSelectedWorkoutItem(null);
                }}
            />

            {/* Meal Portion Modal */}
            <MealLoggerModal
                item={selectedMealItem}
                isOpen={isMealModalOpen}
                isLocked={isLocked}
                onClose={() => {
                    setIsMealModalOpen(false);
                    setSelectedMealItem(null);
                }}
            />
        </AuthenticatedLayout>
    );
}
