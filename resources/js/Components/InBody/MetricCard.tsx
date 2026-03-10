import { motion } from 'framer-motion';
import { LucideIcon } from 'lucide-react';
import { Card, CardContent } from '@/Components/ui/card';
import TrendPill from './TrendPill';
import { cn } from '@/lib/utils';

interface MetricCardProps {
  title: string;
  value: number;
  unit: string;
  icon: LucideIcon;
  change?: {
    absolute: number;
    percentage: number;
    trend: 'up' | 'down' | 'stable';
  };
  invertTrend?: boolean;
  iconColor?: string;
  bgGradient?: string;
  delay?: number;
}

export default function MetricCard({
  title,
  value,
  unit,
  icon: Icon,
  change,
  invertTrend = false,
  iconColor = 'text-primary',
  delay = 0,
}: MetricCardProps) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4, delay }}
    >
      <Card className={cn(
        'relative overflow-hidden border-border bg-card',
        'hover:shadow-md transition-shadow duration-300'
      )}>
        <CardContent className="relative p-5">
          <div className="flex items-start justify-between">
            <div className="flex-1">
              <p className="text-sm font-medium text-muted-foreground mb-1">
                {title}
              </p>
              <div className="flex items-baseline gap-1.5">
                <motion.span
                  className="text-3xl font-bold text-foreground"
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  transition={{ duration: 0.3, delay: delay + 0.2 }}
                >
                  {value.toFixed(unit === 'kcal' ? 0 : 1)}
                </motion.span>
                <span className="text-sm font-medium text-muted-foreground">
                  {unit}
                </span>
              </div>
              {change && change.trend !== 'stable' && (
                <div className="mt-2">
                  <TrendPill
                    value={change.absolute}
                    unit={unit === '%' ? ' pp' : ` ${unit}`}
                    trend={change.trend}
                    invertColors={invertTrend}
                    size="sm"
                  />
                </div>
              )}
            </div>
            <div className={cn(
              'p-3 rounded-xl bg-background border border-border',
              'shadow-sm'
            )}>
              <Icon className={cn('h-6 w-6', iconColor)} />
            </div>
          </div>
        </CardContent>
      </Card>
    </motion.div>
  );
}
