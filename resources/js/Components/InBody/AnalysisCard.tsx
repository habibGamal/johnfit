import { motion, AnimatePresence } from 'framer-motion';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Activity, Sparkles, TrendingUp, TrendingDown, Minus, Target } from 'lucide-react';
import { InBodyAnalysisResult } from '@/types';
import { cn } from '@/lib/utils';

interface AnalysisCardProps {
  analysis: InBodyAnalysisResult;
}

export default function AnalysisCard({ analysis }: AnalysisCardProps) {
  const statusConfig = {
    excellent: {
      bgColor: 'from-emerald-500/20 to-emerald-500/5',
      borderColor: 'border-emerald-500/30',
      textColor: 'text-emerald-600 dark:text-emerald-400',
      icon: Sparkles,
      label: 'Excellent Progress',
    },
    positive: {
      bgColor: 'from-green-500/20 to-green-500/5',
      borderColor: 'border-green-500/30',
      textColor: 'text-green-600 dark:text-green-400',
      icon: TrendingUp,
      label: 'Positive Progress',
    },
    neutral: {
      bgColor: 'from-blue-500/20 to-blue-500/5',
      borderColor: 'border-blue-500/30',
      textColor: 'text-blue-600 dark:text-blue-400',
      icon: Minus,
      label: 'Stable',
    },
    negative: {
      bgColor: 'from-orange-500/20 to-orange-500/5',
      borderColor: 'border-orange-500/30',
      textColor: 'text-orange-600 dark:text-orange-400',
      icon: TrendingDown,
      label: 'Needs Attention',
    },
  };

  const config = statusConfig[analysis.status];
  const StatusIcon = config.icon;

  const indicatorConfig = {
    gaining: { icon: TrendingUp, color: 'text-emerald-500' },
    losing: { icon: TrendingDown, color: 'text-red-500' },
    maintaining: { icon: Minus, color: 'text-gray-500' },
    stable: { icon: Minus, color: 'text-gray-500' },
  };

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5, delay: 0.3 }}
    >
      <Card className={cn(
        'relative overflow-hidden border shadow-lg backdrop-blur-xl',
        'bg-gradient-to-br from-white/80 to-white/60 dark:from-gray-800/80 dark:to-gray-800/60',
        config.borderColor
      )}>
        {/* Decorative gradient overlay */}
        <div className={cn(
          'absolute inset-0 bg-gradient-to-br opacity-30',
          config.bgColor
        )} />

        <CardHeader className="relative">
          <CardTitle className="flex items-center gap-3 text-lg font-bold text-gray-900 dark:text-white">
            <motion.div
              className={cn('p-2 rounded-xl bg-white/50 dark:bg-gray-700/50', config.textColor)}
              initial={{ rotate: -10, scale: 0.9 }}
              animate={{ rotate: 0, scale: 1 }}
              transition={{ type: 'spring', stiffness: 300 }}
            >
              <Activity className="h-5 w-5" />
            </motion.div>
            Body Composition Analysis
          </CardTitle>
        </CardHeader>

        <CardContent className="relative space-y-6">
          {/* Status Badge */}
          <div className="flex items-center gap-3">
            <motion.div
              className={cn(
                'flex items-center gap-2 px-4 py-2 rounded-full',
                'bg-white/60 dark:bg-gray-700/60 backdrop-blur-sm',
                'border shadow-sm',
                config.borderColor
              )}
              initial={{ scale: 0.9 }}
              animate={{ scale: 1 }}
              transition={{ type: 'spring', stiffness: 400, damping: 20 }}
            >
              <StatusIcon className={cn('h-5 w-5', config.textColor)} />
              <span className={cn('font-semibold', config.textColor)}>
                {config.label}
              </span>
            </motion.div>

            {/* Progress Score */}
            <div className="flex items-center gap-2 px-3 py-1.5 bg-white/40 dark:bg-gray-700/40 rounded-full">
              <Target className="h-4 w-4 text-indigo-500" />
              <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
                Score: <span className={cn(
                  'font-bold',
                  analysis.score > 0 ? 'text-emerald-600' : analysis.score < 0 ? 'text-red-600' : 'text-gray-600'
                )}>{analysis.score > 0 ? '+' : ''}{analysis.score}</span>
              </span>
            </div>
          </div>

          {/* Description */}
          <motion.p
            className="text-gray-700 dark:text-gray-300 leading-relaxed"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ delay: 0.2 }}
          >
            {analysis.description}
          </motion.p>

          {/* Indicators Grid */}
          <div className="grid grid-cols-3 gap-4">
            {(['muscle_trend', 'fat_trend', 'weight_trend'] as const).map((key, index) => {
              const trend = analysis.indicators[key] as 'gaining' | 'losing' | 'maintaining' | 'stable';
              const indConfig = indicatorConfig[trend];
              const TrendIcon = indConfig.icon;
              const labels = {
                muscle_trend: 'Muscle',
                fat_trend: 'Fat',
                weight_trend: 'Weight',
              };
              const changes = {
                muscle_trend: `${analysis.indicators.smm_change_kg > 0 ? '+' : ''}${analysis.indicators.smm_change_kg} kg`,
                fat_trend: `${analysis.indicators.pbf_change_pct > 0 ? '+' : ''}${analysis.indicators.pbf_change_pct}%`,
                weight_trend: `${analysis.indicators.weight_change_kg > 0 ? '+' : ''}${analysis.indicators.weight_change_kg} kg`,
              };

              return (
                <motion.div
                  key={key}
                  className="flex flex-col items-center p-3 bg-white/40 dark:bg-gray-700/40 rounded-xl"
                  initial={{ opacity: 0, y: 10 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.3 + index * 0.1 }}
                >
                  <TrendIcon className={cn('h-5 w-5 mb-1', indConfig.color)} />
                  <span className="text-xs font-medium text-gray-500 dark:text-gray-400">
                    {labels[key]}
                  </span>
                  <span className="text-sm font-bold text-gray-900 dark:text-white">
                    {changes[key]}
                  </span>
                  <span className="text-xs text-gray-500 capitalize">
                    {trend}
                  </span>
                </motion.div>
              );
            })}
          </div>

          {/* Recommendations */}
          <AnimatePresence>
            {analysis.recommendations.length > 0 && (
              <motion.div
                className="space-y-2"
                initial={{ opacity: 0, height: 0 }}
                animate={{ opacity: 1, height: 'auto' }}
                exit={{ opacity: 0, height: 0 }}
              >
                <h4 className="text-sm font-semibold text-gray-700 dark:text-gray-300">
                  Recommendations
                </h4>
                <ul className="space-y-2">
                  {analysis.recommendations.map((rec, index) => (
                    <motion.li
                      key={index}
                      className="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400"
                      initial={{ opacity: 0, x: -10 }}
                      animate={{ opacity: 1, x: 0 }}
                      transition={{ delay: 0.4 + index * 0.1 }}
                    >
                      <span className="w-1.5 h-1.5 mt-2 rounded-full bg-indigo-500 flex-shrink-0" />
                      {rec}
                    </motion.li>
                  ))}
                </ul>
              </motion.div>
            )}
          </AnimatePresence>
        </CardContent>
      </Card>
    </motion.div>
  );
}
