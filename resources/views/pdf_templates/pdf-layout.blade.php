<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <style>
        html {
            width: 600px;
        }

        ::-webkit-scrollbar {
            width: 0px;
        }
    </style>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
        integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>


    <!-- Scripts -->
    @vite(['resources/css/app.css'])
</head>

<body>
    <div class="bg-[#0e0e0e] pb-2" id="app">
        <div class="text-white w-[90%] mx-auto">
            <div class="flex justify-between py-6 items-center">
                <img src="{{ asset('images/logo_pdf.png') }}" onclick="print()" class="rounded-full w-16" alt="Logo">
                <a class="text-white text-xl font-medium"
                    href="https://www.facebook.com/photo/?fbid=452259133590019&set=a.452259086923357">
                    Facebook
                </a>
            </div>
            <div class="mb-8 p-8 w-full">
                <img class="rounded-xl" src="{{ asset('images/intro.jpg') }}" alt="Intro">
            </div>

            @yield('content')

            <div class="mb-8 p-8 w-full">
                <img class="rounded-xl" src="{{ asset('images/outer.jpg') }}" alt="Outer">
            </div>
        </div>
    </div>
    <!-- Include your JS files here -->
    <script src="{{ asset('js/app.js') }}"></script>
    @if (isset($print) && $print)
        <script>
            var element = document.getElementById('app');
            html2pdf(element, {
                filename: "{{ $name }}.pdf",
                html2canvas: {
                    useCORS: true,
                    scale: 2,
                    height: document.querySelector('#app').clientHeight + 150
                },
                jsPDF: {
                    unit: 'px',
                    format: [600, document.querySelector('#app').clientHeight + 150],
                    orientation: 'p'
                }
            });
        </script>
    @endif
</body>

</html>