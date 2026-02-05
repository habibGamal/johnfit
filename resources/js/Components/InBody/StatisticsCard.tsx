import { motion } from 'framer-motion';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { BarChart3 } from 'lucide-react';
import { InBodyStatistics } from '@/types';
import { cn } from '@/lib/utils';

interface StatisticsCardProps {
  statistics: InBodyStatistics;
}

const metricConfig = {
  weight: { label: 'Weight', unit: 'kg', color: 'bg-blue-500' },
  smm: { label: 'Muscle Mass', unit: 'kg', color: 'bg-emerald-500' },
  pbf: { label: 'Body Fat', unit: '%', color: 'bg-orange-500' },
  bmi: { label: 'BMI', unit: '', color: 'bg-purple-500' },
  bmr: { label: 'BMR', unit: 'kcal', color: 'bg-amber-500' },
};

export default function StatisticsCard({ statistics }: StatisticsCardProps) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5, delay: 0.4 }}
    >
      <Card className="border-0 shadow-lg backdrop-blur-xl bg-gradient-to-br from-white/80 to-white/60 dark:from-gray-800/80 dark:to-gray-800/60">
        <CardHeader>
          <CardTitle className="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
            <div className="p-2 bg-purple-500/10 rounded-lg">
              <BarChart3 className="h-5 w-5 text-purple-600" />
            </div>
            Historical Statistics
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            {(Object.keys(metricConfig) as Array<keyof typeof metricConfig>).map((key, index) => {
              const config = metricConfig[key];
              const stat = statistics[key];
              const range = stat.max - stat.min || 1;
              const currentPosition = ((stat.current - stat.min) / range) * 100;
              const avgPosition = ((stat.avg - stat.min) / range) * 100;

              return (
                <motion.div
                  key={key}
                  className="space-y-2"
                  initial={{ opacity: 0, x: -20 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: 0.5 + index * 0.1 }}
                >
                  <div className="flex items-center justify-between">
                    <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
                      {config.label}
                    </span>
                    <span className="text-sm font-bold text-gray-900 dark:text-white">
                      {stat.current.toFixed(key === 'bmr' ? 0 : 1)} {config.unit}
                    </span>
                  </div>

                  {/* Range bar */}
                  <div className="relative h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div
                      className={cn('absolute h-full rounded-full opacity-30', config.color)}
                      style={{ width: '100%' }}
                    />
                    {/* Current position marker */}
                    <motion.div
                      className={cn('absolute top-0 h-full w-1 rounded-full', config.color)}
                      initial={{ left: '0%' }}
                      animate={{ left: `${Math.min(Math.max(currentPosition, 0), 100)}%` }}
                      transition={{ type: 'spring', stiffness: 100 }}
                    />
                  </div>

                  {/* Min/Max/Avg labels */}
                  <div className="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>Min: {stat.min.toFixed(key === 'bmr' ? 0 : 1)}</span>
                    <span className="text-gray-600 dark:text-gray-300">
                      Avg: {stat.avg.toFixed(key === 'bmr' ? 0 : 1)}
                    </span>
                    <span>Max: {stat.max.toFixed(key === 'bmr' ? 0 : 1)}</span>
                  </div>
                </motion.div>
              );
            })}
          </div>
        </CardContent>
      </Card>
    </motion.div>
  );
}
