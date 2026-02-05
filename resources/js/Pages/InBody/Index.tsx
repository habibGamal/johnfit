import { motion } from 'framer-motion';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import {
  Scale,
  Activity,
  Percent,
  Target,
  Flame,
  Droplets,
} from 'lucide-react';
import { InBodyAnalysis } from '@/types';

// Import InBody components
import MetricCard from '@/Components/InBody/MetricCard';
import BodyCompositionChart from '@/Components/InBody/BodyCompositionChart';
import AnalysisCard from '@/Components/InBody/AnalysisCard';
import StatisticsCard from '@/Components/InBody/StatisticsCard';
import HistoryTimeline from '@/Components/InBody/HistoryTimeline';
import QuickEntryModal from '@/Components/InBody/QuickEntryModal';
import EmptyState from '@/Components/InBody/EmptyState';

interface InBodyPageProps {
  auth: {
    user: {
      name: string;
      email: string;
    };
  };
  analysis: InBodyAnalysis;
}

export default function InBodyIndex({ auth, analysis }: InBodyPageProps) {
  const { latest, delta, bodyCompositionAnalysis, statistics, history } = analysis;
  const hasData = latest !== null;

  return (
    <AuthenticatedLayout
      header={
        <div className="flex items-center justify-between">
          <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            InBody Analysis
          </h2>
          <QuickEntryModal />
        </div>
      }
    >
      <Head title="InBody Analysis" />

      <div className="min-h-screen bg-gradient-to-br from-gray-50 via-indigo-50/30 to-purple-50/20 dark:from-gray-900 dark:via-gray-900 dark:to-gray-800 py-8">
        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          {!hasData ? (
            <EmptyState />
          ) : (
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ duration: 0.3 }}
              className="space-y-8"
            >
              {/* Hero Section - Current Metrics */}
              <section>
                <motion.div
                  className="mb-6"
                  initial={{ opacity: 0, y: -20 }}
                  animate={{ opacity: 1, y: 0 }}
                >
                  <h3 className="text-2xl font-bold text-gray-900 dark:text-white">
                    Your Current Stats
                  </h3>
                  <p className="text-gray-500 dark:text-gray-400">
                    Last measured: {latest.measured_at_formatted}
                  </p>
                </motion.div>

                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                  <MetricCard
                    title="Weight"
                    value={latest.weight}
                    unit="kg"
                    icon={Scale}
                    change={delta?.weight}
                    iconColor="text-blue-600"
                    bgGradient="from-blue-500/10 to-blue-500/5"
                    delay={0}
                  />
                  <MetricCard
                    title="Muscle Mass"
                    value={latest.smm}
                    unit="kg"
                    icon={Activity}
                    change={delta?.smm}
                    iconColor="text-emerald-600"
                    bgGradient="from-emerald-500/10 to-emerald-500/5"
                    delay={0.1}
                  />
                  <MetricCard
                    title="Body Fat"
                    value={latest.pbf}
                    unit="%"
                    icon={Percent}
                    change={delta?.pbf}
                    invertTrend={true}
                    iconColor="text-orange-600"
                    bgGradient="from-orange-500/10 to-orange-500/5"
                    delay={0.2}
                  />
                  <MetricCard
                    title="BMI"
                    value={latest.bmi}
                    unit=""
                    icon={Target}
                    change={delta?.bmi}
                    iconColor="text-purple-600"
                    bgGradient="from-purple-500/10 to-purple-500/5"
                    delay={0.3}
                  />
                  <MetricCard
                    title="BMR"
                    value={latest.bmr}
                    unit="kcal"
                    icon={Flame}
                    change={delta?.bmr}
                    iconColor="text-amber-600"
                    bgGradient="from-amber-500/10 to-amber-500/5"
                    delay={0.4}
                  />
                </div>

                {/* Optional extended metrics */}
                {(latest.body_water || latest.lean_body_mass || latest.visceral_fat) && (
                  <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                    {latest.body_water && (
                      <MetricCard
                        title="Body Water"
                        value={latest.body_water}
                        unit="L"
                        icon={Droplets}
                        change={delta?.body_water || undefined}
                        iconColor="text-cyan-600"
                        bgGradient="from-cyan-500/10 to-cyan-500/5"
                        delay={0.5}
                      />
                    )}
                    {latest.lean_body_mass && (
                      <MetricCard
                        title="Lean Body Mass"
                        value={latest.lean_body_mass}
                        unit="kg"
                        icon={Activity}
                        change={delta?.lean_body_mass || undefined}
                        iconColor="text-teal-600"
                        bgGradient="from-teal-500/10 to-teal-500/5"
                        delay={0.6}
                      />
                    )}
                    {latest.visceral_fat && (
                      <MetricCard
                        title="Visceral Fat"
                        value={latest.visceral_fat}
                        unit=""
                        icon={Target}
                        change={delta?.visceral_fat || undefined}
                        invertTrend={true}
                        iconColor="text-red-600"
                        bgGradient="from-red-500/10 to-red-500/5"
                        delay={0.7}
                      />
                    )}
                  </div>
                )}
              </section>

              {/* Body Composition Chart */}
              <section>
                <BodyCompositionChart data={history} />
              </section>

              {/* Analysis & Statistics Grid */}
              <section className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {bodyCompositionAnalysis && (
                  <AnalysisCard analysis={bodyCompositionAnalysis} />
                )}
                {statistics && <StatisticsCard statistics={statistics} />}
              </section>

              {/* History Timeline */}
              <section>
                <HistoryTimeline history={history} />
              </section>
            </motion.div>
          )}
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
