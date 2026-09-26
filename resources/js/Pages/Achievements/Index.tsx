import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import BadgeCard from '@/Components/Achievements/BadgeCard';
import StreakFlame from '@/Components/Achievements/StreakFlame';
import { Progress } from '@/Components/ui/progress';
import { Award, Flame, Target, Trophy } from 'lucide-react';
import type { AchievementPageProps, AchievementBadge } from '@/types/achievements';

const STREAK_TYPES = ['workout', 'meal', 'hydration', 'overall'] as const;

export default function AchievementsIndex({ journey }: AchievementPageProps) {
    const badges = journey.badges ?? [];
    const unlocked = badges.filter((b: AchievementBadge) => b.unlocked);
    const inProgress = badges
        .filter((b: AchievementBadge) => !b.unlocked && b.progress > 0)
        .sort((a, b) => b.progress - a.progress);
    const locked = badges.filter((b: AchievementBadge) => !b.unlocked && b.progress === 0);

    const nextBadge = inProgress[0];
    const overallProgress =
        journey.total_count > 0
            ? Math.round((journey.unlocked_count / journey.total_count) * 100)
            : 0;

    return (
        <AuthenticatedLayout>
            <Head title="Achievements" />

            <div className="mx-auto max-w-7xl px-4 pb-24 pt-6 sm:px-6 lg:px-8">
                <header className="mb-8">
                    <h1 className="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        Your Journey
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Badges you have earned and the ones still within reach.
                    </p>
                </header>

                <section
                    aria-label="Journey summary"
                    className="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3"
                >
                    <div className="rounded-2xl border border-border bg-card p-5">
                        <div className="flex items-center gap-2 text-muted-foreground">
                            <Trophy className="h-4 w-4" aria-hidden="true" />
                            <span className="text-xs font-medium uppercase tracking-wide">
                                Badges
                            </span>
                        </div>
                        <p className="mt-2 text-2xl font-bold text-foreground">
                            <span className="tabular-nums">{journey.unlocked_count}</span>
                            <span className="text-base font-normal text-muted-foreground">
                                {' '}
                                / {journey.total_count}
                            </span>
                        </p>
                        <Progress value={overallProgress} className="mt-3 h-1.5" />
                    </div>

                    <div className="rounded-2xl border border-border bg-card p-5">
                        <div className="flex items-center justify-between text-muted-foreground">
                            <div className="flex items-center gap-2">
                                <Target className="h-4 w-4" aria-hidden="true" />
                                <span className="text-xs font-medium uppercase tracking-wide">
                                    Level &amp; Points
                                </span>
                            </div>
                            <span className="rounded-md bg-primary/10 px-2 py-0.5 text-xs font-bold text-primary">
                                Level {journey.points?.level ?? journey.scores?.level ?? 1}
                            </span>
                        </div>
                        <p className="mt-2 text-2xl font-bold text-foreground">
                            <span className="tabular-nums">{journey.points?.total_points ?? journey.scores?.total ?? 0}</span>
                            <span className="text-sm font-normal text-muted-foreground"> pts</span>
                        </p>
                        <dl className="mt-2 space-y-1 text-xs">
                            <div className="flex justify-between">
                                <dt className="text-muted-foreground">Workouts</dt>
                                <dd className="font-semibold tabular-nums text-foreground">
                                    {journey.points?.workout_points ?? journey.scores?.workout ?? 0} pts
                                </dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-muted-foreground">Nutrition</dt>
                                <dd className="font-semibold tabular-nums text-foreground">
                                    {journey.points?.meal_points ?? journey.scores?.meal ?? 0} pts
                                </dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-muted-foreground">Hydration</dt>
                                <dd className="font-semibold tabular-nums text-foreground">
                                    {journey.points?.hydration_points ?? journey.scores?.hydration ?? 0} pts
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div className="rounded-2xl border border-border bg-card p-5">
                        <div className="flex items-center gap-2 text-muted-foreground">
                            <Flame className="h-4 w-4" aria-hidden="true" />
                            <span className="text-xs font-medium uppercase tracking-wide">
                                Next Badge
                            </span>
                        </div>
                        {nextBadge ? (
                            <>
                                <p className="mt-2 truncate text-sm font-semibold text-foreground">
                                    {nextBadge.name}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    {nextBadge.progress}% complete
                                </p>
                                <Progress value={nextBadge.progress} className="mt-3 h-1.5" />
                            </>
                        ) : (
                            <p className="mt-2 text-sm text-muted-foreground">
                                {journey.unlocked_count > 0
                                    ? 'All within reach unlocked.'
                                    : 'Keep training to start earning.'}
                            </p>
                        )}
                    </div>
                </section>

                <section aria-label="Current streaks" className="mb-10">
                    <h2 className="mb-3 text-lg font-semibold text-foreground">Streaks</h2>
                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        {STREAK_TYPES.map((type) => (
                            <StreakFlame
                                key={type}
                                type={type}
                                current={journey.streaks?.[`streak_${type}`] ?? 0}
                                best={journey.streaks?.[`streak_${type}_best`] ?? 0}
                            />
                        ))}
                    </div>
                </section>

                {unlocked.length > 0 && (
                    <section aria-label="Unlocked badges" className="mb-10">
                        <h2 className="mb-3 flex items-center gap-2 text-lg font-semibold text-foreground">
                            <Award className="h-5 w-5 text-primary" aria-hidden="true" />
                            Unlocked
                        </h2>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {unlocked.map((badge: AchievementBadge, i: number) => (
                                <BadgeCard key={badge.badge_id} badge={badge} index={i} />
                            ))}
                        </div>
                    </section>
                )}

                {inProgress.length > 0 && (
                    <section aria-label="Badges in progress" className="mb-10">
                        <h2 className="mb-3 text-lg font-semibold text-foreground">
                            In Progress
                        </h2>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {inProgress.map((badge: AchievementBadge, i: number) => (
                                <BadgeCard key={badge.badge_id} badge={badge} index={i} />
                            ))}
                        </div>
                    </section>
                )}

                {locked.length > 0 && (
                    <section aria-label="Locked badges">
                        <h2 className="mb-3 text-lg font-semibold text-foreground">Locked</h2>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {locked.map((badge: AchievementBadge, i: number) => (
                                <BadgeCard key={badge.badge_id} badge={badge} index={i} />
                            ))}
                        </div>
                    </section>
                )}

                {badges.length === 0 && (
                    <p className="rounded-2xl border border-dashed border-border p-10 text-center text-sm text-muted-foreground">
                        No badges have been published yet.
                    </p>
                )}
            </div>
        </AuthenticatedLayout>
    );
}

