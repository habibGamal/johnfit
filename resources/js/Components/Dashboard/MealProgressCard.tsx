import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { MealStats } from '@/types';
import { AppleIcon, CalendarDays, Utensils } from 'lucide-react';
import { Badge } from '@/Components/ui/badge';
import DailyActivityBar from './DailyActivityBar';
import PercentageChangeBadge from './PercentageChangeBadge';

interface MealProgressCardProps {
  weeklyCompletionRate: MealStats['weeklyCompletionRate'];
  activeDaysData: {
    day: string;
    count: number;
    percentage: number;
  }[];
  comparisonStats?: MealStats['comparisonStats'];
}

export default function MealProgressCard({ weeklyCompletionRate, activeDaysData, comparisonStats }: MealProgressCardProps) {
  const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

  return (
    <Card className="lg:col-span-2 shadow-sm border-gray-100 dark:border-gray-700 dark:bg-gray-800/50 backdrop-blur-sm">
      <CardHeader>
        <CardTitle className="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
          <div className="p-2 bg-emerald-500/10 rounded-lg">
            <AppleIcon className="h-5 w-5 text-emerald-600" />
          </div>
          Meal Tracking Progress
        </CardTitle>
        <CardDescription className="flex items-center gap-2 text-gray-500 dark:text-gray-400">
          Your meal consumption patterns
          {comparisonStats && (
            <PercentageChangeBadge
              percentage={comparisonStats.percentage_change}
              trend={comparisonStats.trend}
            />
          )}
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div className="space-y-8">
          <div>
            <div className="flex items-center justify-between mb-2">
              <span className="text-sm font-medium text-gray-700 dark:text-gray-300">Weekly Completion</span>
              <span className="text-sm font-bold text-emerald-600">{weeklyCompletionRate.percentage}%</span>
            </div>
            <div className="h-3 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
              <div
                className="h-full bg-gradient-to-r from-emerald-500 to-emerald-400 rounded-full transition-all duration-1000 ease-out"
                style={{ width: `${weeklyCompletionRate.percentage}%` }}
              />
            </div>
            <p className="mt-2 text-xs text-gray-500 dark:text-gray-400 text-right">
              {weeklyCompletionRate.completed} of {weeklyCompletionRate.total} meals tracked
            </p>
          </div>

          {/* Activity by Day Chart */}
          <div>
            <h4 className="text-sm font-semibold text-gray-900 dark:text-white mb-4">Meal Activity by Day</h4>
            {activeDaysData.length > 0 ? (
              <div className="space-y-3">
                {daysOfWeek.map((day) => {
                  const dayData = activeDaysData.find(d => d.day === day);
                  return (
                    <DailyActivityBar
                      key={day}
                      day={day}
                      count={dayData?.count || 0}
                      percentage={dayData?.percentage || 0}
                      colorClass="bg-emerald-500"
                    />
                  );
                })}
              </div>
            ) : (
              <div className="flex items-center justify-center h-40 border border-dashed border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-800/50">
                <p className="text-sm text-gray-500 dark:text-gray-400">No meal tracking data available yet</p>
              </div>
            )}
          </div>

          <div className="pt-6 border-t border-gray-100 dark:border-gray-700">
            <h4 className="text-sm font-semibold text-gray-900 dark:text-white mb-3">Eating Insights</h4>
            <div className="flex flex-wrap gap-2">
              {activeDaysData.length > 0 && (
                <>
                  <Badge variant="outline" className="bg-emerald-50/50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800 py-1.5">
                    <Utensils className="mr-1.5 h-3.5 w-3.5" />
                    {activeDaysData.reduce((max, day) => day.count > max.count ? day : max, activeDaysData[0]).day} is your most consistent day
                  </Badge>

                  {(() => {
                    const totalMeals = activeDaysData.reduce((sum, day) => sum + day.count, 0);
                    const averageMeals = Math.round(totalMeals / 7 * 10) / 10; // Round to 1 decimal
                    const displayText = averageMeals >= 1
                      ? `${averageMeals} meals tracked daily on average`
                      : `${totalMeals} meals tracked this week`;

                    return (
                      <Badge variant="outline" className="bg-blue-50/50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800 py-1.5">
                        <CalendarDays className="mr-1.5 h-3.5 w-3.5" />
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
