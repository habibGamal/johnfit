import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { MealStats } from '@/types';
import { AppleIcon, CalendarDays, Utensils } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import DailyActivityBar from './DailyActivityBar';

interface MealProgressCardProps {
  weeklyCompletionRate: MealStats['weeklyCompletionRate'];
  activeDaysData: {
    day: string;
    count: number;
    percentage: number;
  }[];
}

export default function MealProgressCard({ weeklyCompletionRate, activeDaysData }: MealProgressCardProps) {
  const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

  return (
    <Card className="lg:col-span-2">
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <AppleIcon className="h-5 w-5 text-green-600" />
          Meal Tracking Progress
        </CardTitle>
        <CardDescription>Your meal consumption patterns and consistency</CardDescription>
      </CardHeader>
      <CardContent>
        <div className="space-y-8">
          <div>
            <div className="flex items-center justify-between mb-1">
              <span className="text-sm font-medium">Weekly Completion</span>
              <span className="text-sm font-medium">{weeklyCompletionRate.percentage}%</span>
            </div>
            <div className="h-3 w-full overflow-hidden rounded-full bg-muted">
              <div
                className="h-full bg-green-600"
                style={{ width: `${weeklyCompletionRate.percentage}%` }}
              />
            </div>
            <p className="mt-1 text-xs text-muted-foreground text-right">
              {weeklyCompletionRate.completed} of {weeklyCompletionRate.total} meals tracked
            </p>
          </div>

          {/* Activity by Day Chart */}
          <div>
            <h4 className="text-sm font-medium mb-4">Meal Activity by Day</h4>
            {activeDaysData.length > 0 ? (
              <div className="space-y-2">
                {daysOfWeek.map((day) => {
                  const dayData = activeDaysData.find(d => d.day === day);
                  return (
                    <DailyActivityBar
                      key={day}
                      day={day}
                      count={dayData?.count || 0}
                      percentage={dayData?.percentage || 0}
                    />
                  );
                })}
              </div>
            ) : (
              <div className="flex items-center justify-center h-40 border rounded-md bg-muted/5">
                <p className="text-sm text-muted-foreground">No meal tracking data available yet</p>
              </div>
            )}
          </div>

          <div className="pt-4 border-t">
            <h4 className="text-sm font-medium mb-2">Eating Insights</h4>
            <div className="flex flex-wrap gap-2">
              {activeDaysData.length > 0 && (
                <>
                  <Badge variant="outline" className="bg-muted/20">
                    <Utensils className="mr-1 h-3 w-3" />
                    {activeDaysData.reduce((max, day) => day.count > max.count ? day : max, activeDaysData[0]).day} is your most consistent day
                  </Badge>

                  {(() => {
                    const totalMeals = activeDaysData.reduce((sum, day) => sum + day.count, 0);
                    const averageMeals = Math.round(totalMeals / 7 * 10) / 10; // Round to 1 decimal
                    const displayText = averageMeals >= 1
                      ? `${averageMeals} meals tracked daily on average`
                      : `${totalMeals} meals tracked this week`;

                    return (
                      <Badge variant="outline" className="bg-muted/20">
                        <CalendarDays className="mr-1 h-3 w-3" />
                        {displayText}
                      </Badge>
                    );
                  })()}
                </>
              )}
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
