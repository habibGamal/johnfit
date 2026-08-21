@extends('layouts.home')

@section('title', $page->title . ' - GYMORT - Fitness And GYM')

@section('content')
    <section class="bg_black sec_padding">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-10">
                    <div class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <h1 class="color_white zen_dots_fontfamily text-uppercase mb-0">{{ $page->title }}</h1>
                        <a href="{{ url('/') }}" class="white_hover_btn text-decoration-none">
                            <span class="px-3 py-2 d-inline-block">Back To Home</span>
                        </a>
                    </div>

                    <div class="bg-white rounded-3 p-4 p-md-5">
                        {!! $page->content !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
