import React from 'react';
import {
    BarChart,
    Bar,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    ResponsiveContainer,
    ReferenceLine,
    Cell,
} from 'recharts';
import { WaterWeeklyStats } from '@/types/water';
import { CheckCircle2, TrendingUp, Calendar } from 'lucide-react';

interface WaterWeeklyChartProps {
    stats: WaterWeeklyStats;
    targetMl: number;
}

export default function WaterWeeklyChart({ stats, targetMl }: WaterWeeklyChartProps) {
    const data = stats.history || [];

    const CustomTooltip = ({ active, payload }: any) => {
        if (active && payload && payload.length) {
            const item = payload[0].payload;
            return (
                <div className="bg-card/95 backdrop-blur-md border border-border rounded-xl p-3 shadow-xl text-xs space-y-1">
                    <p className="font-semibold text-foreground">{item.full_date} ({item.day_name})</p>
                    <div className="flex items-center justify-between gap-4">
                        <span className="text-muted-foreground">Consumed:</span>
                        <span className="font-bold text-blue-600 dark:text-blue-400">
                            {item.consumed_ml.toLocaleString()} ml
                        </span>
                    </div>
                    <div className="flex items-center justify-between gap-4">
                        <span className="text-muted-foreground">Target:</span>
                        <span className="font-medium text-foreground">
                            {item.target_ml.toLocaleString()} ml
                        </span>
                    </div>
                    <div className="flex items-center justify-between gap-4 pt-1 border-t border-border/40">
                        <span className="text-muted-foreground">Adherence:</span>
                        <span className={`font-semibold ${item.is_completed ? 'text-emerald-500' : 'text-amber-500'}`}>
                            {item.percentage}% {item.is_completed && '✓'}
                        </span>
                    </div>
                </div>
            );
        }
        return null;
    };

    return (
        <div className="space-y-4">
            {/* Top Stat Pills */}
            <div className="grid grid-cols-3 gap-2">
                <div className="p-3 rounded-xl border border-border/50 bg-muted/30">
                    <span className="text-[11px] text-muted-foreground block">Daily Avg</span>
                    <span className="text-sm font-bold text-foreground">
                        {stats.average_daily_ml.toLocaleString()} ml
                    </span>
                </div>
                <div className="p-3 rounded-xl border border-border/50 bg-muted/30">
                    <span className="text-[11px] text-muted-foreground block">Days Reached</span>
                    <span className="text-sm font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                        <CheckCircle2 className="w-3.5 h-3.5 inline" />
                        {stats.completed_days} / 7
                    </span>
                </div>
                <div className="p-3 rounded-xl border border-border/50 bg-muted/30">
                    <span className="text-[11px] text-muted-foreground block">Weekly Rate</span>
                    <span className="text-sm font-bold text-blue-600 dark:text-blue-400">
                        {stats.completion_rate}%
                    </span>
                </div>
            </div>

            {/* Recharts Bar Graph */}
            <div className="h-44 w-full pt-2">
                <ResponsiveContainer width="100%" height="100%">
                    <BarChart data={data} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                        <CartesianGrid strokeDasharray="3 3" stroke="currentColor" opacity={0.08} />
                        <XAxis
                            dataKey="day_name"
                            axisLine={false}
                            tickLine={false}
                            tick={{ fontSize: 11, fill: 'currentColor', opacity: 0.6 }}
                        />
                        <YAxis
                            axisLine={false}
                            tickLine={false}
                            tick={{ fontSize: 10, fill: 'currentColor', opacity: 0.6 }}
                            tickFormatter={(v) => `${(v / 1000).toFixed(1)}L`}
                        />
                        <Tooltip content={<CustomTooltip />} />
                        <ReferenceLine
                            y={targetMl}
                            stroke="#3b82f6"
                            strokeDasharray="4 4"
                            strokeOpacity={0.6}
                        />
                        <Bar dataKey="consumed_ml" radius={[6, 6, 0, 0]} maxBarSize={28}>
                            {data.map((entry, index) => (
                                <Cell
                                    key={`cell-${index}`}
                                    fill={entry.is_completed ? '#10b981' : '#3b82f6'}
                                    fillOpacity={entry.is_completed ? 0.9 : 0.75}
                                />
                            ))}
                        </Bar>
                    </BarChart>
                </ResponsiveContainer>
            </div>
            <div className="flex items-center justify-center gap-4 text-[11px] text-muted-foreground">
                <span className="flex items-center gap-1.5">
                    <span className="w-2.5 h-2.5 rounded-sm bg-emerald-500 inline-block" />
                    Target Reached
                </span>
                <span className="flex items-center gap-1.5">
                    <span className="w-2.5 h-2.5 rounded-sm bg-blue-500 inline-block" />
                    In Progress
                </span>
                <span className="flex items-center gap-1.5">
                    <span className="w-3 h-0.5 border-t border-dashed border-blue-500 inline-block" />
                    Daily Target Line
                </span>
            </div>
        </div>
    );
}
