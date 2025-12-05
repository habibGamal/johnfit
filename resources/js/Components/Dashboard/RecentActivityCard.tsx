import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { CalendarDays, Dumbbell, History } from "lucide-react";
import { Link } from "@inertiajs/react";
import { RecentActivity } from "@/types";

interface ActivityItemProps {
  activity: RecentActivity;
}

function ActivityItem({ activity }: ActivityItemProps) {
  return (
    <div className="flex items-start gap-4 p-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 hover:shadow-md transition-all duration-300 group">
      <div className="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-primary/10 group-hover:bg-primary/20 transition-colors">
        <Dumbbell className="h-6 w-6 text-primary" />
      </div>
      <div className="flex-1">
        <div className="flex items-center justify-between">
          <h4 className="font-bold text-gray-900 dark:text-white group-hover:text-primary transition-colors">{activity.workout}</h4>
          <span className="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full">{activity.completed_at}</span>
        </div>
        <p className="text-sm text-gray-600 dark:text-gray-300 mt-1">{activity.plan_name}</p>
        <div className="mt-3">
          <span className="inline-flex items-center rounded-full bg-secondary/50 px-2.5 py-1 text-xs font-medium text-secondary-foreground">
            <CalendarDays className="mr-1.5 h-3.5 w-3.5" />
            {activity.day}
          </span>
        </div>
      </div>
    </div>
  );
}

interface RecentActivityCardProps {
  activities: RecentActivity[];
}

export default function RecentActivityCard({ activities }: RecentActivityCardProps) {
  return (
    <Card className="shadow-sm border-gray-100 dark:border-gray-700 dark:bg-gray-800/50 backdrop-blur-sm h-full">
      <CardHeader>
        <CardTitle className="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
          <div className="p-2 bg-primary/10 rounded-lg">
            <History className="h-5 w-5 text-primary" />
          </div>
          Recent Activity
        </CardTitle>
        <CardDescription className="text-gray-500 dark:text-gray-400">Your latest completed workouts</CardDescription>
      </CardHeader>
      <CardContent>
        {activities && activities.length > 0 ? (
          <div className="space-y-4">
            {activities.map((activity, index) => (
              <ActivityItem key={index} activity={activity} />
            ))}
          </div>
        ) : (
          <div className="flex flex-col items-center justify-center py-12 text-center">
            <div className="mb-4 rounded-full bg-gray-100 dark:bg-gray-700 p-4">
              <History className="h-8 w-8 text-gray-400 dark:text-gray-500" />
            </div>
            <h3 className="mt-2 text-base font-semibold text-gray-900 dark:text-white">No activity yet</h3>
            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-xs">
              Complete your first workout to see your activity here
            </p>
            <Button asChild className="mt-6 bg-primary hover:bg-primary/90" size="sm">
              <Link href={route('workout-plans.index')}>
                Start Workout
              </Link>
            </Button>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
