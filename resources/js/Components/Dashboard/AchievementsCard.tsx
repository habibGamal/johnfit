import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { Award, Flame } from "lucide-react";
import { AchievementStats } from "@/types";

interface AchievementsCardProps {
  aggregateStats: AchievementStats;
  currentStreak: number;
}

export default function AchievementsCard({ aggregateStats, currentStreak }: AchievementsCardProps) {
  return (
    <Card className="h-full flex flex-col">
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Award className="h-5 w-5 text-primary" />
          Your Achievements
        </CardTitle>
        <CardDescription>Your fitness journey so far</CardDescription>
      </CardHeader>
      <CardContent className="flex-1 flex flex-col justify-evenly">
        {aggregateStats ? (
          <div className="space-y-6">
            <div className="border rounded-md p-5 text-center">
              <div className="inline-flex items-center justify-center size-16 rounded-full bg-primary/10 mb-2">
                <svg
                  className="size-8 text-primary"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                >
                  <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                  <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
              </div>
              <div className="text-3xl font-bold">{aggregateStats.total_completions}</div>
              <div className="text-sm text-muted-foreground">Workouts Completed</div>
            </div>

            <div className="flex flex-col space-y-2">
              <div className="flex items-center justify-between">
                <span className="text-sm">Current Streak</span>
                <span className="flex items-center text-sm font-semibold">
                  <Flame className="mr-1 size-3.5 text-amber-500" />
                  {currentStreak} days
                </span>
              </div>

              <div className="flex items-center justify-between">
                <span className="text-sm">Active Plans</span>
                <span className="font-semibold text-sm">
                  {aggregateStats.active_plans}
                </span>
              </div>

              <div className="flex items-center justify-between">
                <span className="text-sm">Last 7 Days</span>
                <span className="font-semibold text-sm">
                  {aggregateStats.recent_completions} workouts
                </span>
              </div>
            </div>
          </div>
        ) : (
          <div className="flex items-center justify-center py-8 border rounded-md bg-muted/5">
            <p className="text-sm text-muted-foreground">No achievement data yet</p>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
