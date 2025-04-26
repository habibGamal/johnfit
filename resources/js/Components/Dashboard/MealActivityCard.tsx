import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { CalendarDays, Utensils, ClipboardList } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { RecentActivity } from '@/types';

interface MealActivityItemProps {
  activity: RecentActivity;
}

function MealActivityItem({ activity }: MealActivityItemProps) {
  return (
    <div className="flex items-start gap-3 p-4 rounded-lg border">
      <div className="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-green-500/10">
        <Utensils className="h-6 w-6 text-green-600" />
      </div>
      <div className="flex-1">
        <div className="flex items-center justify-between">
          <h4 className="font-semibold">{activity.meal}</h4>
          <span className="text-xs text-muted-foreground">{activity.completed_at}</span>
        </div>
        <p className="text-sm text-muted-foreground mt-1">{activity.plan_name}</p>
        <div className="mt-2">
          <span className="inline-flex items-center rounded-full bg-secondary/30 px-2 py-1 text-xs">
            <CalendarDays className="mr-1 h-3 w-3" />
            {activity.day}
          </span>
        </div>
      </div>
    </div>
  );
}

interface MealActivityCardProps {
  activities: RecentActivity[];
}

export default function MealActivityCard({ activities }: MealActivityCardProps) {
  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <ClipboardList className="h-5 w-5 text-green-600" />
          Recent Meals
        </CardTitle>
        <CardDescription>Your latest tracked meals</CardDescription>
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
            <div className="mb-3 rounded-full bg-muted/30 p-3">
              <Utensils className="h-6 w-6 text-muted-foreground" />
            </div>
            <h3 className="mt-2 text-sm font-medium">No meal tracking yet</h3>
            <p className="mt-1 text-sm text-muted-foreground max-w-xs">
              Track your first meal to start building your nutrition history
            </p>
            <Button asChild className="mt-6" variant="outline" size="sm">
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
