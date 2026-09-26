import { ReactNode } from 'react';
import { motion } from 'framer-motion';

interface ProgressRingProps {
    /** Completion percentage, 0-100 */
    value: number;
    size?: number;
    strokeWidth?: number;
    /** Tailwind colour class for the background track */
    trackClassName?: string;
    /** Tailwind colour class for the filled arc */
    progressClassName?: string;
    children?: ReactNode;
}

/**
 * A circular progress indicator.
 *
 * Deliberately tooltip-free: on touch devices a chart that only reveals
 * numbers on hover is unreadable, so the value lives in the centre.
 */
export default function ProgressRing({
    value,
    size = 96,
    strokeWidth = 9,
    trackClassName = 'text-muted',
    progressClassName = 'text-primary',
    children,
}: ProgressRingProps) {
    const safeValue = Math.max(0, Math.min(100, Number.isFinite(value) ? value : 0));

    const radius = (size - strokeWidth) / 2;
    const circumference = 2 * Math.PI * radius;
    const offset = circumference - (safeValue / 100) * circumference;

    return (
        <div className="relative shrink-0" style={{ width: size, height: size }}>
            <svg
                width={size}
                height={size}
                className="-rotate-90"
                role="img"
                aria-label={`${Math.round(safeValue)} percent complete`}
            >
                <circle
                    cx={size / 2}
                    cy={size / 2}
                    r={radius}
                    fill="none"
                    strokeWidth={strokeWidth}
                    className={trackClassName}
                />
                <motion.circle
                    cx={size / 2}
                    cy={size / 2}
                    r={radius}
                    fill="none"
                    strokeWidth={strokeWidth}
                    strokeLinecap="round"
                    className={progressClassName}
                    strokeDasharray={circumference}
                    initial={{ strokeDashoffset: circumference }}
                    animate={{ strokeDashoffset: offset }}
                    transition={{ duration: 0.9, ease: 'easeOut' }}
                />
            </svg>

            <div className="absolute inset-0 flex flex-col items-center justify-center text-center leading-none">
                {children}
            </div>
        </div>
    );
}
