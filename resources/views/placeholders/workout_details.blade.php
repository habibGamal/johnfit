<div class="flex gap-4">
    <div class="flex-1">
        <div class="flex gap-2">
            <div class="flex-1">
                <div class="text-lg font-bold text-gray-800">Workout Thumbnail</div>
                <img src="{{ $workout->thumb }}" />
            </div>
            <div class="flex-1">
                <div class="text-lg font-bold text-gray-800">Workout Video</div>
                <div class="text-sm text-gray-500">
                    <a href="{{ $workout->video_url }}" class="text-blue-500">Watch Video</a>
                </div>
            </div>
            <div class="flex-1">
                <div class="text-lg font-bold text-gray-800">Workout Target Muscels</div>
                <div class="text-sm text-gray-500">{{ implode(',', $workout->muscles) }}</div>
            </div>
            <div class="flex-1">
                <div class="text-lg font-bold text-gray-800">Workout Tools</div>
                <div class="text-sm text-gray-500">{{ implode(',', $workout->tools) }}</div>
            </div>
        </div>
    </div>

</div>
