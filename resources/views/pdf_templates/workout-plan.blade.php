@extends('pdf_templates.pdf-layout')
@section('content')
    @foreach ($data as $index => $day)
        @php
            $dayNumber = $index + 1;
            $dayName = $day['day'];
            $workouts = $day['workouts'];
        @endphp
        <h1 class="text-6xl font-bold mt-4 mb-10">
            DAY {{ $dayNumber }} <span class="text-main">-</span> {{ $dayName }}
        </h1>
        @foreach ($workouts as $exIndex => $workout)
            <div class="rounded-xl mb-8 p-8 w-full bg-cover bg-center bg-no-repeat"
                style="background-image: url('{{ asset('images/bg.png') }}');">
                <img class="rounded-xl mx-auto" src="{{ $workout['data']->thumb }}" alt="{{ $workout['data']->name }}">
                <div class="mt-12 flex justify-between">
                    <div class=" w-fit pb-4">
                        <h2
                            class="text-3xl font-bold mt-4 bg-white text-black py-2 px-4 w-fit border-l-8 border-black rounded">
                            {{ $workout['data']->name ?? '_______________' }}
                        </h2>
                        <p class="text-2xl text-black font-medium ml-6 mt-4">Reps: {{ $workout['reps']->value }}</p>
                    </div>
                    <div class="flex-grow min-w-[30%] grid place-items-center">
                        <a href="{{ $workout['data']->video_url }}">
                            <img class="w-[70px] h-[70px] object-contain" src="{{ asset('images/play-button.png') }}"
                                alt="Play">
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach
@endsection
