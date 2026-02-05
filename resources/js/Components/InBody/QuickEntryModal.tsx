import { useState } from 'react';
import { useForm } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogTrigger,
} from '@/Components/ui/dialog';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
  Plus,
  Scale,
  Activity,
  Percent,
  Heart,
  Flame,
  Droplets,
  Target,
  ChevronRight,
  ChevronLeft,
  Check,
  Loader2,
} from 'lucide-react';
import { cn } from '@/lib/utils';

interface QuickEntryModalProps {
  trigger?: React.ReactNode;
}

const steps = [
  {
    id: 'basic',
    title: 'Basic Measurements',
    description: 'Enter your core InBody metrics',
    fields: ['weight', 'bmi', 'bmr'],
  },
  {
    id: 'composition',
    title: 'Body Composition',
    description: 'Muscle mass and body fat details',
    fields: ['smm', 'pbf', 'lean_body_mass'],
  },
  {
    id: 'advanced',
    title: 'Advanced Metrics',
    description: 'Optional detailed measurements',
    fields: ['body_water', 'visceral_fat', 'waist_hip_ratio'],
  },
  {
    id: 'details',
    title: 'Additional Details',
    description: 'Date and notes',
    fields: ['measured_at', 'notes'],
  },
];

const fieldConfig: Record<string, {
  label: string;
  icon: typeof Scale;
  unit: string;
  placeholder: string;
  required: boolean;
  type: string;
  min?: number;
  max?: number;
  step?: number;
}> = {
  weight: {
    label: 'Weight',
    icon: Scale,
    unit: 'kg',
    placeholder: '70.5',
    required: true,
    type: 'number',
    min: 20,
    max: 350,
    step: 0.1,
  },
  smm: {
    label: 'Skeletal Muscle Mass',
    icon: Activity,
    unit: 'kg',
    placeholder: '32.5',
    required: true,
    type: 'number',
    min: 5,
    max: 80,
    step: 0.1,
  },
  pbf: {
    label: 'Body Fat Percentage',
    icon: Percent,
    unit: '%',
    placeholder: '18.5',
    required: true,
    type: 'number',
    min: 3,
    max: 60,
    step: 0.1,
  },
  bmi: {
    label: 'BMI',
    icon: Target,
    unit: '',
    placeholder: '22.5',
    required: true,
    type: 'number',
    min: 10,
    max: 60,
    step: 0.1,
  },
  bmr: {
    label: 'Basal Metabolic Rate',
    icon: Flame,
    unit: 'kcal',
    placeholder: '1650',
    required: true,
    type: 'number',
    min: 800,
    max: 4000,
    step: 1,
  },
  body_water: {
    label: 'Body Water',
    icon: Droplets,
    unit: 'L',
    placeholder: '42.5',
    required: false,
    type: 'number',
    min: 20,
    max: 70,
    step: 0.1,
  },
  lean_body_mass: {
    label: 'Lean Body Mass',
    icon: Activity,
    unit: 'kg',
    placeholder: '55.0',
    required: false,
    type: 'number',
    min: 25,
    max: 120,
    step: 0.1,
  },
  visceral_fat: {
    label: 'Visceral Fat Level',
    icon: Heart,
    unit: '',
    placeholder: '8',
    required: false,
    type: 'number',
    min: 1,
    max: 60,
    step: 0.1,
  },
  waist_hip_ratio: {
    label: 'Waist-Hip Ratio',
    icon: Target,
    unit: '',
    placeholder: '0.85',
    required: false,
    type: 'number',
    min: 0.5,
    max: 1.5,
    step: 0.001,
  },
  measured_at: {
    label: 'Measurement Date',
    icon: Scale,
    unit: '',
    placeholder: '',
    required: true,
    type: 'date',
  },
  notes: {
    label: 'Notes',
    icon: Scale,
    unit: '',
    placeholder: 'Any notes about this measurement...',
    required: false,
    type: 'textarea',
  },
};

