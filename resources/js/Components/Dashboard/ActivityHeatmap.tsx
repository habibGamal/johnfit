interface ActivityDay {
    date: string; // YYYY-MM-DD
    count: number;
}

interface ActivityHeatmapProps {
    data: ActivityDay[];
    title?: string;
}

export default function ActivityHeatmap({ data, title = "Activity Intensity" }: ActivityHeatmapProps) {

    const getIntensityColor = (count: number) => {
        if (count === 0) return "bg-gray-100 dark:bg-gray-800";
        if (count === 1) return "bg-emerald-200 dark:bg-emerald-900";
        if (count === 2) return "bg-emerald-400 dark:bg-emerald-700";
        if (count >= 3) return "bg-emerald-600 dark:bg-emerald-500";
        return "bg-gray-100 dark:bg-gray-800";
    };

    return (
        <div className="w-full">
            <h4 className="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3 uppercase tracking-wider">{title}</h4>

            <div className="flex flex-wrap gap-1.5">
                {data.map((day) => (
                    <div
                        key={day.date}
                        title={`${day.date}: ${day.count} activities`}
                        className={`w-4 h-4 rounded-sm transition-colors hover:ring-2 hover:ring-offset-1 hover:ring-emerald-400 cursor-help ${getIntensityColor(day.count)}`}
                    />
                ))}
            </div>

            <div className="flex items-center gap-2 mt-2 text-xs text-gray-400 justify-end">
                <span>Less</span>
                <div className="flex gap-1">
                    <div className="w-3 h-3 rounded-sm bg-gray-100 dark:bg-gray-800"></div>
                    <div className="w-3 h-3 rounded-sm bg-emerald-200 dark:bg-emerald-900"></div>
                    <div className="w-3 h-3 rounded-sm bg-emerald-400 dark:bg-emerald-700"></div>
                    <div className="w-3 h-3 rounded-sm bg-emerald-600 dark:bg-emerald-500"></div>
                </div>
                <span>More</span>
            </div>
        </div>
    );
}
