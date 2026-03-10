import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { Check, Crown, Zap, Star, Loader2 } from 'lucide-react';
import { SubscriptionPlan, Subscription } from '@/types';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardFooter, CardHeader } from '@/Components/ui/card';
import { cn } from '@/lib/utils';
import axios from 'axios';

interface PackagesProps {
    plans: SubscriptionPlan[];
    activeSubscription: Subscription | null;
}

const tagColors: Record<string, string> = {
    popular: 'bg-primary text-black',
    best: 'bg-emerald-500 text-white',
};

function getTagColor(tag: string | null): string {
    if (!tag) return 'bg-primary text-black';
    const key = tag.toLowerCase();
    return tagColors[key] ?? 'bg-primary text-black';
}

export default function Packages({ plans, activeSubscription }: PackagesProps) {
    const [loading, setLoading] = useState<number | null>(null);

    const handleSubscribe = async (planId: number) => {
        setLoading(planId);
        try {
            // const response = await fetch(route('subscriptions.initiate'), {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //         'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
            //         'Accept': 'application/json',
            //     },
            //     body: JSON.stringify({ plan_id: planId }),
            // });
            const response = await axios.post(route('subscriptions.initiate'), { plan_id: planId });
            const data = await response.data;
            if (data.payment_url) {
                window.location.href = data.payment_url;
            }
        } finally {
            setLoading(null);
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-3">
                    <span className="bg-primary p-1.5 rounded-lg">
                        <Crown className="w-5 h-5 text-black" />
                    </span>
                    <div>
                        <h2 className="text-2xl font-black uppercase tracking-wider text-foreground">
                            Subscription Plans
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            Choose the plan that fits your goals
                        </p>
                    </div>
                </div>
            }
        >
            <Head title="Subscription Plans" />

            <div className="min-h-screen bg-background py-12 relative">
                {/* Background Glow */}
                <div className="fixed top-0 left-1/2 -translate-x-1/2 w-[600px] h-[400px] bg-primary/5 blur-[120px] pointer-events-none rounded-full" />

                <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 relative z-10">

                    {/* Active Subscription Banner */}
                    {activeSubscription && (
                        <motion.div
                            initial={{ opacity: 0, y: -10 }}
                            animate={{ opacity: 1, y: 0 }}
                            className="mb-10 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-6 flex items-center gap-4"
                        >
                            <div className="p-3 bg-emerald-500/20 rounded-full">
                                <Check className="w-6 h-6 text-emerald-400" />
                            </div>
                            <div>
                                <p className="font-bold text-emerald-400 text-lg">
                                    Active Subscription: {activeSubscription.plan?.name}
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    Expires on{' '}
                                    {activeSubscription.end_date
                                        ? new Date(activeSubscription.end_date).toLocaleDateString('en-US', {
                                              year: 'numeric',
                                              month: 'long',
                                              day: 'numeric',
                                          })
                                        : 'N/A'}
                                </p>
                            </div>
                        </motion.div>
                    )}

                    {/* Hero */}
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.5 }}
                        className="text-center mb-14"
                    >
                        <h1 className="text-4xl font-black text-foreground tracking-tight mb-4">
                            Unlock Your <span className="text-primary">Full Potential</span>
                        </h1>
                        <p className="text-muted-foreground text-lg max-w-xl mx-auto">
                            Get access to personalized workout plans, tailored meal plans, and expert coaching.
                        </p>
                    </motion.div>

                    {/* Plans Grid */}
                    {plans.length === 0 ? (
                        <div className="text-center py-20 text-muted-foreground">
                            <Crown className="mx-auto mb-4 w-16 h-16 opacity-30" />
                            <p className="text-xl font-semibold">No plans available right now.</p>
                            <p className="text-sm mt-2">Check back soon!</p>
                        </div>
                    ) : (
                        <div className={cn(
                            'grid gap-8',
                            plans.length === 1 ? 'grid-cols-1 max-w-sm mx-auto' :
                            plans.length === 2 ? 'grid-cols-1 md:grid-cols-2 max-w-3xl mx-auto' :
                            'grid-cols-1 md:grid-cols-2 lg:grid-cols-3'
                        )}>
                            {plans.map((plan, index) => (
                                <motion.div
                                    key={plan.id}
                                    initial={{ opacity: 0, y: 30 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    transition={{ duration: 0.4, delay: index * 0.1 }}
                                >
                                    <Card className={cn(
                                        'relative h-full flex flex-col border border-border/50 bg-card/60 backdrop-blur-sm transition-all duration-300 hover:border-primary/40 hover:shadow-[0_0_40px_-12px_rgba(252,211,77,0.3)] hover:-translate-y-1',
                                        plan.tag?.toLowerCase().includes('popular') && 'border-primary/50 shadow-[0_0_40px_-12px_rgba(252,211,77,0.2)]'
                                    )}>
                                        {/* Tag Badge */}
                                        {plan.tag && (
                                            <div className="absolute -top-3.5 left-1/2 -translate-x-1/2 z-10">
                                                <span className={cn('text-xs font-bold uppercase tracking-widest px-4 py-1 rounded-full', getTagColor(plan.tag))}>
                                                    {plan.tag}
                                                </span>
                                            </div>
                                        )}

                                        <CardHeader className="pt-8 pb-4 text-center">
                                            <div className="flex justify-center mb-4">
                                                <div className="p-3 bg-primary/10 rounded-2xl">
                                                    {index === 0 ? <Zap className="w-8 h-8 text-primary" /> :
                                                     index === 1 ? <Crown className="w-8 h-8 text-primary" /> :
                                                                   <Star className="w-8 h-8 text-primary" />}
                                                </div>
                                            </div>
                                            <h3 className="text-xl font-black text-foreground tracking-tight">{plan.name}</h3>
                                            <div className="mt-4">
                                                <span className="text-4xl font-black text-primary">
                                                    {Number(plan.price).toLocaleString('en-EG')}
                                                </span>
                                                <span className="text-muted-foreground ml-1 text-sm font-medium">EGP</span>
                                            </div>
                                            <p className="text-xs text-muted-foreground mt-1">
                                                {plan.duration_days} days access
                                            </p>
                                        </CardHeader>

                                        <CardContent className="flex-1 px-6 py-4">
                                            {plan.features && plan.features.length > 0 ? (
                                                <ul className="space-y-3">
                                                    {plan.features.map((item, i) => (
                                                        <li key={i} className="flex items-start gap-3 text-sm">
                                                            <div className="mt-0.5 flex-shrink-0 w-5 h-5 rounded-full bg-primary/15 flex items-center justify-center">
                                                                <Check className="w-3 h-3 text-primary" />
                                                            </div>
                                                            <span className="text-muted-foreground">{item.feature}</span>
                                                        </li>
                                                    ))}
                                                </ul>
                                            ) : (
                                                <p className="text-muted-foreground text-sm text-center">Full access included</p>
                                            )}
                                        </CardContent>

                                        <CardFooter className="px-6 pb-8 pt-4">
                                            <Button
                                                onClick={() => handleSubscribe(plan.id)}
                                                disabled={activeSubscription?.plan_id === plan.id || loading === plan.id}
                                                className={cn(
                                                    'w-full font-bold py-6 text-base transition-all',
                                                    plan.tag?.toLowerCase().includes('popular')
                                                        ? 'bg-primary hover:bg-primary/90 text-black shadow-[0_0_20px_-5px_rgba(252,211,77,0.5)]'
                                                        : 'bg-card border border-primary/40 text-primary hover:bg-primary/10'
                                                )}
                                            >
                                                {loading === plan.id ? (
                                                    <span className="flex items-center justify-center gap-2">
                                                        <Loader2 className="w-4 h-4 animate-spin" /> Redirecting...
                                                    </span>
                                                ) : activeSubscription?.plan_id === plan.id
                                                    ? 'Current Plan'
                                                    : activeSubscription
                                                    ? 'Switch to this Plan'
                                                    : 'Get Started'}
                                            </Button>
                                        </CardFooter>
                                    </Card>
                                </motion.div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