export default function QuickEntryModal({ trigger }: QuickEntryModalProps) {
  const [open, setOpen] = useState(false);
  const [currentStep, setCurrentStep] = useState(0);

  const { data, setData, post, processing, errors, reset } = useForm({
    weight: '',
    smm: '',
    pbf: '',
    bmi: '',
    bmr: '',
    body_water: '',
    lean_body_mass: '',
    visceral_fat: '',
    waist_hip_ratio: '',
    measured_at: new Date().toISOString().split('T')[0],
    notes: '',
  });

  const currentStepData = steps[currentStep];
  const isLastStep = currentStep === steps.length - 1;
  const isFirstStep = currentStep === 0;

  const handleNext = () => {
    if (!isLastStep) {
      setCurrentStep(prev => prev + 1);
    }
  };

  const handleBack = () => {
    if (!isFirstStep) {
      setCurrentStep(prev => prev - 1);
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('inbody.store'), {
      onSuccess: () => {
        setOpen(false);
        reset();
        setCurrentStep(0);
      },
    });
  };

  const canProceed = () => {
    const requiredFields = currentStepData.fields.filter(
      f => fieldConfig[f]?.required
    );
    return requiredFields.every(f => data[f as keyof typeof data]);
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        {trigger || (
          <Button className="gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-lg shadow-indigo-500/25">
            <Plus className="h-4 w-4" />
            Log InBody
          </Button>
        )}
      </DialogTrigger>
      <DialogContent className="sm:max-w-lg bg-gradient-to-br from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 border-0 shadow-2xl">
        {/* Glassmorphism overlay */}
        <div className="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-purple-500/5 rounded-lg pointer-events-none" />

        <DialogHeader className="relative">
          <DialogTitle className="text-xl font-bold text-gray-900 dark:text-white">
            Log InBody Measurement
          </DialogTitle>
          <DialogDescription className="text-gray-500 dark:text-gray-400">
            Track your body composition progress
          </DialogDescription>
        </DialogHeader>

        {/* Step Indicator */}
        <div className="relative flex items-center justify-between px-2 py-4">
          {steps.map((step, index) => (
            <div key={step.id} className="flex items-center">
              <motion.div
                className={cn(
                  'flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium transition-all duration-300',
                  index < currentStep
                    ? 'bg-indigo-600 text-white'
                    : index === currentStep
                    ? 'bg-indigo-600 text-white ring-4 ring-indigo-600/20'
                    : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400'
                )}
                initial={false}
                animate={{
                  scale: index === currentStep ? 1.1 : 1,
                }}
                transition={{ type: 'spring', stiffness: 300 }}
              >
                {index < currentStep ? (
                  <Check className="h-4 w-4" />
                ) : (
                  index + 1
                )}
              </motion.div>
              {index < steps.length - 1 && (
                <div className={cn(
                  'w-12 h-0.5 mx-1',
                  index < currentStep ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700'
                )} />
              )}
            </div>
          ))}
        </div>

        {/* Step Content */}
        <form onSubmit={handleSubmit}>
          <AnimatePresence mode="wait">
            <motion.div
              key={currentStep}
              initial={{ opacity: 0, x: 20 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: -20 }}
              transition={{ duration: 0.2 }}
              className="space-y-4 py-4"
            >
              <div className="mb-4">
                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                  {currentStepData.title}
                </h3>
                <p className="text-sm text-gray-500 dark:text-gray-400">
                  {currentStepData.description}
                </p>
              </div>

              <div className="space-y-4">
                {currentStepData.fields.map(fieldName => {
                  const config = fieldConfig[fieldName];
                  if (!config) return null;

                  const Icon = config.icon;
                  const error = errors[fieldName as keyof typeof errors];

                  if (config.type === 'textarea') {
                    return (
                      <div key={fieldName} className="space-y-2">
                        <Label htmlFor={fieldName} className="text-gray-700 dark:text-gray-300">
                          {config.label}
                          {!config.required && <span className="text-gray-400 ml-1">(optional)</span>}
                        </Label>
                        <textarea
                          id={fieldName}
                          value={data[fieldName as keyof typeof data]}
                          onChange={e => setData(fieldName as keyof typeof data, e.target.value)}
                          placeholder={config.placeholder}
                          rows={3}
                          className={cn(
                            'w-full px-4 py-3 rounded-xl border bg-white/50 dark:bg-gray-800/50',
                            'focus:ring-2 focus:ring-indigo-500 focus:border-transparent',
                            'placeholder:text-gray-400 text-gray-900 dark:text-white',
                            'backdrop-blur-sm transition-all duration-200',
                            error ? 'border-red-500' : 'border-gray-200 dark:border-gray-700'
                          )}
                        />
                        {error && <p className="text-sm text-red-500">{error}</p>}
                      </div>
                    );
                  }

                  return (
                    <div key={fieldName} className="space-y-2">
                      <Label htmlFor={fieldName} className="text-gray-700 dark:text-gray-300">
                        {config.label}
                        {config.required && <span className="text-red-500 ml-1">*</span>}
                        {!config.required && <span className="text-gray-400 ml-1">(optional)</span>}
                      </Label>
                      <div className="relative">
                        {config.type === 'number' && (
                          <div className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <Icon className="h-5 w-5" />
                          </div>
                        )}
                        <Input
                          id={fieldName}
                          type={config.type}
                          value={data[fieldName as keyof typeof data]}
                          onChange={e => setData(fieldName as keyof typeof data, e.target.value)}
                          placeholder={config.placeholder}
                          min={config.min}
                          max={config.max}
                          step={config.step}
                          className={cn(
                            'w-full rounded-xl border bg-white/50 dark:bg-gray-800/50',
                            'focus:ring-2 focus:ring-indigo-500 focus:border-transparent',
                            'placeholder:text-gray-400 text-gray-900 dark:text-white',
                            'backdrop-blur-sm transition-all duration-200',
                            config.type === 'number' ? 'pl-11' : '',
                            config.unit ? 'pr-14' : '',
                            error ? 'border-red-500' : 'border-gray-200 dark:border-gray-700'
                          )}
                        />
                        {config.unit && (
                          <span className="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-gray-400">
                            {config.unit}
                          </span>
                        )}
                      </div>
                      {error && <p className="text-sm text-red-500">{error}</p>}
                    </div>
                  );
                })}
              </div>
            </motion.div>
          </AnimatePresence>

          {/* Navigation Buttons */}
          <div className="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
            <Button
              type="button"
              variant="ghost"
              onClick={handleBack}
              disabled={isFirstStep}
              className={cn(
                'gap-1',
                isFirstStep && 'opacity-0 pointer-events-none'
              )}
            >
              <ChevronLeft className="h-4 w-4" />
              Back
            </Button>

            {isLastStep ? (
              <Button
                type="submit"
                disabled={processing || !canProceed()}
                className="gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700"
              >
                {processing ? (
                  <>
                    <Loader2 className="h-4 w-4 animate-spin" />
                    Saving...
                  </>
                ) : (
                  <>
                    <Check className="h-4 w-4" />
                    Save Measurement
                  </>
                )}
              </Button>
            ) : (
              <Button
                type="button"
                onClick={handleNext}
                disabled={!canProceed()}
                className="gap-1 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700"
              >
                Next
                <ChevronRight className="h-4 w-4" />
              </Button>
            )}
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}
