import { cn } from '@/lib/utils';
import type { BadgeTier } from '@/types/achievements';

/**
 * Visual treatment per badge tier. Colours stay inside the JohnFit palette
 * (no purple) and keep text contrast above 4.5:1 on the dark card surface.
 */
export const BADGE_TIER_STYLES: Record<
    BadgeTier,
    { label: string; ring: string; bg: string; text: string; glow: string }
> = {
    bronze: {
        label: 'Bronze',
        ring: 'border-amber-700/60',
        bg: 'bg-amber-950/30',
        text: 'text-amber-500',
        glow: 'shadow-[0_0_24px_-6px_rgba(180,83,9,0.6)]',
    },
    silver: {
        label: 'Silver',
        ring: 'border-slate-400/50',
        bg: 'bg-slate-500/15',
        text: 'text-slate-200',
        glow: 'shadow-[0_0_24px_-6px_rgba(148,163,184,0.5)]',
    },
    gold: {
        label: 'Gold',
        ring: 'border-yellow-400/60',
        bg: 'bg-yellow-500/15',
        text: 'text-yellow-300',
        glow: 'shadow-[0_0_28px_-6px_rgba(250,204,21,0.6)]',
    },
    platinum: {
        label: 'Platinum',
        ring: 'border-cyan-300/50',
        bg: 'bg-cyan-400/15',
        text: 'text-cyan-200',
        glow: 'shadow-[0_0_28px_-6px_rgba(103,232,249,0.55)]',
    },
    diamond: {
        label: 'Diamond',
        ring: 'border-sky-200/60',
        bg: 'bg-sky-300/15',
        text: 'text-sky-100',
        glow: 'shadow-[0_0_32px_-6px_rgba(186,230,253,0.7)]',
    },
};

export function tierStyle(tier: string) {
    return BADGE_TIER_STYLES[(tier as BadgeTier)] ?? BADGE_TIER_STYLES.bronze;
}

/**
 * Human readable rendering of a single requirement, e.g. "Workout Score >= 50".
 */
export function describeRequirement(requirement: {
    metric_label: string;
    operator: 'gte' | 'lte';
    threshold: number;
}): string {
    const operator = requirement.operator === 'lte' ? 'at most' : 'at least';
    return `${requirement.metric_label} ${operator} ${requirement.threshold}`;
}

export function formatValue(value: number | null | undefined): string {
    if (value === null || value === undefined) {
        return '—';
    }

    return Number.isInteger(value) ? String(value) : value.toFixed(1);
}
