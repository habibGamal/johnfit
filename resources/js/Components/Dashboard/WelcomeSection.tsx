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
      className="mb-10 relative overflow-hidden rounded-2xl bg-card/80 backdrop-blur-xl p-6 shadow-lg border border-primary/10 shadow-[0_0_30px_-15px_rgba(252,211,77,0.2)]"
    >
      {/* Animated gradient blobs */}
      <motion.div
        animate={{
          scale: [1, 1.2, 1],
          opacity: [0.05, 0.08, 0.05],
        }}
        transition={{
          duration: 8,
          repeat: Infinity,
          ease: "easeInOut",
        }}
        className="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-primary rounded-full blur-3xl"
      />
      <motion.div
        animate={{
          scale: [1, 1.3, 1],
          opacity: [0.1, 0.15, 0.1],
        }}
        transition={{
          duration: 10,
          repeat: Infinity,
          ease: "easeInOut",
          delay: 1,
        }}
        className="absolute bottom-0 left-0 -mb-4 -ml-4 w-24 h-24 bg-primary rounded-full blur-2xl"
      />

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
