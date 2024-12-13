<header>
    <div id="header_section">
        <div class="common_header_1" id="header_theme_2">
            <div class="header_sec" id="header_sec">
                <div class="header_theme_2">
                    <div class="header_area">
                        <div class="row align-items-center">
                            <div class="col-xl-2 col-lg-3 col-6">
                                <div class="header_logo">
                                    <a href="/" class="custom_cursor_orangeglow">
                                        <img src="{{ asset('/images/home/logos/gymort_logo.png') }}" alt="header_logo"
                                            class="img-fluid">
                                    </a>
                                </div>
                            </div>
                            @include('components.home.nav-bar')
                            @include('components.home.slide-down')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
