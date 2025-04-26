interface DailyActivityBarProps {
  day: string;
  count: number;
  percentage: number;
}

export default function DailyActivityBar({ day, count, percentage }: DailyActivityBarProps) {
  return (
    <div className="flex items-center">
      <div className="w-24 text-xs font-medium">{day}</div>
      <div className="flex-1 mr-4">
        <div className="flex h-2 overflow-hidden rounded-full bg-muted">
          <div
            className="bg-primary"
            style={{ width: `${percentage}%` }}
          />
        </div>
      </div>
      <div className="w-8 text-right text-xs font-medium">
        {count}
      </div>
    </div>
  );
}
