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
        <h2 className="text-xl font-semibold leading-tight text-gray-800">
          My Meal Plans
        </h2>
      }
    >
      <Head title="My Meal Plans" />

      <div className="py-12">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div className="overflow-hidden rounded-lg border bg-background shadow">
            <div className="p-6">
              {mealPlans.length > 0 ? (
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                  {mealPlans.map((plan) => (
                    <MealPlanCard key={plan.id} mealPlan={plan} />
                  ))}
                </div>
              ) : (
                <EmptyState
                  icon={<Utensils className="h-10 w-10 text-primary" />}
                  title="No meal plans assigned"
                  description="You don't have any meal plans assigned to you yet. Contact your coach to get started on your nutrition journey."
                />
              )}
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
