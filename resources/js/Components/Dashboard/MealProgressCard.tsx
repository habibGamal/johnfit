import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { MealStats } from '@/types';
import { AppleIcon, CalendarDays, Utensils } from 'lucide-react';
import { Badge } from '@/Components/ui/badge';
import PercentageChangeBadge from './PercentageChangeBadge';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Cell } from "recharts";
import { motion } from "framer-motion";

interface MealProgressCardProps {
  weeklyCompletionRate: MealStats['weeklyCompletionRate'];
  activeDaysData: {
    day: string;
    count: number;
    percentage: number;
  }[];
  comparisonStats?: MealStats['comparisonStats'];
}

// Custom Tooltip Component
const CustomTooltip = ({ active, payload }: any) => {
  if (active && payload && payload.length) {
    return (
      <div className="bg-card/95 backdrop-blur-md border border-border rounded-lg px-3 py-2 shadow-lg">
        <p className="text-sm font-semibold text-foreground">{payload[0].payload.day}</p>
        <p className="text-xs text-muted-foreground mt-1">
          <span className="font-bold text-emerald-600">{payload[0].value}</span> meals tracked
        </p>
      </div>
    );
  }
  return null;
};

export default function MealProgressCard({ weeklyCompletionRate, activeDaysData, comparisonStats }: MealProgressCardProps) {
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
      transition={{ duration: 0.5, ease: "easeOut", delay: 0.1 }}
    >
      <Card className="lg:col-span-2 shadow-xl border-border/50 bg-card/40 backdrop-blur-md hover:shadow-2xl transition-shadow duration-300">
        <CardHeader>
          <CardTitle className="flex items-center gap-3 text-lg font-bold text-foreground">
            <div className="p-2.5 bg-gradient-to-br from-emerald-500/20 to-emerald-500/10 rounded-xl border border-emerald-500/20">
              <AppleIcon className="h-5 w-5 text-emerald-600" />
            </div>
            Meal Tracking Progress
          </CardTitle>
          <CardDescription className="flex items-center gap-2 text-muted-foreground">
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
                <span className="text-sm font-medium text-foreground">Weekly Completion</span>
                <span className="text-sm font-bold text-emerald-600">{weeklyCompletionRate.percentage}%</span>
              </div>
              <div className="h-3 w-full overflow-hidden rounded-full bg-muted">
                <div
                  className="h-full bg-gradient-to-r from-emerald-500 to-emerald-400 rounded-full transition-all duration-1000 ease-out"
                  style={{ width: `${weeklyCompletionRate.percentage}%` }}
                />
              </div>
              <p className="mt-2 text-xs text-muted-foreground text-right">
                {weeklyCompletionRate.completed} of {weeklyCompletionRate.total} meals tracked
              </p>
            </div>

            {/* Activity by Day Chart */}
            <div>
              <h4 className="text-sm font-semibold text-foreground mb-4">Meal Activity by Day</h4>
              {activeDaysData.length > 0 ? (
                <ResponsiveContainer width="100%" height={240}>
                  <BarChart data={chartData} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                    <defs>
                      <linearGradient id="mealBarGradient" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stopColor="#10B981" stopOpacity={0.9} />
                        <stop offset="100%" stopColor="#059669" stopOpacity={0.7} />
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
                          fill={entry.count > 0 ? "url(#mealBarGradient)" : "hsl(var(--muted))"}
                        />
                      ))}
                    </Bar>
                  </BarChart>
                </ResponsiveContainer>
              ) : (
                <div className="flex items-center justify-center h-40 border border-dashed border-border rounded-xl bg-muted/50">
                  <p className="text-sm text-muted-foreground">No meal tracking data available yet</p>
                </div>
              )}
            </div>

            <div className="pt-6 border-t border-border">
              <h4 className="text-sm font-semibold text-foreground mb-3">Eating Insights</h4>
              <div className="flex flex-wrap gap-2">
                {activeDaysData.length > 0 && (
                  <>
                    <Badge variant="outline" className="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20 py-1.5">
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
                        <Badge variant="outline" className="bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20 py-1.5">
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
    </motion.div>
  );
}
