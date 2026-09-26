import { motion } from 'framer-motion';
import { Flame, Utensils } from 'lucide-react';
import { NutritionAverages } from '@/types';

/** Macro energy densities (kcal per gram). */
const KCAL = { protein: 4, carbs: 4, fat: 9 } as const;

interface MacroRow {
    key: keyof typeof KCAL;
    label: string;
    grams: number;
    kcal: number;
    share: number; // 0-100
    barClass: string;
    dotClass: string;
}

interface NutritionStatsCardProps {
    nutritionAverages: NutritionAverages;
}

/**
 * Daily macro averages.
 *
 * Uses one horizontal stacked bar plus labelled rows instead of a donut:
 * a donut is unreadable on a phone (tiny segments, no axis) and the old
 * version also showed a misleading "Macros" label in the centre.
 */
export default function NutritionStatsCard({ nutritionAverages }: NutritionStatsCardProps) {
    const avgs = nutritionAverages ?? { calories: 0, protein: 0, carbs: 0, fat: 0 };

    const kcal = {
        protein: (avgs.protein || 0) * KCAL.protein,
        carbs: (avgs.carbs || 0) * KCAL.carbs,
        fat: (avgs.fat || 0) * KCAL.fat,
    };
    const macroKcalTotal = kcal.protein + kcal.carbs + kcal.fat;

    const baseRows: MacroRow[] = [
        { key: 'protein', label: 'Protein', grams: avgs.protein || 0, kcal: kcal.protein, share: 0, barClass: 'bg-yellow-400', dotClass: 'bg-yellow-400' },
        { key: 'carbs', label: 'Carbs', grams: avgs.carbs || 0, kcal: kcal.carbs, share: 0, barClass: 'bg-emerald-400', dotClass: 'bg-emerald-400' },
        { key: 'fat', label: 'Fat', grams: avgs.fat || 0, kcal: kcal.fat, share: 0, barClass: 'bg-blue-400', dotClass: 'bg-blue-400' },
    ];

    const rows: MacroRow[] = baseRows.map((r) => ({
        ...r,
        share: macroKcalTotal > 0 ? Math.round((r.kcal / macroKcalTotal) * 100) : 0,
    }));

    const hasData = (avgs.calories || 0) > 0 || macroKcalTotal > 0;
    const rounded = (n: number) => Math.round(n * 10) / 10;

    return (
        <motion.div
            initial={{ opacity: 0, y: 16 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.45, ease: 'easeOut', delay: 0.05 }}
            className="rounded-2xl border border-border bg-card/60 p-4 shadow-sm sm:p-5"
        >
            <div className="mb-4 flex items-center gap-2.5">
                <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-500/10">
                    <Utensils className="h-5 w-5 text-orange-500" />
                </div>
                <div className="min-w-0">
                    <h3 className="text-base font-bold text-foreground sm:text-lg">Daily Nutrition</h3>
                    <p className="text-xs text-muted-foreground">Your average over the last 7 days</p>
                </div>
            </div>

            {!hasData ? (
                <div className="flex flex-col items-center justify-center rounded-xl border border-dashed border-border py-10 text-center">
                    <p className="text-sm text-muted-foreground">Log a few meals to see your macro split.</p>
                </div>
            ) : (
                <>
                    <div className="flex items-baseline gap-2">
                        <span className="text-3xl font-extrabold leading-none text-foreground">
                            {Math.round(avgs.calories || 0).toLocaleString()}
                        </span>
                        <span className="text-sm font-medium text-muted-foreground">kcal / day</span>
                    </div>

                    {/* Stacked macro bar */}
                    <div className="mt-3 flex h-2.5 w-full overflow-hidden rounded-full bg-muted">
                        {rows.map((r) => (
                            <div
                                key={r.key}
                                className={`h-full transition-all duration-500 ${r.barClass}`}
                                style={{ width: `${r.share}%` }}
                                title={`${r.label}: ${r.share}%`}
                            />
                        ))}
                    </div>

                    {/* Labelled rows */}
                    <div className="mt-4 space-y-2">
                        {rows.map((r) => (
                            <div key={r.key} className="flex items-center gap-3">
                                <span className={`h-2.5 w-2.5 shrink-0 rounded-full ${r.dotClass}`} />
                                <span className="min-w-0 flex-1 truncate text-sm font-medium text-foreground">
                                    {r.label}
                                </span>
                                <span className="text-sm font-bold text-foreground">{rounded(r.grams)}g</span>
                                <span className="w-9 text-right text-xs font-semibold text-muted-foreground">
                                    {r.share}%
                                </span>
                            </div>
                        ))}
                    </div>

                    <p className="mt-4 flex items-start gap-2 border-t border-border pt-3 text-[11px] leading-relaxed text-muted-foreground">
                        <Flame className="mt-0.5 h-3.5 w-3.5 shrink-0" />
                        <span>Percentages show each macro&apos;s share of your daily calories.</span>
                    </p>
                </>
            )}
        </motion.div>
    );
}
