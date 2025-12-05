import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/Components/ui/card";
import { Award, Lock, CheckCircle2, Trophy, Flame, Target, Zap, Dumbbell } from "lucide-react";
import { Achievement } from "@/types";
import { Progress } from "@/Components/ui/progress";

interface AchievementsCardProps {
  achievements: Achievement[];
}

const IconMap: Record<string, any> = {
  Award,
  Trophy,
  Flame,
  Target,
  Zap,
  Dumbbell
};

export default function AchievementsCard({ achievements }: AchievementsCardProps) {
  // If no achievements explicitly passed yet, use fallback or empty state (should handle safe prop access)
  const displayAchievements = achievements || [];

  return (
    <Card className="h-full flex flex-col shadow-sm border-gray-100 dark:border-gray-700 dark:bg-gray-800/50 backdrop-blur-sm">
      <CardHeader>
        <CardTitle className="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
          <div className="p-2 bg-amber-500/10 rounded-lg">
            <Award className="h-5 w-5 text-amber-500" />
          </div>
          Achievements
        </CardTitle>
        <CardDescription className="text-gray-500 dark:text-gray-400">Unlock badges as you progress</CardDescription>
      </CardHeader>
      <CardContent className="flex-1 overflow-y-auto pr-1 custom-scrollbar" style={{ maxHeight: '300px' }}>
        <div className="space-y-4">
          {displayAchievements.length > 0 ? (
            displayAchievements.map((achievement) => {
              const IconComponent = IconMap[achievement.icon] || Award;
              const isUnlocked = achievement.unlocked;

              let tierColor = "";
              if (achievement.tier === 'gold') tierColor = "text-yellow-500 bg-yellow-500/10 border-yellow-200 dark:border-yellow-700";
              else if (achievement.tier === 'silver') tierColor = "text-slate-400 bg-slate-400/10 border-slate-200 dark:border-slate-700";
              else tierColor = "text-amber-700 bg-amber-700/10 border-amber-200 dark:border-amber-900"; // Bronze

              return (
                <div
                  key={achievement.id}
                  className={`flex items-start gap-3 p-3 rounded-xl border transition-all ${isUnlocked
                      ? "bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700 shadow-sm"
                      : "bg-gray-50 dark:bg-gray-800/50 border-transparent opacity-80"
                    }`}
                >
                  <div className={`p-2 rounded-full shrink-0 ${isUnlocked ? tierColor : "bg-gray-200 dark:bg-gray-700 text-gray-400"}`}>
                    {isUnlocked ? <IconComponent className="h-5 w-5" /> : <Lock className="h-5 w-5" />}
                  </div>

                  <div className="flex-1 min-w-0">
                    <div className="flex items-center justify-between mb-1">
                      <h4 className={`text-sm font-semibold truncate ${isUnlocked ? "text-gray-900 dark:text-gray-100" : "text-gray-500 dark:text-gray-500"}`}>
                        {achievement.title}
                      </h4>
                      {isUnlocked && <CheckCircle2 className="h-4 w-4 text-emerald-500" />}
                    </div>

                    <p className="text-xs text-gray-500 dark:text-gray-400 mb-2 line-clamp-2">
                      {achievement.description}
                    </p>

                    <div className="space-y-1">
                      <div className="flex justify-between text-[10px] text-gray-400">
                        <span>{Math.round(achievement.progress)}%</span>
                      </div>
                      <Progress value={achievement.progress} className="h-1.5" />
                    </div>
                  </div>
                </div>
              );
            })
          ) : (
            <div className="text-center py-8 text-gray-500 dark:text-gray-400">
              <Trophy className="h-10 w-10 mx-auto mb-2 text-gray-300 dark:text-gray-600" />
              <p>Start your journey to earn badges!</p>
            </div>
          )}
        </div>
      </CardContent>
    </Card>
  );
}
