import { motion } from 'framer-motion';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { History, Scale, Activity, Percent } from 'lucide-react';
import { InBodyHistoryEntry } from '@/types';
import { cn } from '@/lib/utils';

interface HistoryTimelineProps {
  history: InBodyHistoryEntry[];
}

export default function HistoryTimeline({ history }: HistoryTimelineProps) {
  const recentHistory = history.slice(-10).reverse();

  if (recentHistory.length === 0) {
    return (
      <Card className="border-0 shadow-lg backdrop-blur-xl bg-gradient-to-br from-white/80 to-white/60 dark:from-gray-800/80 dark:to-gray-800/60">
        <CardContent className="flex items-center justify-center h-48">
          <div className="text-center">
            <History className="h-12 w-12 text-gray-400 mx-auto mb-3" />
            <p className="text-gray-500 dark:text-gray-400">No measurements yet</p>
            <p className="text-sm text-gray-400 dark:text-gray-500">
              Start tracking your InBody data
            </p>
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5, delay: 0.5 }}
    >
      <Card className="border-0 shadow-lg backdrop-blur-xl bg-gradient-to-br from-white/80 to-white/60 dark:from-gray-800/80 dark:to-gray-800/60">
        <CardHeader>
          <CardTitle className="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
            <div className="p-2 bg-teal-500/10 rounded-lg">
              <History className="h-5 w-5 text-teal-600" />
            </div>
            Recent Measurements
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="relative">
            {/* Timeline line */}
            <div className="absolute left-4 top-0 bottom-0 w-0.5 bg-gradient-to-b from-indigo-500 via-purple-500 to-pink-500" />

            <div className="space-y-4">
              {recentHistory.map((entry, index) => (
                <motion.div
                  key={entry.fullDate}
                  className="relative pl-10"
                  initial={{ opacity: 0, x: -20 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: 0.6 + index * 0.1 }}
                >
                  {/* Timeline dot */}
                  <div className={cn(
                    'absolute left-2 w-4 h-4 rounded-full border-2 border-white dark:border-gray-800',
                    index === 0
                      ? 'bg-indigo-500 ring-4 ring-indigo-500/20'
                      : 'bg-gray-400 dark:bg-gray-600'
                  )} />

                  <div className={cn(
                    'p-4 rounded-xl transition-all duration-200',
                    index === 0
                      ? 'bg-indigo-500/10 border border-indigo-500/20'
                      : 'bg-white/40 dark:bg-gray-700/40 hover:bg-white/60 dark:hover:bg-gray-700/60'
                  )}>
                    <div className="flex items-center justify-between mb-2">
                      <span className={cn(
                        'text-sm font-semibold',
                        index === 0
                          ? 'text-indigo-600 dark:text-indigo-400'
                          : 'text-gray-700 dark:text-gray-300'
                      )}>
                        {entry.date}
                      </span>
                      {index === 0 && (
                        <span className="px-2 py-0.5 text-xs font-medium bg-indigo-500 text-white rounded-full">
                          Latest
                        </span>
                      )}
                    </div>

                    <div className="grid grid-cols-3 gap-3">
                      <div className="flex items-center gap-2">
                        <Scale className="h-4 w-4 text-blue-500" />
                        <div>
                          <p className="text-xs text-gray-500">Weight</p>
                          <p className="text-sm font-semibold text-gray-900 dark:text-white">
                            {entry.weight} kg
                          </p>
                        </div>
                      </div>
                      <div className="flex items-center gap-2">
                        <Activity className="h-4 w-4 text-emerald-500" />
                        <div>
                          <p className="text-xs text-gray-500">SMM</p>
                          <p className="text-sm font-semibold text-gray-900 dark:text-white">
                            {entry.smm} kg
                          </p>
                        </div>
                      </div>
                      <div className="flex items-center gap-2">
                        <Percent className="h-4 w-4 text-orange-500" />
                        <div>
                          <p className="text-xs text-gray-500">Body Fat</p>
                          <p className="text-sm font-semibold text-gray-900 dark:text-white">
                            {entry.pbf}%
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </motion.div>
              ))}
            </div>
          </div>
        </CardContent>
      </Card>
    </motion.div>
  );
}
