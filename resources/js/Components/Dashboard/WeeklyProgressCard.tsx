import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { TrendingUp } from "lucide-react";
import CompletionProgress from "./CompletionProgress";
import DailyActivityBar from "./DailyActivityBar";
import { WeeklyCompletionRate } from "@/types";

interface WeeklyProgressCardProps {
  weeklyCompletionRate: WeeklyCompletionRate;
  activeDaysData: {
    day: string;
    count: number;
    percentage: number;
  }[];
}

export default function WeeklyProgressCard({ weeklyCompletionRate, activeDaysData }: WeeklyProgressCardProps) {
  const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

  return (
    <Card className="lg:col-span-2">
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <TrendingUp className="h-5 w-5 text-primary" />
          Weekly Progress
        </CardTitle>
        <CardDescription>Your workout completion over the past week</CardDescription>
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
            <h4 className="text-sm font-medium mb-4">Activity by Day</h4>
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
                <p className="text-sm text-muted-foreground">No activity data available yet</p>
              </div>
            )}
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
