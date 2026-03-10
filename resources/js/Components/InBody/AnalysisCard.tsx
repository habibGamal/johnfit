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
      borderColor: 'border-emerald-500/30',
      textColor: 'text-emerald-500',
      icon: Sparkles,
      label: 'Excellent Progress',
    },
    positive: {
      borderColor: 'border-green-500/30',
      textColor: 'text-green-500',
      icon: TrendingUp,
      label: 'Positive Progress',
    },
    neutral: {
      borderColor: 'border-blue-500/30',
      textColor: 'text-blue-500',
      icon: Minus,
      label: 'Stable',
    },
    negative: {
      borderColor: 'border-orange-500/30',
      textColor: 'text-orange-500',
      icon: TrendingDown,
      label: 'Needs Attention',
    },
  };

  const config = statusConfig[analysis.status];
  const StatusIcon = config.icon;

  const indicatorConfig = {
    gaining: { icon: TrendingUp, color: 'text-emerald-500' },
    losing: { icon: TrendingDown, color: 'text-destructive' },
    maintaining: { icon: Minus, color: 'text-muted-foreground' },
    stable: { icon: Minus, color: 'text-muted-foreground' },
  };

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5, delay: 0.3 }}
    >
      <Card className={cn(
        'relative overflow-hidden border-border bg-card',
        config.borderColor
      )}>
        <CardHeader className="relative">
          <CardTitle className="flex items-center gap-3 text-lg font-bold text-foreground">
            <motion.div
              className={cn('p-2 rounded-xl bg-muted', config.textColor)}
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
                'bg-muted/50 backdrop-blur-sm',
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
            <div className="flex items-center gap-2 px-3 py-1.5 bg-muted/50 rounded-full">
              <Target className="h-4 w-4 text-primary" />
              <span className="text-sm font-medium text-foreground">
                Score: <span className={cn(
                  'font-bold',
                  analysis.score > 0 ? 'text-emerald-500' : analysis.score < 0 ? 'text-destructive' : 'text-muted-foreground'
                )}>{analysis.score > 0 ? '+' : ''}{analysis.score}</span>
              </span>
            </div>
          </div>

          {/* Description */}
          <motion.p
            className="text-muted-foreground leading-relaxed"
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
                  className="flex flex-col items-center p-3 bg-muted/30 rounded-xl"
                  initial={{ opacity: 0, y: 10 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.3 + index * 0.1 }}
                >
                  <TrendIcon className={cn('h-5 w-5 mb-1', indConfig.color)} />
                  <span className="text-xs font-medium text-muted-foreground">
                    {labels[key]}
                  </span>
                  <span className="text-sm font-bold text-foreground">
                    {changes[key]}
                  </span>
                  <span className="text-xs text-muted-foreground capitalize">
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
                <h4 className="text-sm font-semibold text-foreground">
                  Recommendations
                </h4>
                <ul className="space-y-2">
                  {analysis.recommendations.map((rec, index) => (
                    <motion.li
                      key={index}
                      className="flex items-start gap-2 text-sm text-muted-foreground"
                      initial={{ opacity: 0, x: -10 }}
                      animate={{ opacity: 1, x: 0 }}
                      transition={{ delay: 0.4 + index * 0.1 }}
                    >
                      <span className="w-1.5 h-1.5 mt-2 rounded-full bg-primary flex-shrink-0" />
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
