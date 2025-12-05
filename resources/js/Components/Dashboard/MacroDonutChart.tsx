import { useMemo } from 'react';

interface MacroData {
    name: string;
    value: number;
    color: string;
}

interface MacroDonutChartProps {
    data: MacroData[];
    size?: number;
    thickness?: number;
}

export default function MacroDonutChart({
    data,
    size = 160,
    thickness = 20
}: MacroDonutChartProps) {
    const radius = (size - thickness) / 2;
    const circumference = 2 * Math.PI * radius;

    const segments = useMemo(() => {
        let accumulatedValue = 0;
        const total = data.reduce((acc, curr) => acc + curr.value, 0);

        // Normalize if total is not 100
        const normalizedData = total === 0 ? data : data.map(item => ({
            ...item,
            normalizedValue: (item.value / total) * 100
        }));

        return normalizedData.map((item) => {
            // Use normalizedValue if it exists, otherwise fall back to value (though normalized should exist)
            const val = 'normalizedValue' in item ? (item as any).normalizedValue : item.value;

            const offset = (accumulatedValue / 100) * circumference;
            const strokeDashArray = `${(val / 100) * circumference} ${circumference}`;
            accumulatedValue += val;

            return {
                ...item,
                offset: -offset,
                strokeDashArray
            };
        });
    }, [data, circumference]);

    return (
        <div className="flex flex-col items-center">
            <div className="relative" style={{ width: size, height: size }}>
                {/* Background Circle */}
                <svg width={size} height={size} className="transform -rotate-90">
                    <circle
                        cx={size / 2}
                        cy={size / 2}
                        r={radius}
                        fill="transparent"
                        stroke="currentColor"
                        strokeWidth={thickness}
                        className="text-gray-100 dark:text-gray-800"
                    />
                    {segments.map((segment) => (
                        <circle
                            key={segment.name}
                            cx={size / 2}
                            cy={size / 2}
                            r={radius}
                            fill="transparent"
                            stroke={segment.color}
                            strokeWidth={thickness}
                            strokeDasharray={segment.strokeDashArray}
                            strokeDashoffset={segment.offset}
                            strokeLinecap="round"
                            className="transition-all duration-1000 ease-out"
                        />
                    ))}
                </svg>

                {/* Center Text */}
                <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span className="text-gray-400 text-xs font-medium uppercase tracking-wider">Macros</span>
                </div>
            </div>

            {/* Legend */}
            <div className="flex justify-center flex-wrap gap-4 mt-4">
                {data.map((item) => (
                    <div key={item.name} className="flex items-center">
                        <span
                            className="w-3 h-3 rounded-full mr-2"
                            style={{ backgroundColor: item.color }}
                        />
                        <span className="text-sm text-gray-600 dark:text-gray-300 font-medium">
                            {item.name} <span className="text-gray-400 font-normal">({item.value}%)</span>
                        </span>
                    </div>
                ))}
            </div>
        </div>
    );
}
