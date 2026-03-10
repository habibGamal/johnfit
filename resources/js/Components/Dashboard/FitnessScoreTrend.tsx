import { motion } from 'framer-motion';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Legend } from 'recharts';
import { TrendingUp } from 'lucide-react';
import { FitnessScoreHistory } from '@/types/fitness-score';

interface FitnessScoreTrendProps {
    history?: FitnessScoreHistory[];
    weeks?: number;
    isLoading?: boolean;
}

export default function FitnessScoreTrend({ history = [], weeks = 12, isLoading }: FitnessScoreTrendProps) {
    console.log(history);
    if (isLoading) {
        return (
            <div className="relative overflow-hidden rounded-2xl border border-border bg-card/50 backdrop-blur-sm p-6">
                <div className="animate-pulse space-y-4">
                    <div className="h-6 w-40 bg-muted rounded" />
                    <div className="h-64 bg-muted rounded" />
                </div>
            </div>
        );
    }

    if (!history || history.length === 0) {
        return (
            <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="relative overflow-hidden rounded-2xl border border-border bg-card/50 backdrop-blur-sm p-6"
            >
                <div className="flex items-center gap-2 mb-4">
                    <div className="p-2 rounded-lg bg-primary/10">
                        <TrendingUp className="w-5 h-5 text-primary" />
                    </div>
                    <h3 className="text-lg font-semibold text-foreground">Score Trend</h3>
                </div>
                <div className="flex flex-col items-center justify-center h-64 text-center">
                    <div className="text-muted-foreground mb-2">No trend data available yet</div>
                    <div className="text-sm text-muted-foreground">
                        Complete more workouts and meals to see your progress over time
                    </div>
                </div>
            </motion.div>
        );
    }

    // Custom tooltip
    const CustomTooltip = ({ active, payload }: any) => {
        if (active && payload && payload.length) {
            return (
                <div className="bg-card/95 backdrop-blur-sm border border-border rounded-lg p-3 shadow-lg">
                    <p className="text-sm font-semibold text-foreground mb-2">{payload[0].payload.date}</p>
                    <div className="space-y-1">
                        <div className="flex items-center gap-2">
                            <div className="w-3 h-3 rounded-full bg-primary" />
                            <span className="text-xs text-muted-foreground">Total:</span>
                            <span className="text-sm font-semibold text-foreground">
                                {Number(payload[0].value || 0).toFixed(1)}
                            </span>
                        </div>
                        {payload[1] && payload[1].value != null && (
                            <div className="flex items-center gap-2">
                                <div className="w-3 h-3 rounded-full bg-blue-500" />
                                <span className="text-xs text-muted-foreground">Workout:</span>
                                <span className="text-sm font-semibold text-foreground">
                                    {Number(payload[1].value).toFixed(1)}
                                </span>
                            </div>
                        )}
                        {payload[2] && payload[2].value != null && (
                            <div className="flex items-center gap-2">
                                <div className="w-3 h-3 rounded-full bg-emerald-500" />
                                <span className="text-xs text-muted-foreground">Meal:</span>
                                <span className="text-sm font-semibold text-foreground">
                                    {Number(payload[2].value).toFixed(1)}
                                </span>
                            </div>
                        )}
                    </div>
                </div>
            );
        }
        return null;
    };

    return (
        <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.2 }}
            className="relative overflow-hidden rounded-2xl border border-border bg-card/50 backdrop-blur-sm p-6"
        >
            <div className="flex items-center justify-between mb-6">
                <div className="flex items-center gap-2">
                    <div className="p-2 rounded-lg bg-primary/10">
                        <TrendingUp className="w-5 h-5 text-primary" />
                    </div>
                    <h3 className="text-lg font-semibold text-foreground">Score Trend</h3>
                </div>
                <div className="text-sm text-muted-foreground">Last {weeks} weeks</div>
            </div>

            <ResponsiveContainer width="100%" height={300}>
                <LineChart data={history} margin={{ top: 5, right: 5, left: -20, bottom: 5 }}>
                    <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" opacity={0.3} />
                    <XAxis
                        dataKey="date"
                        stroke="hsl(var(--muted-foreground))"
                        fontSize={12}
                        tickLine={false}
                        axisLine={false}
                    />
                    <YAxis
                        stroke="hsl(var(--muted-foreground))"
                        fontSize={12}
                        tickLine={false}
                        axisLine={false}
                        domain={[0, 100]}
                    />
                    <Tooltip content={<CustomTooltip />} />
                    <Legend
                        wrapperStyle={{
                            paddingTop: '20px',
                            fontSize: '12px',
                        }}
                        iconType="circle"
                    />
                    <Line
                        type="monotone"
                        dataKey="total_score"
                        stroke="#FCD34D"
                        strokeWidth={3}
                        dot={{ fill: '#FCD34D', r: 4 }}
                        activeDot={{ r: 6 }}
                        name="Total Score"
                    />
                    <Line
                        type="monotone"
                        dataKey="workout_score"
                        stroke="#3B82F6"
                        strokeWidth={2}
                        dot={{ fill: '#3B82F6', r: 3 }}
                        name="Workout"
                        opacity={0.7}
                    />
                    <Line
                        type="monotone"
                        dataKey="meal_score"
                        stroke="#10B981"
                        strokeWidth={2}
                        dot={{ fill: '#10B981', r: 3 }}
                        name="Nutrition"
                        opacity={0.7}
                    />
                </LineChart>
            </ResponsiveContainer>
        </motion.div>
    );
}
