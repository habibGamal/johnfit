interface DailyActivityBarProps {
  day: string;
  count: number;
  percentage: number;
  colorClass?: string;
}

export default function DailyActivityBar({ day, count, percentage, colorClass = "bg-primary" }: DailyActivityBarProps) {
  return (
    <div className="flex items-center group">
      <div className="w-24 text-xs font-medium text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">{day}</div>
      <div className="flex-1 mr-4">
        <div className="flex h-2.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
          <div
            className={`h-full rounded-full transition-all duration-500 ease-out ${colorClass}`}
            style={{ width: `${percentage}%` }}
          />
        </div>
      </div>
      <div className="w-8 text-right text-xs font-bold text-gray-700 dark:text-gray-300">
        {count}
      </div>
    </div>
  );
}
