import { ReactNode } from "react";

interface WelcomeSectionProps {
  firstName: string;
  greeting: string;
  statBadges: ReactNode;
}

export default function WelcomeSection({ firstName, greeting, statBadges }: WelcomeSectionProps) {
  return (
    <div className="mb-10 relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 shadow-sm border border-gray-100 dark:border-gray-700">
      <div className="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-primary/5 rounded-full blur-3xl"></div>
      <div className="absolute bottom-0 left-0 -mb-4 -ml-4 w-24 h-24 bg-blue-500/5 rounded-full blur-2xl"></div>

      <div className="relative flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">
        <div>
          <h2 className="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
            {greeting}, <span className="text-primary">{firstName}</span>
          </h2>
          <p className="mt-2 text-gray-600 dark:text-gray-400 max-w-xl">
            "The only bad workout is the one that didn't happen."
          </p>
        </div>

        <div className="flex flex-wrap gap-3 sm:gap-4">
          {statBadges}
        </div>
      </div>
    </div>
  );
}
