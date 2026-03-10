import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { CheckCircle, Calendar, ArrowRight } from 'lucide-react';
import { Subscription } from '@/types';
import { Button } from '@/Components/ui/button';

interface SuccessProps {
    subscription: Subscription;
}

export default function Success({ subscription }: SuccessProps) {
    return (
        <AuthenticatedLayout>
            <Head title="Subscription Confirmed" />

            <div className="min-h-screen bg-background flex items-center justify-center py-12 px-4">
                <motion.div
                    initial={{ opacity: 0, scale: 0.95 }}
                    animate={{ opacity: 1, scale: 1 }}
                    transition={{ duration: 0.5 }}
                    className="max-w-md w-full text-center"
                >
                    <div className="flex justify-center mb-6">
                        <div className="w-24 h-24 rounded-full bg-emerald-500/15 flex items-center justify-center">
                            <CheckCircle className="w-14 h-14 text-emerald-400" />
                        </div>
                    </div>

                    <h1 className="text-3xl font-black text-foreground mb-3">
                        You're All Set!
                    </h1>
                    <p className="text-muted-foreground mb-8">
                        Your <span className="text-primary font-bold">{subscription.plan?.name}</span> subscription is now active.
                    </p>

                    {subscription.end_date && (
                        <div className="flex items-center gap-3 justify-center mb-10 rounded-xl bg-card/60 border border-border/50 p-4">
                            <Calendar className="w-5 h-5 text-primary" />
                            <span className="text-sm text-muted-foreground">
                                Access until{' '}
                                <span className="font-semibold text-foreground">
                                    {new Date(subscription.end_date).toLocaleDateString('en-US', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                    })}
                                </span>
                            </span>
                        </div>
                    )}

                    <Link href={route('dashboard')}>
                        <Button className="w-full bg-primary hover:bg-primary/90 text-black font-bold py-6 text-base gap-2">
                            Go to Dashboard <ArrowRight className="w-5 h-5" />
                        </Button>
                    </Link>
                </motion.div>
            </div>
        </AuthenticatedLayout>
    );
}
