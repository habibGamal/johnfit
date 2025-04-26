import { Progress } from "@/Components/ui/progress";

interface CompletionProgressProps {
  completed: number;
  total: number;
  percentage: number;
}

export default function CompletionProgress({
  completed,
  total,
  percentage
}: CompletionProgressProps) {
  return (
    <div>
      <div className="flex items-center justify-between mb-1">
        <span className="text-sm font-medium">Overall Completion</span>
        <span className="text-sm font-medium">{percentage}%</span>
      </div>
      <Progress
        value={percentage}
        className="h-3 bg-muted"
      />
      <p className="mt-1 text-xs text-muted-foreground text-right">
        {completed} of {total} workouts completed
      </p>
    </div>
  );
}
