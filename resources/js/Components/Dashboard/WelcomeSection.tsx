import { ReactNode } from "react";
import { motion } from "framer-motion";

interface WelcomeSectionProps {
  firstName: string;
  greeting: string;
  statBadges: ReactNode;
}

export default function WelcomeSection({ firstName, greeting, statBadges }: WelcomeSectionProps) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5, ease: "easeOut" }}
      className="mb-10 relative overflow-hidden rounded-2xl bg-card/80 p-6 shadow-sm border border-border"
    >
      <div className="relative flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">
        <motion.div
          initial={{ opacity: 0, x: -20 }}
          animate={{ opacity: 1, x: 0 }}
          transition={{ duration: 0.5, delay: 0.1 }}
        >
          <h2 className="text-3xl font-bold text-foreground tracking-tight">
            {greeting}, <span className="text-primary">{firstName}</span>
          </h2>
          <p className="mt-2 text-muted-foreground max-w-xl italic">
            "The only bad workout is the one that didn't happen."
          </p>
        </motion.div>

        <motion.div
          initial={{ opacity: 0, x: 20 }}
          animate={{ opacity: 1, x: 0 }}
          transition={{ duration: 0.5, delay: 0.2 }}
          className="flex flex-wrap gap-3 sm:gap-4"
        >
          {statBadges}
        </motion.div>
      </div>
    </motion.div>
  );
}
