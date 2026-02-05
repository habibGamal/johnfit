import { useMemo } from 'react';
import {
  ComposedChart,
  Line,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
  Area,
} from 'recharts';
import { motion } from 'framer-motion';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/Components/ui/card';
import { TrendingUp } from 'lucide-react';
import { InBodyHistoryEntry } from '@/types';

interface BodyCompositionChartProps {
  data: InBodyHistoryEntry[];
}

export default function BodyCompositionChart({ data }: BodyCompositionChartProps) {
  const chartData = useMemo(() => {
    return data.map(entry => ({
      ...entry,
      name: entry.date,
    }));
  }, [data]);

  const CustomTooltip = ({ active, payload, label }: any) => {
    if (active && payload && payload.length) {
      return (
        <motion.div
          initial={{ opacity: 0, scale: 0.95 }}
          animate={{ opacity: 1, scale: 1 }}
          className="bg-white/95 dark:bg-gray-800/95 backdrop-blur-lg p-4 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700"
        >
          <p className="font-semibold text-gray-900 dark:text-white mb-2">{label}</p>
          <div className="space-y-1.5">
            {payload.map((entry: any, index: number) => (
              <div key={index} className="flex items-center gap-2 text-sm">
                <div
                  className="w-3 h-3 rounded-full"
                  style={{ backgroundColor: entry.color }}
                />
                <span className="text-gray-600 dark:text-gray-400">{entry.name}:</span>
                <span className="font-medium text-gray-900 dark:text-white">
                  {entry.value.toFixed(1)} {entry.name === 'Body Fat' ? '%' : 'kg'}
                </span>
              </div>
            ))}
          </div>
        </motion.div>
      );
    }
    return null;
  };

  if (data.length < 2) {
    return (
      <Card className="border-0 shadow-lg backdrop-blur-xl bg-white/80 dark:bg-gray-800/80">
        <CardContent className="flex items-center justify-center h-64">
          <p className="text-gray-500 dark:text-gray-400 text-center">
            Need at least 2 measurements to show trends.
            <br />
            <span className="text-sm">Keep logging your InBody data!</span>
          </p>
        </CardContent>
      </Card>
    );
  }

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5, delay: 0.2 }}
    >
      <Card className="border-0 shadow-lg backdrop-blur-xl bg-gradient-to-br from-white/80 to-white/60 dark:from-gray-800/80 dark:to-gray-800/60">
        <CardHeader>
          <CardTitle className="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
            <div className="p-2 bg-indigo-500/10 rounded-lg">
              <TrendingUp className="h-5 w-5 text-indigo-600" />
            </div>
            Body Composition Trends
          </CardTitle>
          <CardDescription className="text-gray-500 dark:text-gray-400">
            Tracking Muscle Mass vs Body Fat over time
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="h-80">
            <ResponsiveContainer width="100%" height="100%">
              <ComposedChart data={chartData} margin={{ top: 20, right: 30, left: 0, bottom: 0 }}>
                <defs>
                  <linearGradient id="smmGradient" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stopColor="#10b981" stopOpacity={0.3} />
                    <stop offset="95%" stopColor="#10b981" stopOpacity={0} />
                  </linearGradient>
                  <linearGradient id="pbfGradient" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stopColor="#f97316" stopOpacity={0.3} />
                    <stop offset="95%" stopColor="#f97316" stopOpacity={0} />
                  </linearGradient>
                </defs>
                <CartesianGrid
                  strokeDasharray="3 3"
                  className="stroke-gray-200 dark:stroke-gray-700"
                  vertical={false}
                />
                <XAxis
                  dataKey="name"
                  tick={{ fill: '#6b7280', fontSize: 12 }}
                  tickLine={false}
                  axisLine={false}
                />
                <YAxis
                  yAxisId="left"
                  orientation="left"
                  tick={{ fill: '#6b7280', fontSize: 12 }}
                  tickLine={false}
                  axisLine={false}
                  label={{ value: 'SMM (kg)', angle: -90, position: 'insideLeft', fill: '#10b981' }}
                />
                <YAxis
                  yAxisId="right"
                  orientation="right"
                  tick={{ fill: '#6b7280', fontSize: 12 }}
                  tickLine={false}
                  axisLine={false}
                  label={{ value: 'Body Fat (%)', angle: 90, position: 'insideRight', fill: '#f97316' }}
                />
                <Tooltip content={<CustomTooltip />} />
                <Legend
                  wrapperStyle={{ paddingTop: 20 }}
                  formatter={(value) => (
                    <span className="text-gray-700 dark:text-gray-300">{value}</span>
                  )}
                />
                <Area
                  yAxisId="left"
                  type="monotone"
                  dataKey="smm"
                  name="Muscle Mass"
                  stroke="#10b981"
                  strokeWidth={3}
                  fill="url(#smmGradient)"
                  dot={{ fill: '#10b981', strokeWidth: 2, r: 4 }}
                  activeDot={{ r: 6, strokeWidth: 2 }}
                />
                <Line
                  yAxisId="right"
                  type="monotone"
                  dataKey="pbf"
                  name="Body Fat"
                  stroke="#f97316"
                  strokeWidth={3}
                  dot={{ fill: '#f97316', strokeWidth: 2, r: 4 }}
                  activeDot={{ r: 6, strokeWidth: 2 }}
                />
              </ComposedChart>
            </ResponsiveContainer>
          </div>
        </CardContent>
      </Card>
    </motion.div>
  );
}
