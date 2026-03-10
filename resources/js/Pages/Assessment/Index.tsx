import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import { Assessment, AssessmentOption, AssessmentType } from '@/types';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { cn } from '@/lib/utils';
import {
    ChevronRight,
    ChevronLeft,
    CheckCircle,
    Dumbbell,
    Loader2,
} from 'lucide-react';
import ApplicationLogo from '@/Components/ApplicationLogo';

interface AssessmentIndexProps {
    assessments: Assessment[];
}

const variants = {
    enter: (direction: number) => ({
        x: direction > 0 ? 80 : -80,
        opacity: 0,
    }),
    center: {
        x: 0,
        opacity: 1,
    },
    exit: (direction: number) => ({
        x: direction < 0 ? 80 : -80,
        opacity: 0,
    }),
};

export default function AssessmentIndex({ assessments }: AssessmentIndexProps) {
    const [currentStep, setCurrentStep] = useState(0);
    const [direction, setDirection] = useState(1);
    const [answers, setAnswers] = useState<Record<number, string | string[]>>({});
    const [submitting, setSubmitting] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});

    const total = assessments.length;
    const current = assessments[currentStep];
    const progress = ((currentStep) / total) * 100;

    const currentAnswer = answers[current?.id];

    const isAnswered = (): boolean => {
        if (!current) return false;
        if (current.type === 'text') {
            return typeof currentAnswer === 'string' && currentAnswer.trim().length > 0;
        }
        if (current.type === 'multiple_select') {
            return Array.isArray(currentAnswer) && currentAnswer.length > 0;
        }
        return typeof currentAnswer === 'string' && currentAnswer.length > 0;
    };

    const handleSelectOption = (label: string) => {
        if (current.type === 'multiple_select') {
            const prev = Array.isArray(currentAnswer) ? currentAnswer : [];
            const updated = prev.includes(label)
                ? prev.filter((v) => v !== label)
                : [...prev, label];
            setAnswers((a) => ({ ...a, [current.id]: updated }));
        } else {
            setAnswers((a) => ({ ...a, [current.id]: label }));
        }
        setErrors((e) => ({ ...e, [`answers.${current.id}`]: '' }));
    };

    const handleTextChange = (value: string) => {
        setAnswers((a) => ({ ...a, [current.id]: value }));
        setErrors((e) => ({ ...e, [`answers.${current.id}`]: '' }));
    };

    const handleNext = () => {
        if (!isAnswered()) {
            setErrors((e) => ({
                ...e,
                [`answers.${current.id}`]: 'Please answer this question before continuing.',
            }));
            return;
        }
        if (currentStep < total - 1) {
            setDirection(1);
            setCurrentStep((s) => s + 1);
        }
    };

    const handleBack = () => {
        if (currentStep > 0) {
            setDirection(-1);
            setCurrentStep((s) => s - 1);
        }
    };

    const handleSubmit = () => {
        if (!isAnswered()) {
            setErrors((e) => ({
                ...e,
                [`answers.${current.id}`]: 'Please answer this question before submitting.',
            }));
            return;
        }

        setSubmitting(true);

        const payload: Record<string, string | string[]> = {};
        for (const [id, answer] of Object.entries(answers)) {
            payload[id] = answer;
        }

        router.post(
            route('assessment.store'),
            { answers: payload },
            {
                onError: (err) => {
                    setErrors(err);
                    setSubmitting(false);
                },
                onFinish: () => setSubmitting(false),
            }
        );
    };

    return (
        <div className="min-h-screen bg-background flex flex-col">
            <Head title="Initial Assessment" />

            {/* Header */}
            <div className="border-b border-border bg-card px-4 py-4 flex items-center justify-between">
                <ApplicationLogo className="h-8 w-auto fill-current text-primary" />
                <div className="text-sm text-muted-foreground font-medium">
                    <span className="text-foreground font-bold">{currentStep + 1}</span>
                    <span className="mx-1">/</span>
                    <span>{total}</span>
                </div>
            </div>

            {/* Progress Bar */}
            <div className="h-1 bg-muted">
                <motion.div
                    className="h-full bg-primary"
                    animate={{ width: `${progress}%` }}
                    transition={{ duration: 0.4, ease: 'easeInOut' }}
                />
            </div>

            {/* Question Area */}
            <div className="flex-1 flex flex-col items-center justify-center px-4 py-12">
                <div className="w-full max-w-lg">
                    <AnimatePresence mode="wait" custom={direction}>
                        <motion.div
                            key={currentStep}
                            custom={direction}
                            variants={variants}
                            initial="enter"
                            animate="center"
                            exit="exit"
                            transition={{ duration: 0.3, ease: 'easeInOut' }}
                        >
                            {/* Question Image */}
                            {current?.image && (
                                <div className="flex justify-center mb-6">
                                    <img
                                        src={current.image}
                                        alt="Question illustration"
                                        className="h-40 w-40 object-contain rounded-2xl"
                                    />
                                </div>
                            )}

                            {/* Question Text */}
                            <h2 className="text-2xl font-black uppercase tracking-wider text-foreground text-center mb-2">
                                {current?.question}
                            </h2>

                            {current?.type === 'multiple_select' && (
                                <p className="text-sm text-muted-foreground text-center mb-6">
                                    Select all that apply
                                </p>
                            )}
                            {current?.type !== 'multiple_select' && <div className="mb-6" />}

                            {/* Options */}
                            {(current?.type === 'select' || current?.type === 'multiple_select') && (
                                <div className="flex flex-col gap-3">
                                    {current.options?.map((option) => {
                                        const isSelected =
                                            current.type === 'multiple_select'
                                                ? Array.isArray(currentAnswer) && currentAnswer.includes(option.label)
                                                : currentAnswer === option.label;

                                        return (
                                            <button
                                                key={option.label}
                                                onClick={() => handleSelectOption(option.label)}
                                                className={cn(
                                                    'w-full flex items-center justify-between rounded-xl border px-5 py-4 text-left transition-all duration-200',
                                                    'hover:border-primary/60 hover:bg-primary/5',
                                                    isSelected
                                                        ? 'border-primary bg-primary/10 text-foreground shadow-sm'
                                                        : 'border-border bg-card text-muted-foreground'
                                                )}
                                            >
                                                <span className={cn('font-semibold text-sm', isSelected && 'text-primary')}>
                                                    {option.label}
                                                </span>
                                                {isSelected && (
                                                    <CheckCircle className="h-5 w-5 text-primary shrink-0" />
                                                )}
                                            </button>
                                        );
                                    })}
                                </div>
                            )}

                            {/* Text Input */}
                            {current?.type === 'text' && (
                                <Input
                                    type="text"
                                    placeholder="Type your answer..."
                                    value={typeof currentAnswer === 'string' ? currentAnswer : ''}
                                    onChange={(e) => handleTextChange(e.target.value)}
                                    className="text-center text-lg h-14 rounded-xl border-border bg-card"
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter') {
                                            currentStep < total - 1 ? handleNext() : handleSubmit();
                                        }
                                    }}
                                    autoFocus
                                />
                            )}

                            {/* Error */}
                            {errors[`answers.${current?.id}`] && (
                                <p className="mt-3 text-sm text-destructive text-center">
                                    {errors[`answers.${current?.id}`]}
                                </p>
                            )}
                        </motion.div>
                    </AnimatePresence>
                </div>
            </div>

            {/* Navigation */}
            <div className="border-t border-border bg-card px-4 py-5 safe-area-bottom">
                <div className="mx-auto max-w-lg flex items-center gap-3">
                    {currentStep > 0 && (
                        <Button
                            variant="outline"
                            onClick={handleBack}
                            className="flex-shrink-0 h-14 px-5 rounded-xl border-border"
                        >
                            <ChevronLeft className="h-5 w-5" />
                        </Button>
                    )}

                    {currentStep < total - 1 ? (
                        <Button
                            onClick={handleNext}
                            className="flex-1 h-14 rounded-xl font-bold text-base uppercase tracking-wide"
                        >
                            Continue
                            <ChevronRight className="h-5 w-5 ml-1" />
                        </Button>
                    ) : (
                        <Button
                            onClick={handleSubmit}
                            disabled={submitting}
                            className="flex-1 h-14 rounded-xl font-bold text-base uppercase tracking-wide bg-primary"
                        >
                            {submitting ? (
                                <>
                                    <Loader2 className="h-5 w-5 mr-2 animate-spin" />
                                    Saving...
                                </>
                            ) : (
                                <>
                                    <Dumbbell className="h-5 w-5 mr-2" />
                                    Start My Journey
                                </>
                            )}
                        </Button>
                    )}
                </div>
            </div>
        </div>
    );
}
