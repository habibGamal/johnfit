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
          <h2 className="text-xl font-black uppercase tracking-wider text-foreground">
            My Meal Plans
          </h2>
          <span className="text-xs font-medium text-yellow-500 uppercase tracking-widest bg-yellow-500/10 px-3 py-1 rounded-full border border-yellow-500/20">
            {mealPlans.length} Active
          </span>
        </div>
      }
    >
      <Head title="My Meal Plans" />

      <div className="py-8 relative">
        {/* Background Glow */}
        <div className="fixed top-20 right-0 w-[300px] h-[300px] bg-yellow-500/5 blur-[100px] pointer-events-none rounded-full" />

        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
          <div className="relative overflow-hidden rounded-2xl border border-white/5 bg-zinc-900/40 backdrop-blur-md p-6 shadow-xl transition-all duration-300">
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
                  icon={<Utensils className="h-12 w-12 text-yellow-500/80" />}
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
