import { ReactNode } from 'react';
import { motion } from 'framer-motion';

export interface GlanceTile {
    id: string;
    label: string;
    /** e.g. "3 of 5" */
    value: string;
    /** 0-100 */
    percent: number;
    icon: ReactNode;
    iconClassName: string;
    barClassName: string;
    /** e.g. "2 more to go" */
    caption?: string;
}

interface WeekAtGlanceProps {
    title?: string;
    subtitle?: string;
    tiles: GlanceTile[];
}

/**
 * Answer-first summary: the three numbers a user actually opens the
 * dashboard to check, before any chart or drill-down.
 */
export default function WeekAtGlance({ title = 'This week', subtitle, tiles }: WeekAtGlanceProps) {
    if (!tiles.length) return null;

    return (
        <motion.section
            initial={{ opacity: 0, y: 12 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.4 }}
            aria-label={title}
            className="rounded-2xl border border-border bg-card/60 p-4 shadow-sm sm:p-5"
        >
            <div className="mb-3">
                <h2 className="text-base font-bold text-foreground sm:text-lg">{title}</h2>
                {subtitle ? <p className="text-xs text-muted-foreground">{subtitle}</p> : null}
            </div>

            <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
                {tiles.map((tile) => (
                    <div
                        key={tile.id}
                        className="flex flex-col rounded-xl border border-border bg-background/40 p-3"
                    >
                        <div className="mb-1.5 flex items-center gap-2">
                            <span className={`flex h-7 w-7 items-center justify-center rounded-lg ${tile.iconClassName}`}>
                                {tile.icon}
                            </span>
                            <span className="truncate text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">
                                {tile.label}
                            </span>
                        </div>

                        <p className="text-xl font-extrabold leading-none text-foreground">{tile.value}</p>

                        <div className="mt-2.5 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                            <div
                                className={`h-full rounded-full transition-all duration-700 ${tile.barClassName}`}
                                style={{ width: `${Math.max(0, Math.min(100, tile.percent))}%` }}
                            />
                        </div>

                        {tile.caption ? (
                            <p className="mt-1.5 truncate text-[11px] font-medium text-muted-foreground">
                                {tile.caption}
                            </p>
                        ) : null}
                    </div>
                ))}
            </div>
        </motion.section>
    );
}
