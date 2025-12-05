import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { CalendarDays, Utensils, ClipboardList } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { MealRecentActivity } from '@/types';

interface MealActivityItemProps {
  activity: MealRecentActivity;
}

function MealActivityItem({ activity }: MealActivityItemProps) {
  return (
    <div className="flex items-start gap-4 p-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 hover:shadow-md transition-all duration-300 group">
      <div className="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-emerald-500/10 group-hover:bg-emerald-500/20 transition-colors">
        <Utensils className="h-6 w-6 text-emerald-600" />
      </div>
      <div className="flex-1">
        <div className="flex items-center justify-between">
          <h4 className="font-bold text-gray-900 dark:text-white group-hover:text-emerald-600 transition-colors">{activity.meal}</h4>
          <span className="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full">{activity.completed_at}</span>
        </div>
        <p className="text-sm text-gray-600 dark:text-gray-300 mt-1">{activity.plan_name}</p>
        <div className="mt-3">
          <span className="inline-flex items-center rounded-full bg-blue-50 dark:bg-blue-900/20 px-2.5 py-1 text-xs font-medium text-blue-700 dark:text-blue-300">
            <CalendarDays className="mr-1.5 h-3.5 w-3.5" />
            {activity.day}
          </span>
        </div>
      </div>
    </div>
  );
}

interface MealActivityCardProps {
  activities: MealRecentActivity[];
}

export default function MealActivityCard({ activities }: MealActivityCardProps) {
  return (
    <Card className="shadow-sm border-gray-100 dark:border-gray-700 dark:bg-gray-800/50 backdrop-blur-sm h-full">
      <CardHeader>
        <CardTitle className="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
          <div className="p-2 bg-emerald-500/10 rounded-lg">
            <ClipboardList className="h-5 w-5 text-emerald-600" />
          </div>
          Recent Meals
        </CardTitle>
        <CardDescription className="text-gray-500 dark:text-gray-400">Your latest tracked meals</CardDescription>
      </CardHeader>
      <CardContent>
        {activities && activities.length > 0 ? (
          <div className="space-y-4">
            {activities.map((activity, index) => (
              <MealActivityItem key={index} activity={activity} />
            ))}
          </div>
        ) : (
          <div className="flex flex-col items-center justify-center py-12 text-center">
            <div className="mb-4 rounded-full bg-gray-100 dark:bg-gray-700 p-4">
              <Utensils className="h-8 w-8 text-gray-400 dark:text-gray-500" />
            </div>
            <h3 className="mt-2 text-base font-semibold text-gray-900 dark:text-white">No meal tracking yet</h3>
            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-xs">
              Track your first meal to start building your nutrition history
            </p>
            <Button asChild className="mt-6 bg-emerald-600 hover:bg-emerald-700 text-white" size="sm">
              <Link href={route('meal-plans.index')}>
                View Meal Plans
              </Link>
            </Button>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
