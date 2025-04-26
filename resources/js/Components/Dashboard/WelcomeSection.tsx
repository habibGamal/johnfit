import { ReactNode } from "react";

interface WelcomeSectionProps {
  firstName: string;
  greeting: string;
  statBadges: ReactNode;
}

export default function WelcomeSection({ firstName, greeting, statBadges }: WelcomeSectionProps) {
  return (
    <div className="mb-8">
      <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h2 className="text-3xl font-bold text-gray-900">{greeting}, {firstName}</h2>
          <p className="mt-1 text-gray-600">Here's your fitness progress at a glance</p>
        </div>

        <div className="flex flex-wrap gap-2 sm:gap-4">
          {statBadges}
        </div>
      </div>
    </div>
  );
}
