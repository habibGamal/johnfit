import { ReactNode } from "react";

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
    <div className={`flex items-center gap-3 rounded-xl border ${className} px-4 py-2.5 transition-all hover:scale-105 duration-300`}>
      <div className={`p-1.5 rounded-full bg-white/50 dark:bg-black/20 ${iconClassName}`}>
        {icon}
      </div>
      <div>
        <p className="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{label}</p>
        <p className={`text-lg font-bold leading-none mt-0.5 ${valueClassName}`}>{value}</p>
      </div>
    </div>
  );
}
