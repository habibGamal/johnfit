import { motion } from 'framer-motion';
import { TrendingUp, TrendingDown, Minus } from 'lucide-react';
import { cn } from '@/lib/utils';

interface TrendPillProps {
  value: number;
  unit?: string;
  trend: 'up' | 'down' | 'stable';
  invertColors?: boolean; // For metrics where down is good (like body fat)
  size?: 'sm' | 'md' | 'lg';
  showIcon?: boolean;
  className?: string;
}

export default function TrendPill({
  value,
  unit = '%',
  trend,
  invertColors = false,
  size = 'md',
  showIcon = true,
  className,
}: TrendPillProps) {
  // Determine if the trend is positive or negative based on context
  const isPositive = invertColors ? trend === 'down' : trend === 'up';
  const isNegative = invertColors ? trend === 'up' : trend === 'down';

  const colorClasses = {
    positive: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
    negative: 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-200 dark:border-red-800',
    neutral: 'bg-gray-500/10 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-700',
  };

  const sizeClasses = {
    sm: 'px-1.5 py-0.5 text-xs gap-0.5',
    md: 'px-2 py-1 text-sm gap-1',
    lg: 'px-3 py-1.5 text-base gap-1.5',
  };

  const iconSizes = {
    sm: 'h-3 w-3',
    md: 'h-4 w-4',
    lg: 'h-5 w-5',
  };

  const Icon = trend === 'up' ? TrendingUp : trend === 'down' ? TrendingDown : Minus;
  const colorClass = isPositive ? colorClasses.positive : isNegative ? colorClasses.negative : colorClasses.neutral;

  return (
    <motion.span
      initial={{ scale: 0.9, opacity: 0 }}
      animate={{ scale: 1, opacity: 1 }}
      transition={{ type: 'spring', stiffness: 500, damping: 25 }}
      className={cn(
        'inline-flex items-center rounded-full border font-medium',
        sizeClasses[size],
        colorClass,
        className
      )}
    >
      {showIcon && <Icon className={iconSizes[size]} />}
      <span>
        {trend === 'up' ? '+' : trend === 'down' ? '' : ''}
        {value.toFixed(1)}{unit}
      </span>
    </motion.span>
  );
}
