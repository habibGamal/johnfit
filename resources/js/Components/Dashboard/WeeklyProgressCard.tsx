import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { TrendingUp } from "lucide-react";
import CompletionProgress from "./CompletionProgress";
import DailyActivityBar from "./DailyActivityBar";
import { WeeklyCompletionRate, ComparisonStats } from "@/types";
import PercentageChangeBadge from "./PercentageChangeBadge";

interface WeeklyProgressCardProps {
  weeklyCompletionRate: WeeklyCompletionRate;
  activeDaysData: {
    day: string;
    count: number;
    percentage: number;
  }[];
  comparisonStats?: ComparisonStats;
}

export default function WeeklyProgressCard({ weeklyCompletionRate, activeDaysData, comparisonStats }: WeeklyProgressCardProps) {
  const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

  return (
    <Card className="lg:col-span-2 shadow-sm border-gray-100 dark:border-gray-700 dark:bg-gray-800/50 backdrop-blur-sm">
      <CardHeader>
        <CardTitle className="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
          <div className="p-2 bg-primary/10 rounded-lg">
            <TrendingUp className="h-5 w-5 text-primary" />
          </div>
          Weekly Progress
        </CardTitle>
        <CardDescription className="flex items-center gap-2 text-gray-500 dark:text-gray-400">
          Your workout completion over the past week
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
          <CompletionProgress
            completed={weeklyCompletionRate.completed}
            total={weeklyCompletionRate.total}
            percentage={weeklyCompletionRate.percentage}
          />

          {/* Activity by Day Chart */}
          <div>
            <h4 className="text-sm font-semibold text-gray-900 dark:text-white mb-4">Activity by Day</h4>
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
                    />
                  );
                })}
              </div>
            ) : (
              <div className="flex items-center justify-center h-40 border border-dashed border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-800/50">
                <p className="text-sm text-gray-500 dark:text-gray-400">No activity data available yet</p>
              </div>
            )}
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
