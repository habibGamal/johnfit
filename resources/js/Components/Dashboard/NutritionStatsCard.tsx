import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
import { NutritionAverages, MacroDistributionItem } from "@/types";
import { Pizza } from "lucide-react";
import MacroDonutChart from "./MacroDonutChart";

interface NutritionStatsCardProps {
    nutritionAverages: NutritionAverages;
    macroDistribution?: MacroDistributionItem[];
}

export default function NutritionStatsCard({
    nutritionAverages,
    macroDistribution
}: NutritionStatsCardProps) {
    // Calculate percentage of each macro based on calories (rough estimate if distribution not provided)
    // 1g protein = 4 calories, 1g carbs = 4 calories, 1g fat = 9 calories
    const totalCalories = nutritionAverages.calories || 1; // Prevent division by zero
    const proteinCalories = nutritionAverages.protein * 4;
    const carbsCalories = nutritionAverages.carbs * 4;
    const fatCalories = nutritionAverages.fat * 9;

    const proteinPercentage = Math.round(
        (proteinCalories / totalCalories) * 100
    );
    const carbsPercentage = Math.round((carbsCalories / totalCalories) * 100);
    const fatPercentage = Math.round((fatCalories / totalCalories) * 100);

    return (
        <Card className="h-full flex flex-col shadow-sm border-gray-100 dark:border-gray-700 dark:bg-gray-800/50 backdrop-blur-sm">
            <CardHeader>
                <CardTitle className="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
                    <div className="p-2 bg-orange-500/10 rounded-lg">
                        <Pizza className="h-5 w-5 text-orange-500" />
                    </div>
                    Nutrition Breakdown
                </CardTitle>
                <CardDescription className="text-gray-500 dark:text-gray-400">Daily average consumption</CardDescription>
            </CardHeader>
            <CardContent className="flex-1 flex flex-col justify-between">
                <div className="mb-6">
                    <div className="flex justify-between items-baseline mb-3">
                        <span className="text-sm font-medium text-gray-600 dark:text-gray-300">Daily Calories</span>
                        <span className="text-2xl font-bold text-gray-900 dark:text-white">
                            {nutritionAverages.calories.toLocaleString()} <span className="text-sm font-normal text-gray-500">kcal</span>
                        </span>
                    </div>

                    {/* Donut Chart or Fallback Bars */}
                    <div className="flex justify-center py-2">
                        {macroDistribution ? (
                            <MacroDonutChart data={macroDistribution} size={180} thickness={16} />
                        ) : (
                            <div className="h-3 w-full rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden flex">
                                <div
                                    className="bg-yellow-400 h-full transition-all duration-500"
                                    style={{ width: `${proteinPercentage}%` }}
                                ></div>
                                <div
                                    className="bg-green-400 h-full transition-all duration-500"
                                    style={{ width: `${carbsPercentage}%` }}
                                ></div>
                                <div
                                    className="bg-blue-400 h-full transition-all duration-500"
                                    style={{ width: `${fatPercentage}%` }}
                                ></div>
                            </div>
                        )}
                    </div>

                    {!macroDistribution && (
                        <div className="flex justify-between mt-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                            <span className="flex items-center"><div className="w-2 h-2 rounded-full bg-yellow-400 mr-1"></div>Protein {proteinPercentage}%</span>
                            <span className="flex items-center"><div className="w-2 h-2 rounded-full bg-green-400 mr-1"></div>Carbs {carbsPercentage}%</span>
                            <span className="flex items-center"><div className="w-2 h-2 rounded-full bg-blue-400 mr-1"></div>Fat {fatPercentage}%</span>
                        </div>
                    )}
                </div>

                <div className="space-y-3 py-4 border-t border-gray-100 dark:border-gray-700">
                    <div className="flex justify-between items-center p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <div className="flex items-center gap-3">
                            <div className="size-8 rounded-lg bg-yellow-400/10 flex items-center justify-center text-yellow-600 font-bold text-xs">P</div>
                            <span className="text-sm font-medium text-gray-700 dark:text-gray-300">Protein</span>
                        </div>
                        <span className="text-sm font-bold text-gray-900 dark:text-white">
                            {nutritionAverages.protein}g
                        </span>
                    </div>
                    <div className="flex justify-between items-center p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <div className="flex items-center gap-3">
                            <div className="size-8 rounded-lg bg-green-400/10 flex items-center justify-center text-green-600 font-bold text-xs">C</div>
                            <span className="text-sm font-medium text-gray-700 dark:text-gray-300">Carbs</span>
                        </div>
                        <span className="text-sm font-bold text-gray-900 dark:text-white">
                            {nutritionAverages.carbs}g
                        </span>
                    </div>
                    <div className="flex justify-between items-center p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <div className="flex items-center gap-3">
                            <div className="size-8 rounded-lg bg-blue-400/10 flex items-center justify-center text-blue-600 font-bold text-xs">F</div>
                            <span className="text-sm font-medium text-gray-700 dark:text-gray-300">Fat</span>
                        </div>
                        <span className="text-sm font-bold text-gray-900 dark:text-white">
                            {nutritionAverages.fat}g
                        </span>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
