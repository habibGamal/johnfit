import { Check } from 'lucide-react';

const WEEK_DAYS: { full: string; short: string }[] = [
    { full: 'Monday', short: 'Mon' },
    { full: 'Tuesday', short: 'Tue' },
    { full: 'Wednesday', short: 'Wed' },
    { full: 'Thursday', short: 'Thu' },
    { full: 'Friday', short: 'Fri' },
    { full: 'Saturday', short: 'Sat' },
    { full: 'Sunday', short: 'Sun' },
];

interface WeekStripProps {
    /** Day name (e.g. "Monday") -> number of completed items */
    activeDays?: Record<string, number>;
    /** Optional helper line, e.g. "4 of 7 days hit" */
    summaryLabel?: string;
    activeClassName?: string;
    inactiveClassName?: string;
    todayRingClassName?: string;
    labelClassName?: string;
    className?: string;
}

/**
 * A 7 day "did I show up?" strip.
 *
 * Replaces the old bar chart: a full/empty circle plus a check is readable
 * at a glance on a phone, whereas bar heights need a hover tooltip.
 */
export default function WeekStrip({
    activeDays = {},
    summaryLabel,
    activeClassName = 'bg-emerald-500 text-white border-emerald-500',
    inactiveClassName = 'bg-muted/60 text-muted-foreground border-dashed border-border',
    todayRingClassName = 'ring-2 ring-primary ring-offset-2 ring-offset-background',
    labelClassName = 'text-muted-foreground',
    className = '',
}: WeekStripProps) {
    // Monday-first index for today (JS getDay is Sunday-first).
    const todayIndex = (new Date().getDay() + 6) % 7;
    const activeCount = WEEK_DAYS.filter((d) => (activeDays[d.full] ?? 0) > 0).length;

    return (
        <div className={className}>
            <div className="grid grid-cols-7 gap-1.5 sm:gap-2.5">
                {WEEK_DAYS.map((day, index) => {
                    const count = activeDays[day.full] ?? 0;
                    const isActive = count > 0;
                    const isToday = index === todayIndex;
                    const isUpcoming = index > todayIndex;

                    return (
                        <div key={day.full} className="flex flex-col items-center gap-1.5">
                            <div
                                title={`${day.full}: ${count}`}
                                aria-label={`${day.full}: ${count}`}
                                className={[
                                    'flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-full border text-xs font-bold transition-colors',
                                    isActive ? activeClassName : inactiveClassName,
                                    !isActive && isUpcoming ? 'opacity-40' : '',
                                    isToday ? todayRingClassName : '',
                                ].join(' ')}
                            >
                                {isActive ? (
                                    count > 1 ? (
                                        <span>{count}</span>
                                    ) : (
                                        <Check className="h-4 w-4" strokeWidth={3} />
                                    )
                                ) : null}
                            </div>

                            <span
                                className={[
                                    'text-[10px] font-medium sm:text-xs',
                                    isToday ? 'text-foreground font-bold' : labelClassName,
                                ].join(' ')}
                            >
                                {day.short}
                            </span>
                        </div>
                    );
                })}
            </div>

            {summaryLabel !== undefined ? (
                <p className="mt-3 text-center text-xs text-muted-foreground">
                    {summaryLabel || `${activeCount} of 7 days hit`}
                </p>
            ) : null}
        </div>
    );
}
