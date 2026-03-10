import { Link } from '@inertiajs/react';
import { ArrowRight, Lock } from 'lucide-react';
import { cn } from '@/lib/utils';
import { Button } from '@/Components/ui/button';
import { useState, useRef } from 'react';
import { motion } from 'framer-motion';

interface QuickActionCardProps {
    title: string;
    description: string;
    bgImage?: string;
    actionLabel: string;
    actionRoute: string;
    className?: string;
    isLocked?: boolean;
}

export default function QuickActionCard({
    title,
    description,
    bgImage,
    actionLabel,
    actionRoute,
    className,
    isLocked = false
}: QuickActionCardProps) {
    const [mousePosition, setMousePosition] = useState({ x: 0, y: 0 });
    const cardRef = useRef<HTMLDivElement>(null);

    const handleMouseMove = (e: React.MouseEvent<HTMLDivElement>) => {
        if (!cardRef.current) return;
        const rect = cardRef.current.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width - 0.5) * 20;
        const y = ((e.clientY - rect.top) / rect.height - 0.5) * 20;
        setMousePosition({ x, y });
    };

    const handleMouseLeave = () => {
        setMousePosition({ x: 0, y: 0 });
    };

    return (
        <motion.div
            ref={cardRef}
            onMouseMove={handleMouseMove}
            onMouseLeave={handleMouseLeave}
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
            style={{
                transform: `perspective(1000px) rotateX(${-mousePosition.y * 0.1}deg) rotateY(${mousePosition.x * 0.1}deg)`,
            }}
            className={cn(
                "group relative overflow-hidden rounded-3xl h-[240px] w-full transition-all duration-300 hover:shadow-xl hover:shadow-primary/10 cursor-pointer will-change-transform",
                className
            )}
        >
            {/* Background Image / Color */}
            <div className="absolute inset-0 z-0">
                {bgImage ? (
                    <img
                        src={bgImage}
                        alt={title}
                        className="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                    />
                ) : (
                    <div className="h-full w-full bg-gradient-to-br from-zinc-800 to-zinc-900" />
                )}
                {/* Enhanced Gradient Overlay for Text Readability */}
                <div className="absolute inset-0 bg-gradient-to-t from-black/95 via-black/60 to-transparent z-10" />
            </div>

            {/* Content */}
            <div className="relative z-20 flex flex-col justify-end h-full p-6">
                <div className="mb-auto">
                    {/* Optional: Add icon here if needed */}
                </div>

                <h3 className="text-2xl font-bold text-white mb-2 leading-tight drop-shadow-sm">
                    {title}
                </h3>

                <p className="text-zinc-300 text-sm mb-6 line-clamp-2 max-w-[90%]">
                    {description}
                </p>

                <div className="flex items-center justify-between">
                    {isLocked ? (
                        <div className="flex items-center gap-2 text-zinc-400 bg-zinc-900/50 backdrop-blur-md px-4 py-2 rounded-full text-sm font-medium border border-zinc-700/50">
                            <Lock className="w-4 h-4" />
                            <span>Locked</span>
                        </div>
                    ) : (
                        <Button
                            asChild
                            className="bg-[#FCD34D] hover:bg-[#FCD34D]/90 text-black font-semibold rounded-full px-6 transition-all duration-300 shadow-[0_0_15px_-3px_rgba(252,211,77,0.3)] hover:shadow-[0_0_25px_-3px_rgba(252,211,77,0.6)]"
                        >
                            <Link href={actionRoute} className="flex items-center gap-2">
                                {actionLabel}
                                <ArrowRight className="w-4 h-4 transition-transform duration-300 group-hover:translate-x-1" />
                            </Link>
                        </Button>
                    )}
                </div>
            </div>
        </motion.div>
    );
}
