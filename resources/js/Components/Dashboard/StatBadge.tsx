import { ReactNode } from "react";
import { motion } from "framer-motion";

interface StatBadgeProps {
  icon: ReactNode;
  label: string;
  value: string | number;
  className?: string;
  valueClassName?: string;
  iconClassName?: string;
  index?: number;
}

export default function StatBadge({
  icon,
  label,
  value,
  className = "bg-primary/10 border-primary/20",
  valueClassName = "text-foreground",
  iconClassName = "text-primary",
  index = 0,
}: StatBadgeProps) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{
        duration: 0.4,
        delay: 0.3 + index * 0.1,
        ease: "easeOut",
      }}
      whileHover={{
        scale: 1.05,
        transition: { duration: 0.2 },
      }}
      className={`flex items-center gap-3 rounded-xl border ${className} px-4 py-2.5 transition-all duration-300 cursor-pointer hover:shadow-[0_0_20px_-5px_rgba(252,211,77,0.3)]`}
    >
      <div className={`p-1.5 rounded-full bg-gradient-to-br from-primary/20 to-primary/5 ${iconClassName}`}>
        {icon}
      </div>
      <div>
        <p className="text-xs font-medium text-muted-foreground uppercase tracking-wider">{label}</p>
        <p className={`text-lg font-bold leading-none mt-0.5 ${valueClassName}`}>{value}</p>
      </div>
    </motion.div>
  );
}
