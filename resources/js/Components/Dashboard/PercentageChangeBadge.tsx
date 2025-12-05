import { ArrowUpRight, ArrowDownRight, Minus } from 'lucide-react';

interface PercentageChangeBadgeProps {
    percentage: number;
    trend: 'up' | 'down' | 'neutral';
    label?: string;
    reverseColor?: boolean; // If true, up is bad (red) and down is good (green)
}

export default function PercentageChangeBadge({
    percentage,
    trend,
    label = "vs last week",
    reverseColor = false
}: PercentageChangeBadgeProps) {

    const isPositive = trend === 'up';
    const isNeutral = trend === 'neutral';

    // Determine color based on trend and reverseColor preference
    let colorClass = "";
    let Icon = Minus;

    if (isNeutral) {
        colorClass = "text-gray-500 bg-gray-100 dark:text-gray-400 dark:bg-gray-800";
        Icon = Minus;
    } else if ((isPositive && !reverseColor) || (!isPositive && reverseColor)) {
        colorClass = "text-emerald-700 bg-emerald-50 dark:text-emerald-400 dark:bg-emerald-500/10";
        Icon = ArrowUpRight;
    } else {
        colorClass = "text-rose-700 bg-rose-50 dark:text-rose-400 dark:bg-rose-500/10";
        Icon = ArrowDownRight;
    }

    // Override icon for negative trend regardless of color meaning
    if (!isPositive && !isNeutral) Icon = ArrowDownRight;

    return (
        <div className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${colorClass}`}>
            <Icon className="w-3 h-3 mr-1" />
            <span>{percentage}% {label}</span>
        </div>
    );
}
