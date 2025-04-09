<x-filament::section>
    <x-slot name="heading">
        {{ __('workout.statistics.active_days') }}
    </x-slot>

    <div class="space-y-2">
        @forelse($activeDays as $day)
            <div class="flex items-center justify-between p-2 rounded-lg {{ $day['intensity'] }}">
                <span class="text-gray-700 font-medium">{{ $day['label'] }}</span>
                <div class="flex items-center gap-2">
                    <span class="text-gray-500">{{ $day['count'] }}</span>
                    <span class="text-xs text-gray-500">{{ trans_choice('workout.statistics.workout_count', $day['count']) }}</span>
                </div>
            </div>
        @empty
            <div class="text-gray-500 text-center py-4">
                {{ __('workout.statistics.no_active_days') }}
            </div>
        @endforelse
    </div>

    <x-slot name="footer">
        <div class="flex gap-4 text-xs text-gray-500">
            <div class="flex items-center gap-1">
                <div class="w-3 h-3 rounded-full bg-success-50"></div>
                <span>{{ __('workout.statistics.intensity.high') }} (≥5)</span>
            </div>
            <div class="flex items-center gap-1">
                <div class="w-3 h-3 rounded-full bg-warning-50"></div>
                <span>{{ __('workout.statistics.intensity.medium') }} (3-4)</span>
            </div>
            <div class="flex items-center gap-1">
                <div class="w-3 h-3 rounded-full bg-gray-50"></div>
                <span>{{ __('workout.statistics.intensity.low') }} (1-2)</span>
            </div>
        </div>
    </x-slot>
</x-filament::section>
