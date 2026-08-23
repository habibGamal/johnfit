import { router } from '@inertiajs/react';
import { WeeklyAdherence, WeeklyAdherenceDay } from '@/types';
import { ChevronLeft, ChevronRight, Lock, CheckCircle2 } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { cn } from '@/lib/utils';

interface DateNavigatorProps {
    selectedDate: string;
    weeklyAdherence: WeeklyAdherence;
}

export default function DateNavigator({ selectedDate, weeklyAdherence }: DateNavigatorProps) {
    const handleSelectDate = (dateStr: string) => {
        if (dateStr === selectedDate) return;
        router.get(route('schedule.index'), { date: dateStr }, { preserveState: true, preserveScroll: true });
    };

    const handleShiftWeek = (direction: 'prev' | 'next') => {
        const current = new Date(selectedDate);
        current.setDate(current.getDate() + (direction === 'next' ? 7 : -7));
        const newDateStr = current.toISOString().split('T')[0];
        handleSelectDate(newDateStr);
    };

    const handleGoToday = () => {
        const todayStr = new Date().toISOString().split('T')[0];
        handleSelectDate(todayStr);
    };

    const isTodaySelected = new Date().toISOString().split('T')[0] === selectedDate;

    return (
        <div className="bg-card border border-border rounded-xl p-4 shadow-sm space-y-4">
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="icon"
                        className="h-8 w-8 rounded-lg"
                        onClick={() => handleShiftWeek('prev')}
                        aria-label="Previous week"
                    >
                        <ChevronLeft className="h-4 w-4" />
                    </Button>
                    <span className="text-sm font-semibold text-foreground">
                        {new Date(selectedDate).toLocaleDateString('en-US', {
                            month: 'short',
                            year: 'numeric',
                        })}
                    </span>
                    <Button
                        variant="outline"
                        size="icon"
                        className="h-8 w-8 rounded-lg"
                        onClick={() => handleShiftWeek('next')}
                        aria-label="Next week"
                    >
                        <ChevronRight className="h-4 w-4" />
                    </Button>
                </div>

                {!isTodaySelected && (
                    <Button
                        variant="secondary"
                        size="sm"
                        onClick={handleGoToday}
                        className="text-xs h-8 px-3 font-medium text-primary hover:text-primary/90"
                    >
                        Today
                    </Button>
                )}
            </div>

            {/* Days strip */}
            <div className="grid grid-cols-7 gap-1 sm:gap-2">
                {weeklyAdherence.days.map((day: WeeklyAdherenceDay) => {
                    const isSelected = day.date === selectedDate;
                    const hasTarget = day.target_score > 0;

                    return (
                        <button
                            key={day.date}
                            onClick={() => handleSelectDate(day.date)}
                            className={cn(
                                'relative flex flex-col items-center py-2.5 px-1 rounded-xl transition-all duration-150',
                                'border text-center group focus:outline-none focus:ring-2 focus:ring-primary/50',
                                isSelected
                                    ? 'bg-primary text-primary-foreground border-primary shadow-md font-semibold'
                                    : 'bg-secondary/40 hover:bg-secondary border-border/60 text-foreground',
                                day.is_today && !isSelected && 'ring-2 ring-primary/40'
                            )}
                        >
                            <span
                                className={cn(
                                    'text-[11px] font-medium uppercase tracking-wider',
                                    isSelected ? 'text-primary-foreground/90' : 'text-muted-foreground'
                                )}
                            >
                                {day.day_name}
                            </span>
                            <span className="text-sm sm:text-base font-bold my-0.5">
                                {new Date(day.date).getDate()}
                            </span>

                            {/* Status dots or icons */}
                            <div className="mt-1 flex items-center justify-center min-h-[14px]">
                                {day.is_locked ? (
                                    <Lock
                                        className={cn(
                                            'h-3 w-3',
                                            isSelected ? 'text-primary-foreground/80' : 'text-muted-foreground/60'
                                        )}
                                    />
                                ) : day.is_completed ? (
                                    <CheckCircle2
                                        className={cn(
                                            'h-3.5 w-3.5',
                                            isSelected ? 'text-primary-foreground' : 'text-emerald-500'
                                        )}
                                    />
                                ) : hasTarget ? (
                                    <span
                                        className={cn(
                                            'text-[10px] font-semibold px-1 rounded',
                                            isSelected
                                                ? 'text-primary-foreground/80'
                                                : 'text-muted-foreground'
                                        )}
                                    >
                                        {day.percentage}%
                                    </span>
                                ) : (
                                    <span className="block h-1 w-1 rounded-full bg-muted-foreground/30" />
                                )}
                            </div>
                        </button>
                    );
                })}
            </div>
        </div>
    );
}
