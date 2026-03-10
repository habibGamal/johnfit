import { cn } from '@/lib/utils';
import { LucideIcon } from 'lucide-react';
import { motion } from 'framer-motion';

interface AchievementStatProps {
    title: string;
    subtitle: string;
    progress: number; // 0 to 100
    icon?: LucideIcon;
    className?: string;
}

export default function AchievementStat({
    title,
    subtitle,
    progress,
    icon: Icon,
    className
}: AchievementStatProps) {
    // Calculate circle path for SVG
    const radius = 30;
    const circumference = 2 * Math.PI * radius;
    const strokeDashoffset = circumference - (progress / 100) * circumference;

    const isCompleted = progress === 100;

    return (
        <motion.div
            initial={{ opacity: 0, scale: 0.9 }}
            animate={{
                opacity: 1,
                scale: isCompleted ? [1, 1.02, 1] : 1,
            }}
            transition={{
                opacity: { duration: 0.3 },
                scale: {
                    duration: 2,
                    repeat: isCompleted ? Infinity : 0,
                    repeatType: "reverse",
                },
            }}
            whileHover={{
                scale: 1.05,
                transition: { duration: 0.2 },
            }}
            className={cn(
                "relative flex flex-col items-center justify-center p-6 rounded-[24px] border h-[220px] transition-all duration-300 cursor-pointer",
                isCompleted
                    ? "bg-gradient-to-br from-primary/10 to-primary/5 border-primary/30 hover:border-primary/50 hover:shadow-[0_0_25px_-10px_rgba(252,211,77,0.4)]"
                    : "bg-[#18181B] border-zinc-800 hover:border-zinc-700 hover:bg-[#202023] hover:shadow-[0_0_20px_-10px_rgba(255,255,255,0.1)]",
                className
            )}
        >
            {/* Circular Progress */}
            <div className="relative mb-4">
                {/* Background Circle */}
                <div className={cn(
                    "h-24 w-24 rounded-full border-[6px] flex items-center justify-center transition-all duration-300",
                    isCompleted
                        ? "border-primary/20 bg-primary/10"
                        : "border-zinc-800 bg-zinc-900/50"
                )}>
                    {Icon && (
                        <Icon className={cn(
                            "w-8 h-8 transition-colors duration-300",
                            isCompleted ? "text-primary" : "text-zinc-500"
                        )} />
                    )}
                </div>

                {/* Progress SVG Overlay */}
                <svg className="absolute top-0 left-0 h-24 w-24 rotate-[-90deg] drop-shadow-[0_0_10px_rgba(252,211,77,0.2)]">
                    <motion.circle
                        cx="48"
                        cy="48"
                        r={radius}
                        fill="none"
                        stroke="#FCD34D"
                        strokeWidth="6"
                        strokeLinecap="round"
                        strokeDasharray={circumference}
                        initial={{ strokeDashoffset: circumference }}
                        animate={{ strokeDashoffset }}
                        transition={{
                            duration: 1,
                            ease: "easeOut",
                            type: "spring",
                            stiffness: 50,
                        }}
                    />
                </svg>
            </div>

            <h4 className="text-lg font-bold text-white mb-1 text-center">
                {title}
            </h4>

            <p className={cn(
                "text-sm font-medium text-center transition-colors duration-300",
                isCompleted ? "text-primary/80" : "text-zinc-500"
            )}>
                {subtitle}
            </p>
        </motion.div>
    );
}
