import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { XCircle, RefreshCw } from 'lucide-react';
import { Button } from '@/Components/ui/button';

interface FailedProps {
    message: string;
}

export default function Failed({ message }: FailedProps) {
    return (
        <AuthenticatedLayout>
            <Head title="Payment Failed" />

            <div className="min-h-screen bg-background flex items-center justify-center py-12 px-4">
                <motion.div
                    initial={{ opacity: 0, scale: 0.95 }}
                    animate={{ opacity: 1, scale: 1 }}
                    transition={{ duration: 0.5 }}
                    className="max-w-md w-full text-center"
                >
                    <div className="flex justify-center mb-6">
                        <div className="w-24 h-24 rounded-full bg-red-500/15 flex items-center justify-center">
                            <XCircle className="w-14 h-14 text-red-400" />
                        </div>
                    </div>

                    <h1 className="text-3xl font-black text-foreground mb-3">Payment Failed</h1>
                    <p className="text-muted-foreground mb-10">{message}</p>

                    <Link href={route('packages.index')}>
                        <Button className="w-full bg-primary hover:bg-primary/90 text-black font-bold py-6 text-base gap-2">
                            <RefreshCw className="w-5 h-5" /> Try Again
                        </Button>
                    </Link>
                </motion.div>
            </div>
        </AuthenticatedLayout>
    );
}
