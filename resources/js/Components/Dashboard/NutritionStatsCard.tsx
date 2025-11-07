import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { NutritionAverages } from '@/types';
import { BarChart4, Pizza } from 'lucide-react';

interface NutritionStatsCardProps {
  nutritionAverages: NutritionAverages;
}

export default function NutritionStatsCard({ nutritionAverages }: NutritionStatsCardProps) {
  // Calculate percentage of each macro based on calories (rough estimate)
  // 1g protein = 4 calories, 1g carbs = 4 calories, 1g fat = 9 calories
  const totalCalories = nutritionAverages.calories || 1; // Prevent division by zero
  const proteinCalories = nutritionAverages.protein * 4;
  const carbsCalories = nutritionAverages.carbs * 4;
  const fatCalories = nutritionAverages.fat * 9;

  const proteinPercentage = Math.round((proteinCalories / totalCalories) * 100);
  const carbsPercentage = Math.round((carbsCalories / totalCalories) * 100);
  const fatPercentage = Math.round((fatCalories / totalCalories) * 100);
  return (
    <Card className="h-full flex flex-col">
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Pizza className="h-5 w-5 text-primary" />
          Nutrition Breakdown
        </CardTitle>
        <CardDescription>Daily average consumption</CardDescription>
      </CardHeader>
      <CardContent className="flex-1 flex flex-col justify-between">
        <div className="mb-4">
          <div className="flex justify-between items-baseline mb-1">
            <span className="text-sm">Daily Calories</span>
            <span className="text-xl font-bold text-foreground">{nutritionAverages.calories.toLocaleString()} kcal</span>
          </div>
          <div className="h-2 w-full rounded-full bg-secondary overflow-hidden flex">
            <div className="bg-yellow-400 h-full" style={{ width: `${proteinPercentage}%` }}></div>
            <div className="bg-green-400 h-full" style={{ width: `${carbsPercentage}%` }}></div>
            <div className="bg-blue-400 h-full" style={{ width: `${fatPercentage}%` }}></div>
          </div>
          <div className="flex justify-between mt-1 text-xs text-muted-foreground">
            <span>Protein {proteinPercentage}%</span>
            <span>Carbs {carbsPercentage}%</span>
            <span>Fat {fatPercentage}%</span>
          </div>
        </div>

        <div className="space-y-2 py-2 border-t">
          <div className="flex justify-between items-center">
            <div className="flex items-center gap-2">
              <div className="size-3 rounded-full bg-yellow-400"></div>
              <span className="text-sm">Protein</span>
            </div>
            <span className="text-sm font-medium">{nutritionAverages.protein}g</span>
          </div>
          <div className="flex justify-between items-center">
            <div className="flex items-center gap-2">
              <div className="size-3 rounded-full bg-green-400"></div>
              <span className="text-sm">Carbs</span>
            </div>
            <span className="text-sm font-medium">{nutritionAverages.carbs}g</span>
          </div>
          <div className="flex justify-between items-center">
            <div className="flex items-center gap-2">
              <div className="size-3 rounded-full bg-blue-400"></div>
              <span className="text-sm">Fat</span>
            </div>
            <span className="text-sm font-medium">{nutritionAverages.fat}g</span>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
