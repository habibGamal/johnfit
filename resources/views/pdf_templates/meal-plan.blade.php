@extends('pdf_templates.pdf-layout')
@section('content')
    <div class="grid grid-cols-2 items-center gap-4 mt-2 w-full">
        <div
            class="text-lg rounded-full min-w-[140px] w-fit aspect-square p-4 border-4 border-b-transparent border-amber-500 flex flex-col items-center justify-center mx-auto ">
            <span class="font-bold">Calories</span>
            <span> {{ $model->targets['calories'] }} kcal</span>
        </div>
        <div
            class="text-lg rounded-full min-w-[140px] w-fit aspect-square p-4 border-4 border-b-transparent border-amber-500 flex flex-col items-center justify-center mx-auto ">
            <span class="font-bold">Protein</span>
            <span> {{ $model->targets['proteins'] }} g</span>
        </div>
        <div
            class="text-lg rounded-full min-w-[140px] w-fit aspect-square p-4 border-4 border-b-transparent border-amber-500 flex flex-col items-center justify-center mx-auto ">
            <span class="font-bold">Carbs</span>
            <span> {{ $model->targets['carbs'] }} g</span>
        </div>
        <div
            class="text-lg rounded-full min-w-[140px] w-fit aspect-square p-4 border-4 border-b-transparent border-amber-500 flex flex-col items-center justify-center mx-auto ">
            <span class="font-bold">Fats</span>
            <span> {{ $model->targets['fats'] }} g</span>
        </div>
    </div>


    @foreach ($data as $index => $day)
        @php
            $dayNumber = $index + 1;
            $dayName = $day['day'];
            $times = $day['time'];
            // dd($data);
        @endphp
        <h1 class="text-6xl font-bold mt-4 mb-10 border-b-2 border-amber-500 pb-4">
            DAY {{ $dayNumber }} <span class="text-main">-</span> {{ $dayName }}
        </h1>
        @foreach ($times as $time)
            <h2 class="text-5xl font-bold mt-4 mb-10">
                {{ $time['meal_time'] }}
            </h2>
            @foreach ($time['meals'] as $mealSlot)
                @php $options = $mealSlot['options'] ?? []; @endphp
                @if (count($options) > 1)
                    {{-- OR group: show each option separated by an OR divider --}}
                    <div class="mb-6 border-2 border-amber-400 rounded-xl overflow-hidden">
                        <div class="bg-amber-500 text-white text-center text-xl font-bold py-1 px-4 tracking-widest uppercase">
                            اختر واحداً / Choose One
                        </div>
                        @foreach ($options as $optIndex => $meal)
                            @if ($optIndex > 0)
                                <div class="flex items-center gap-4 px-4 py-1 bg-amber-50">
                                    <div class="flex-1 border-t-2 border-amber-300"></div>
                                    <span class="font-bold text-amber-600 text-xl tracking-widest">OR / أو</span>
                                    <div class="flex-1 border-t-2 border-amber-300"></div>
                                </div>
                            @endif
                            <div class="flex flex-col items-end gap-4 meal-details bg-gray-100 text-gray-950 p-4 text-right">
                                <p class="font-bold text-xl rounded px-2 w-fit bg-amber-600 text-white">
                                    جرام {{ $meal['quantity'] }}
                                </p>
                                <h3 class="text-2xl font-medium">
                                    {{ $meal['data']->name }}
                                </h3>
                                <ul class="grid grid-cols-4 gap-4 mt-2 w-full">
                                    @foreach ($meal['data']->meal_macros as $macroName => $macroValue)
                                        <li class="text-lg rounded flex flex-col items-center bg-amber-100">
                                            <span class="rounded px-2 bg-amber-500 text-amber-50 w-full text-center">{{ ucfirst($macroName) }}</span>
                                            <span> {{ $macroValue * $meal['quantity'] }}
                                                {{ $macroName === 'calories' ? 'kcal' : 'g' }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @else
                    @php $meal = $options[0] ?? null; @endphp
                    @if ($meal)
                        <div class="flex flex-col items-end gap-4 meal-details mb-6 bg-gray-100 text-gray-950 p-4 rounded-xl text-right">
                            <p class="font-bold text-xl rounded px-2 w-fit bg-amber-600 text-white">
                                جرام {{ $meal['quantity'] }}
                            </p>
                            <h3 class="text-2xl font-medium">
                                {{ $meal['data']->name }}
                            </h3>
                            <ul class="grid grid-cols-4 gap-4 mt-2 w-full">
                                @foreach ($meal['data']->meal_macros as $macroName => $macroValue)
                                    <li class="text-lg rounded flex flex-col items-center bg-amber-100">
                                        <span class="rounded px-2 bg-amber-500 text-amber-50 w-full text-center">{{ ucfirst($macroName) }}</span>
                                        <span> {{ $macroValue * $meal['quantity'] }}
                                            {{ $macroName === 'calories' ? 'kcal' : 'g' }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endif
            @endforeach
        @endforeach
    @endforeach
@endsection
