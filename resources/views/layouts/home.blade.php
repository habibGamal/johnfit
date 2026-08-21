<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'GYMORT - Fitness And GYM')</title>
    <link rel="shortcut icon" href="assets/images/favicon/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Zen+Dots&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home/animate_plugin.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home/animate.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home/aos.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/home/media-query.css') }}">
</head>

<body>
    <div class="preloader">
        <div class="preloader-content">
            <div class="preloader_img_circle"></div>
            <div class="preloader_img">
                <img src="{{ asset('images/home/preloader/fitness_preimg.gif') }}" alt="fitness_preimg">
            </div>
        </div>
    </div>

    <div class="inner_site_content">
        @include('components.home.header')

        @yield('content')

        @include('components.home.footer')
    </div>

    <script src="{{ asset('js/home/jquery-3.7.1.js') }}"></script>
    <script src="{{ asset('js/home/slick.min.js') }}"></script>
    <script src="{{ asset('js/home/slick-animation.min.js') }}"></script>
    <script src="{{ asset('js/home/aos.js') }}"></script>
    <script src="{{ asset('js/home/plugins.js') }}"></script>
    <script src="{{ asset('js/home/TweenMax.min.js') }}"></script>
    <script src="{{ asset('js/home/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/home/allsliders.js') }}"></script>
    <script src="{{ asset('js/home/custome-cursor.js') }}"></script>
    <script src="{{ asset('js/home/style.js') }}"></script>

    <script>
        AOS.init({
            duration: 1600,
        });
        AOS.init();
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const shareButtons = document.querySelectorAll('.meet_team_social_button');

            const closeAllShareButtons = () => {
                shareButtons.forEach((button) => {
                    button.classList.remove('open');
                });

                if (shareButtons[0]) {
                    shareButtons[0].classList.remove('sent');
                }
            };

            shareButtons.forEach((button, index) => {
                button.addEventListener('click', () => {
                    if (!button.classList.contains('open')) {
                        closeAllShareButtons();
                    }

                    button.classList.toggle('open');

                    if (index === 0) {
                        button.classList.remove('sent');
                    } else if (shareButtons[0]) {
                        shareButtons[0].classList.toggle('sent');
                    }
                });
            });
        });
    </script>

    <script>
        const currentYearElement = document.getElementById('current-year');

        if (currentYearElement) {
            currentYearElement.textContent = new Date().getFullYear();
        }
    </script>

    @stack('scripts')
</body>

</html>
