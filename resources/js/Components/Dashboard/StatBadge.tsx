import { ReactNode } from "react";
import { LucideIcon } from "lucide-react";

interface StatBadgeProps {
  icon: ReactNode;
  label: string;
  value: string | number;
  className?: string;
  valueClassName?: string;
  iconClassName?: string;
}

export default function StatBadge({
  icon,
  label,
  value,
  className = "bg-primary/10",
  valueClassName = "",
  iconClassName = "text-primary",
}: StatBadgeProps) {
  return (
    <div className={`flex items-center gap-2 rounded-lg ${className} px-3 py-1`}>
      <div className={iconClassName}>{icon}</div>
      <div className="p-2">
        <p className="text-xs text-muted-foreground">{label}</p>
        <p className={`text-lg font-bold ${valueClassName}`}>{value}</p>
      </div>
    </div>
  );
}
