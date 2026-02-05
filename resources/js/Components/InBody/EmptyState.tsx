import { motion } from 'framer-motion';
import { Activity, Dumbbell } from 'lucide-react';

interface EmptyStateProps {
  onAddClick?: () => void;
}

export default function EmptyState({ onAddClick }: EmptyStateProps) {
  return (
    <motion.div
      initial={{ opacity: 0, scale: 0.95 }}
      animate={{ opacity: 1, scale: 1 }}
      transition={{ duration: 0.5 }}
      className="flex flex-col items-center justify-center py-20 px-4"
    >
      {/* Animated icon */}
      <motion.div
        className="relative mb-8"
        animate={{
          y: [0, -10, 0],
        }}
        transition={{
          duration: 3,
          repeat: Infinity,
          ease: 'easeInOut',
        }}
      >
        <div className="relative">
          <div className="absolute inset-0 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl blur-2xl opacity-30" />
          <div className="relative p-8 bg-gradient-to-br from-indigo-500/10 to-purple-500/10 rounded-3xl border border-indigo-500/20">
            <Activity className="h-16 w-16 text-indigo-500" />
          </div>
        </div>

        {/* Floating elements */}
        <motion.div
          className="absolute -top-2 -right-2 p-2 bg-emerald-500/20 rounded-xl"
          animate={{
            rotate: [0, 10, -10, 0],
            scale: [1, 1.1, 1],
          }}
          transition={{
            duration: 4,
            repeat: Infinity,
            ease: 'easeInOut',
          }}
        >
          <Dumbbell className="h-6 w-6 text-emerald-500" />
        </motion.div>
      </motion.div>

      <h3 className="text-2xl font-bold text-gray-900 dark:text-white mb-3 text-center">
        Start Your Body Composition Journey
      </h3>

      <p className="text-gray-500 dark:text-gray-400 text-center max-w-md mb-8 leading-relaxed">
        Track your InBody measurements to monitor muscle gain, fat loss, and overall body composition changes.
        Get personalized insights and recommendations based on your progress.
      </p>

      {/* Features list */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8 max-w-2xl">
        {[
          { icon: '📊', text: 'Track Progress' },
          { icon: '💪', text: 'Muscle Analysis' },
          { icon: '🎯', text: 'Smart Insights' },
        ].map((feature, index) => (
          <motion.div
            key={index}
            className="flex items-center gap-2 px-4 py-3 bg-white/50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.2 + index * 0.1 }}
          >
            <span className="text-2xl">{feature.icon}</span>
            <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
              {feature.text}
            </span>
          </motion.div>
        ))}
      </div>

      <motion.p
        className="text-sm text-gray-400 dark:text-gray-500"
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ delay: 0.6 }}
      >
        Click the "Log InBody" button above to record your first measurement
      </motion.p>
    </motion.div>
  );
}
