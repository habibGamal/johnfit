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

/**
 * Props for the InBody Index page.
 */
interface InBodyPageProps {
  auth: {
    user: {
      name: string;
      email: string;
    };
  };
  /** Comprehensive InBody analysis data provided by InBodyAnalysisService.php */
  analysis: InBodyAnalysis;
}

/**
 * InBody Analysis Dashboard.
 * 
 * Displays the latest body composition metrics, historical trends,
 * and automated progress analysis (e.g., muscle gain, fat loss).
 */
export default function InBodyIndex({ auth, analysis }: InBodyPageProps) {
  const { latest, delta, bodyCompositionAnalysis, statistics, history } = analysis;
  const hasData = latest !== null;

  return (
    <AuthenticatedLayout
      header={
        <div className="flex items-center justify-between">
          <h2 className="text-xl font-semibold leading-tight text-foreground">
            InBody Analysis
          </h2>
          <QuickEntryModal />
        </div>
      }
    >
      <Head title="InBody Analysis" />

      <div className="min-h-screen bg-background py-8">
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
                  <h3 className="text-2xl font-bold text-foreground">
                    Your Current Stats
                  </h3>
                  <p className="text-muted-foreground">
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
                    iconColor="text-blue-500"
                    delay={0}
                  />
                  <MetricCard
                    title="Muscle Mass"
                    value={latest.smm}
                    unit="kg"
                    icon={Activity}
                    change={delta?.smm}
                    iconColor="text-emerald-500"
                    delay={0.1}
                  />
                  <MetricCard
                    title="Body Fat"
                    value={latest.pbf}
                    unit="%"
                    icon={Percent}
                    change={delta?.pbf}
                    invertTrend={true}
                    iconColor="text-orange-500"
                    delay={0.2}
                  />
                  <MetricCard
                    title="BMI"
                    value={latest.bmi}
                    unit=""
                    icon={Target}
                    change={delta?.bmi}
                    iconColor="text-purple-500"
                    delay={0.3}
                  />
                  <MetricCard
                    title="BMR"
                    value={latest.bmr}
                    unit="kcal"
                    icon={Flame}
                    change={delta?.bmr}
                    iconColor="text-amber-500"
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
                        iconColor="text-cyan-500"
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
                        iconColor="text-teal-500"
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
                        iconColor="text-red-500"
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
