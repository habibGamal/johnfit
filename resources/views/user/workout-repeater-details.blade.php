@props(['workout'])

@if($workout)
    <div class="p-4 bg-white rounded-lg shadow-sm">
        <div class="flex items-start gap-4">
            @if($workout->thumb)
                <div class="flex-shrink-0">
                    <img src="{{ Storage::url($workout->thumb) }}"
                         alt="{{ $workout->name }}"
                         class="w-16 h-16 object-cover rounded-lg">
                </div>
            @endif

            <div class="flex-1 min-w-0">
                <h4 class="text-sm font-medium text-gray-900">{{ $workout->name }}</h4>

                <div class="mt-1 space-y-1">
                    @if($workout->muscles)
                        <p class="text-xs text-gray-600">
                            <span class="font-medium">{{ __('Muscles') }}:</span>
                            {{ collect($workout->muscles)->join(', ') }}
                        </p>
                    @endif

                    @if($workout->tools)
                        <p class="text-xs text-gray-600">
                            <span class="font-medium">{{ __('Equipment') }}:</span>
                            {{ collect($workout->tools)->join(', ') }}
                        </p>
                    @endif
                </div>

                @if($workout->video_url)
                    <a href="{{ $workout->video_url }}"
                       target="_blank"
                       class="inline-flex items-center mt-2 text-xs text-primary-600 hover:text-primary-500">
                        <span>{{ __('Watch Tutorial') }}</span>
                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </div>
@else
    <div class="p-4 text-sm text-gray-500">
        {{ __('Workout not found') }}
    </div>
@endif
