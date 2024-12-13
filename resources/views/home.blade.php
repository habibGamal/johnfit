<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYMORT - Fitness And GYM</title>
    <!-- CSS Here -->
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
    <!-- Preloader Start -->
    <div class="preloader">
        <div class="preloader-content">
            <div class="preloader_img_circle"></div>
            <div class="preloader_img">
                <img src="{{ asset('/images/home/preloader/fitness_preimg.gif') }}" alt="fitness_preimg">
            </div>
        </div>
    </div>
    <!-- Preloader end -->
    <div class="inner_site_content">
        <!-- Header Start -->
        @include('components.home.header')
        <!-- Header End -->

        <!-- Banner-Slider Start -->
        @include('components.home.hero')
        <!-- Banner-Slider End -->

        <!-- About-Us Start -->

        @include('components.home.about')

        <!-- About-Us End -->


        <!-- Before after Start -->
        @include('components.home.before-after')
        <!-- Before after End -->

        <!-- Calculater-BMI Start -->
        @include('components.home.calculator')

        <!-- Calculater-BMI End -->

        <!-- Gym-Counter Start -->
        @include('components.home.counters')

        <!-- Gym-Counter End -->

        <!-- Client-Say Slider Start -->
        @include('components.home.testimonials')

        <!-- Client-Say Slider End -->

        <!-- Pricing-Plans Start -->
        @include('components.home.pricing')

        <!-- Pricing-Plans End -->


        <!-- Gym-Marqueer-Text Start -->
        <div class="gym_marqueer_text_sec reveal">
            <div class="gym_marqueer_text_slider_list bg_orange orangeglow">
                <div class="gym_marqueer_leftslide">
                    <div class="marquee_content">
                        <ul class="list-inline">
                            <li><a href="project-single-images.html" class="color_white custom_cursor_whiteglow">LIVE
                                    CLASSES</a></li>
                            <li><a href="javascript:void(0);" class="color_white custom_cursor_whiteglow">*</a></li>
                            <li><a href="project-single-images.html"
                                    class="color_white custom_cursor_whiteglow">ON-DEMAND WORKOUTS</a></li>
                            <li><a href="javascript:void(0);" class="color_white custom_cursor_whiteglow">*</a></li>
                            <li><a href="project-single-images.html"
                                    class="color_white custom_cursor_whiteglow">PERSONAL TRAINERS</a></li>
                            <li><a href="javascript:void(0);" class="color_white custom_cursor_whiteglow">*</a></li>
                            <li><a href="project-single-images.html" class="color_white custom_cursor_whiteglow">OUTDOOR
                                    & ONLINE TRAINERS</a></li>
                            <li><a href="javascript:void(0);" class="color_white custom_cursor_whiteglow">*</a></li>
                        </ul>
                        <ul class="list-inline">
                            <li><a href="project-single-images.html" class="color_white custom_cursor_whiteglow">LIVE
                                    CLASSES</a></li>
                            <li><a href="javascript:void(0);" class="color_white custom_cursor_whiteglow">*</a></li>
                            <li><a href="project-single-images.html"
                                    class="color_white custom_cursor_whiteglow">ON-DEMAND WORKOUTS</a></li>
                            <li><a href="javascript:void(0);" class="color_white custom_cursor_whiteglow">*</a></li>
                            <li><a href="project-single-images.html"
                                    class="color_white custom_cursor_whiteglow">PERSONAL TRAINERS</a></li>
                            <li><a href="javascript:void(0);" class="color_white custom_cursor_whiteglow">*</a></li>
                            <li><a href="project-single-images.html" class="color_white custom_cursor_whiteglow">OUTDOOR
                                    & ONLINE TRAINERS</a></li>
                            <li><a href="javascript:void(0);" class="color_white custom_cursor_whiteglow">*</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- Gym-Marqueer-Text End -->

        <!-- footer Start -->
        <footer>
            <div class="footer_sec bg_black" id="contact">
                <div class="container">
                    <div class="footer_area">
                        <div class="row">
                            <div class="col-12">
                                <div class="footer_upper_big_text sec_padding">
                                    <div class="reveal custom_lightSpeedInLeft">
                                        <a href="contact-us.html" class="contact_hover_btn">
                                            <svg viewBox="0 0 1300 128">
                                                <symbol id="s-text">
                                                    <text text-anchor="middle" x="50%" y="50%" dy=".35em">LET’S
                                                        DISCUSS</text>
                                                </symbol>
                                                <use class="text" xlink:href="#s-text"></use>
                                                <use class="text" xlink:href="#s-text"></use>
                                                <use class="text" xlink:href="#s-text"></use>
                                                <use class="text" xlink:href="#s-text"></use>
                                                <use class="text" xlink:href="#s-text"></use>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="footer_mid_area">
                                    <div class="row align-items-end">
                                        <div class="col-xl-4 col-lg-3 col-sm-6">
                                            <div class="footer_logo_area">
                                                <a href="javascript:void(0);"
                                                    class="footer_logo reveal custom_zoom_in d-block">
                                                    <img src="{{ asset('/images/home/logos/gymort_logo.png') }}"
                                                        alt="gymort_logo">
                                                </a>
                                                <p
                                                    class="satoshi_fontfamily line_height_30 fw_500 color_lightgray reveal custom_zoom_in">
                                                    Please fell free to send us an email at <a
                                                        href="mailto:coach.john.info@gmail.com "
                                                        class="color_lightgray triners_icons">coach.john.info@gmail.com </a> for
                                                    any additional inquiries</p>
                                            </div>
                                        </div>
                                        <div class="col-xl-2 col-lg-2 col-sm-6">
                                            <div class="footer_quick_contact">
                                                <h6 class="color_white reveal custom_lightSpeedInLeft">CALL US</h6>
                                                <div class="footer_contact">
                                                    <a href="tel:+201225390914"
                                                        class="satoshi_fontfamily line_height_30 fw_500 color_lightgray d-block triners_icons reveal custom_zoom_in">+20
                                                        12 25390914</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="footer_bottom_area">
                                    <div class="row align-items-center">
                                        <div class="col-sm-6">
                                            <p
                                                class="satoshi_fontfamily line_height_24 fw_500 color_lightgray reveal custom_zoom_in">
                                                Copyright 2024 © Design by <a
                                                    href="https://1.envato.market/website-portfolio"
                                                    class="color_lightgray triners_icons"
                                                    target="_blank">The_Krishna</a></p>
                                        </div>
                                        <div class="col-sm-6">
                                            <ul class="footer_social_icon">
                                                <li class="reveal custom_zoom_in">
                                                    <a href="https://www.facebook.com/JohnFitEg" class="triners_icons"
                                                        target="_blank">
                                                        <svg width="20" height="20" viewBox="0 0 20 20"
                                                            fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <mask style="mask-type:alpha" maskUnits="userSpaceOnUse"
                                                                x="0" y="0" width="20" height="20">
                                                                <rect width="20" height="20" fill="white" />
                                                            </mask>
                                                            <g>
                                                                <path
                                                                    d="M11.6654 11.2498H13.7487L14.582 7.9165H11.6654V6.24984C11.6654 5.39202 11.6654 4.58317 13.332 4.58317H14.582V1.78325C14.3106 1.74721 13.2845 1.6665 12.2011 1.6665C9.93903 1.6665 8.33203 3.04722 8.33203 5.58293V7.9165H5.83203V11.2498H8.33203V18.3332H11.6654V11.2498Z"
                                                                    fill="white" />
                                                            </g>
                                                        </svg>
                                                    </a>
                                                </li>
                                                <li class="reveal custom_zoom_in">
                                                    <a href="https://www.instagram.com/coach.johnxx/profilecard/?igsh=MXJrN2E5cDc2YnV6MQ==" class="triners_icons"
                                                        target="_blank">
                                                        <svg width="20" height="20" viewBox="0 0 20 20"
                                                            fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <mask style="mask-type:alpha" maskUnits="userSpaceOnUse"
                                                                x="0" y="0" width="20" height="20">
                                                                <rect width="20" height="20" fill="white" />
                                                            </mask>
                                                            <g>
                                                                <path
                                                                    d="M10.0013 7.49984C8.62015 7.49984 7.5013 8.61909 7.5013 9.99984C7.5013 11.3809 8.62057 12.4998 10.0013 12.4998C11.3824 12.4998 12.5013 11.3806 12.5013 9.99984C12.5013 8.61875 11.3821 7.49984 10.0013 7.49984ZM10.0013 5.83317C12.3017 5.83317 14.168 7.69742 14.168 9.99984C14.168 12.3003 12.3037 14.1665 10.0013 14.1665C7.70091 14.1665 5.83464 12.3023 5.83464 9.99984C5.83464 7.69945 7.69889 5.83317 10.0013 5.83317ZM15.418 5.62413C15.418 6.19922 14.9507 6.66581 14.3763 6.66581C13.8012 6.66581 13.3347 6.1985 13.3347 5.62413C13.3347 5.04975 13.8019 4.58317 14.3763 4.58317C14.9499 4.58245 15.418 5.04975 15.418 5.62413ZM10.0013 3.33317C7.93924 3.33317 7.60314 3.33863 6.64404 3.38134C5.99061 3.41201 5.55259 3.4999 5.14562 3.6579C4.78395 3.79816 4.52306 3.96565 4.24509 4.24363C3.96604 4.52267 3.79885 4.78286 3.65915 5.1447C3.50079 5.5526 3.41293 5.98993 3.3828 6.64246C3.33968 7.5625 3.33464 7.88405 3.33464 9.99984C3.33464 12.0619 3.34009 12.398 3.38279 13.357C3.41349 14.0102 3.50149 14.4488 3.6591 14.8548C3.79974 15.217 3.96757 15.4785 4.2442 15.7552C4.52434 16.0349 4.78531 16.2027 5.14371 16.341C5.55564 16.5003 5.99339 16.5883 6.64392 16.6183C7.56397 16.6614 7.88551 16.6665 10.0013 16.6665C12.0634 16.6665 12.3995 16.661 13.3585 16.6183C14.0102 16.5878 14.4492 16.4995 14.8563 16.342C15.2175 16.2018 15.4798 16.0333 15.7567 15.7569C16.0368 15.4763 16.2042 15.216 16.3427 14.8568C16.5016 14.4463 16.5897 14.008 16.6198 13.3573C16.6629 12.4372 16.668 12.1156 16.668 9.99984C16.668 7.93777 16.6625 7.60168 16.6198 6.64265C16.5892 5.99072 16.5009 5.55108 16.3432 5.14415C16.2033 4.78349 16.0352 4.5218 15.7575 4.24363C15.478 3.96413 15.2185 3.79728 14.8564 3.65769C14.4488 3.49945 14.0108 3.41147 13.3587 3.38135C12.4387 3.33821 12.1171 3.33317 10.0013 3.33317ZM10.0013 1.6665C12.2652 1.6665 12.5478 1.67484 13.4367 1.7165C14.3235 1.75748 14.9284 1.89775 15.4597 2.104C16.0089 2.31581 16.4728 2.60192 16.936 3.06511C17.3985 3.52831 17.6847 3.99359 17.8972 4.5415C18.1027 5.07206 18.243 5.67761 18.2847 6.56442C18.3242 7.45331 18.3347 7.73595 18.3347 9.99984C18.3347 12.2638 18.3263 12.5463 18.2847 13.4353C18.2437 14.3221 18.1027 14.9269 17.8972 15.4582C17.6853 16.0075 17.3985 16.4713 16.936 16.9346C16.4728 17.3971 16.0068 17.6832 15.4597 17.8957C14.9284 18.1013 14.3235 18.2415 13.4367 18.2832C12.5478 18.3228 12.2652 18.3332 10.0013 18.3332C7.73741 18.3332 7.45477 18.3248 6.56589 18.2832C5.67908 18.2422 5.07491 18.1013 4.54297 17.8957C3.99435 17.6838 3.52977 17.3971 3.06658 16.9346C2.60339 16.4713 2.31797 16.0054 2.10547 15.4582C1.89922 14.9269 1.75964 14.3221 1.71797 13.4353C1.67839 12.5463 1.66797 12.2638 1.66797 9.99984C1.66797 7.73595 1.6763 7.45331 1.71797 6.56442C1.75894 5.67692 1.89922 5.07275 2.10547 4.5415C2.31727 3.9929 2.60339 3.52831 3.06658 3.06511C3.52977 2.60192 3.99505 2.3165 4.54297 2.104C5.07422 1.89775 5.67839 1.75817 6.56589 1.7165C7.45477 1.67692 7.73741 1.6665 10.0013 1.6665Z"
                                                                    fill="white" />
                                                            </g>
                                                        </svg>
                                                    </a>
                                                </li>
                                                <li class="reveal custom_zoom_in">
                                                    <a href="https://www.youtube.com/@coachjohn-fitnesstraineran9776" class="triners_icons"
                                                        target="_blank">
                                                        <svg width="20" height="20" viewBox="0 0 20 20"
                                                            fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <mask style="mask-type:alpha" maskUnits="userSpaceOnUse"
                                                                x="0" y="0" width="20" height="20">
                                                                <rect width="20" height="20" fill="white" />
                                                            </mask>
                                                            <g>
                                                                <path
                                                                    d="M10.5366 3C10.9817 3.00245 12.0952 3.01322 13.2784 3.06061L13.6979 3.0789C14.8892 3.1353 16.0794 3.23165 16.6698 3.39625C17.4572 3.61746 18.0761 4.26292 18.2852 5.0811C18.6183 6.38034 18.66 8.91617 18.6652 9.52983L18.6659 9.657V9.66592C18.6659 9.66592 18.6659 9.669 18.6659 9.67492L18.6652 9.80208C18.66 10.4158 18.6183 12.9516 18.2852 14.2508C18.0732 15.072 17.4543 15.7175 16.6698 15.9357C16.0794 16.1003 14.8892 16.1966 13.6979 16.253L13.2784 16.2713C12.0952 16.3187 10.9817 16.3294 10.5366 16.3319L10.3412 16.3326H10.3326C10.3326 16.3326 10.3297 16.3326 10.3239 16.3326L10.1287 16.3319C9.18675 16.3268 5.2481 16.2842 3.99542 15.9357C3.208 15.7144 2.5891 15.069 2.37989 14.2508C2.04683 12.9516 2.0052 10.4158 2 9.80208V9.52983C2.0052 8.91617 2.04683 6.38034 2.37989 5.0811C2.592 4.25988 3.2109 3.61443 3.99542 3.39625C5.2481 3.04769 9.18675 3.00518 10.1287 3H10.5366ZM8.66593 6.74928V12.5826L13.6659 9.66592L8.66593 6.74928Z"
                                                                    fill="white" />
                                                            </g>
                                                        </svg>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- footer End -->

        <!-- Scroll-Top Start -->
        <div class="scrolltop_area orangeglow">
            <button id="scroll-top-btn">
                <span class="scroll-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <mask style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24"
                            height="24">
                            <path d="M0 0H24V24H0V0Z" fill="white" />
                        </mask>
                        <g>
                            <path d="M7 11L12 6L17 11" stroke="white" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M7 17L12 12L17 17" stroke="white" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </g>
                    </svg>
                </span>
                <span id="scroll-percentage"></span>
            </button>
        </div>
        <!-- Scroll-Top End -->

        <!-- custome mouse -->
        <div class="megic-cursor">
            <div class="megic-cursor-item"></div>
        </div>

    </div>

    <!-- JS Here -->
    <script src="{{ asset('js/home/jquery-3.7.1.js') }}"></script>
    {{-- <script src="{{ asset('js/home/myplugin.js') }}"></script> --}}
    <script src="{{ asset('js/home/slick.min.js') }}"></script>
    <script src="{{ asset('js/home/slick-animation.min.js') }}"></script>
    <script src="{{ asset('js/home/aos.js') }}"></script>
    <script src="{{ asset('js/home/plugins.js') }}"></script>
    <script src="{{ asset('js/home/TweenMax.min.js') }}"></script>
    <script src="{{ asset('js/home/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/home/allsliders.js') }}"></script>
    <script src="{{ asset('js/home/custome-cursor.js') }}"></script>
    <script src="{{ asset('js/home/style.js') }}"></script>

    <!-- AOS Animation -->
    <script>
        AOS.init({
            duration: 1600,
        });
        AOS.init();
    </script>
    <!-- Meet-Team Slider Btn -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const shareButtons = document.querySelectorAll(".meet_team_social_button");

            const closeAllShareButtons = () => {
                shareButtons.forEach((button) => {
                    button.classList.remove("open");
                });
                shareButtons[0].classList.remove("sent");
            };

            shareButtons.forEach((button, index) => {
                button.addEventListener("click", (e) => {
                    if (!button.classList.contains("open")) {
                        closeAllShareButtons();
                    }
                    button.classList.toggle("open");

                    if (index === 0) {
                        button.classList.remove("sent");
                    } else {
                        shareButtons[0].classList.toggle("sent");
                    }
                });
            });
        });
    </script>
</body>

</html>
