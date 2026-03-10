import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { TrendingUp } from "lucide-react";
import CompletionProgress from "./CompletionProgress";
import { WeeklyCompletionRate, ComparisonStats } from "@/types";
import PercentageChangeBadge from "./PercentageChangeBadge";
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Cell } from "recharts";
import { motion } from "framer-motion";

interface WeeklyProgressCardProps {
  weeklyCompletionRate: WeeklyCompletionRate;
  activeDaysData: {
    day: string;
    count: number;
    percentage: number;
  }[];
  comparisonStats?: ComparisonStats;
}

// Custom Tooltip Component
const CustomTooltip = ({ active, payload }: any) => {
  if (active && payload && payload.length) {
    return (
      <div className="bg-card/95 backdrop-blur-md border border-border rounded-lg px-3 py-2 shadow-lg">
        <p className="text-sm font-semibold text-foreground">{payload[0].payload.day}</p>
        <p className="text-xs text-muted-foreground mt-1">
          <span className="font-bold text-primary">{payload[0].value}</span> workouts
        </p>
      </div>
    );
  }
  return null;
};

export default function WeeklyProgressCard({ weeklyCompletionRate, activeDaysData, comparisonStats }: WeeklyProgressCardProps) {
  const daysOfWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  const fullDayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

  // Format data for Recharts
  const chartData = daysOfWeek.map((shortDay, index) => {
    const fullDay = fullDayNames[index];
    const dayData = activeDaysData.find(d => d.day === fullDay);
    return {
      day: shortDay,
      count: dayData?.count || 0,
      percentage: dayData?.percentage || 0,
    };
  });

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5, ease: "easeOut" }}
    >
      <Card className="lg:col-span-2 shadow-xl border-border/50 bg-card/40 backdrop-blur-md hover:shadow-2xl transition-shadow duration-300">
        <CardHeader>
          <CardTitle className="flex items-center gap-3 text-lg font-bold text-foreground">
            <div className="p-2.5 bg-gradient-to-br from-primary/20 to-primary/10 rounded-xl border border-primary/20">
              <TrendingUp className="h-5 w-5 text-primary" />
            </div>
            Weekly Progress
          </CardTitle>
          <CardDescription className="flex items-center gap-2 text-muted-foreground">
            Your workout completion this week
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
              <h4 className="text-sm font-semibold text-foreground mb-4">Activity by Day</h4>
              {activeDaysData.length > 0 ? (
                <ResponsiveContainer width="100%" height={240}>
                  <BarChart data={chartData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                    <defs>
                      <linearGradient id="barGradient" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stopColor="#EAB308" stopOpacity={0.9} />
                        <stop offset="100%" stopColor="#CA8A04" stopOpacity={0.7} />
                      </linearGradient>
                    </defs>
                    <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" opacity={0.3} />
                    <XAxis
                      dataKey="day"
                      tick={{ fill: 'hsl(var(--muted-foreground))', fontSize: 12 }}
                      axisLine={{ stroke: 'hsl(var(--border))' }}
                    />
                    <YAxis
                      tick={{ fill: 'hsl(var(--muted-foreground))', fontSize: 12 }}
                      axisLine={{ stroke: 'hsl(var(--border))' }}
                    />
                    <Tooltip content={<CustomTooltip />} cursor={{ fill: 'hsl(var(--muted))', opacity: 0.3 }} />
                    <Bar dataKey="count" radius={[8, 8, 0, 0]} maxBarSize={50}>
                      {chartData.map((entry, index) => (
                        <Cell
                          key={`cell-${index}`}
                          fill={entry.count > 0 ? "url(#barGradient)" : "hsl(var(--muted))"}
                        />
                      ))}
                    </Bar>
                  </BarChart>
                </ResponsiveContainer>
              ) : (
                <div className="flex items-center justify-center h-40 border border-dashed border-border rounded-xl bg-muted/50">
                  <p className="text-sm text-muted-foreground">No activity data available yet</p>
                </div>
              )}
            </div>
          </div>
        </CardContent>
      </Card>
    </motion.div>
  );
}
