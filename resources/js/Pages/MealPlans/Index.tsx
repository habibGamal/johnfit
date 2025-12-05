import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { MealPlan } from '@/types';
import MealPlanCard from '@/Components/MealPlans/MealPlanCard';
import EmptyState from '@/Components/EmptyState';
import { Utensils } from 'lucide-react';

interface MealPlansProps {
  mealPlans: MealPlan[];
}

export default function Index({ mealPlans }: MealPlansProps) {
  return (
    <AuthenticatedLayout
      header={
        <div className="flex items-center justify-between">
          <h2 className="text-xl font-bold leading-tight text-gray-800 dark:text-gray-200">
            My Meal Plans
          </h2>
          <span className="text-sm font-medium text-muted-foreground">
            {mealPlans.length} {mealPlans.length === 1 ? 'Plan' : 'Plans'} Active
          </span>
        </div>
      }
    >
      <Head title="My Meal Plans" />

      <div className="py-12">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="relative overflow-hidden rounded-2xl border bg-background/50 p-6 shadow-sm backdrop-blur-xl transition-all duration-300 hover:shadow-md dark:bg-background/20">
            {/* Ambient Background Gradient */}
            <div className="pointer-events-none absolute -left-20 -top-20 h-64 w-64 rounded-full bg-primary/5 blur-3xl" />
            <div className="pointer-events-none absolute -bottom-20 -right-20 h-64 w-64 rounded-full bg-secondary/5 blur-3xl" />

            <div className="relative">
              {mealPlans.length > 0 ? (
                <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                  {mealPlans.map((plan) => (
                    <div key={plan.id} className="transition-transform duration-300 hover:-translate-y-1">
                      <MealPlanCard mealPlan={plan} />
                    </div>
                  ))}
                </div>
              ) : (
                <EmptyState
                  icon={<Utensils className="h-12 w-12 text-primary/80" />}
                  title="No meal plans yet"
                  description="Your nutritionist hasn't assigned any meal plans to you effectively yet. Check back soon!"
                />
              )}
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
