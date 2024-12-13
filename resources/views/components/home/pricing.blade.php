<section class="pricing_plan_sec" id="pricing">
    <div class="pricing_v2 bg_black sec_padding">
        <div class="container">
            <div class="pricing_plans" id="pricing_v2">
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-12">
                        <div class="common_title_area pricing_plans_title text-center">
                            <h5 class="satoshi_fontfamily fw_500 line_height_normal color_orange reveal custom_fade_top">
                                Pricing Plans</h5>
                            <h3 class="pt-10 reveal custom_lightSpeedInLeft color_white">FIND PERFECT PLAN</h3>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="pricing_plans_area pricing_plans_area_v2 pt-60">
                            <div class="row">
                                <div class="col-lg-12 col-sm-6">
                                    <div class="pricing_plan_v2_box">
                                        <div class="pricing_plan_v2_head bg_white whiteglow">
                                            <h4 class="color_orange pb-20 reveal custom_zoom_in">BASIC</h4>
                                            <p class="satoshi_fontfamily reveal custom_zoom_in">BILLED MONTHLY
                                            </p>
                                        </div>
                                        <div class="pricing_plan_v2_bottom bg_black">
                                            <div class="row">
                                                <div class="col-xl-8">
                                                    <ul
                                                        class="pricing_plan_v2_points satoshi_fontfamily reveal custom_zoom_in">
                                                        @foreach (['Customized diet plan.', 'Customized workout plan.', 'Monthly follow-up.'] as $feature)
                                                            <x-home.package-feature>
                                                                {{ $feature }}
                                                            </x-home.package-feature>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <div class="col-xl-4">
                                                    <div class="pricing_plan_v2_button">
                                                        <h2 class="color_white mb-20 reveal custom_zoom_in">333 EGP
                                                        </h2>
                                                        <div class="purches_btn reveal custom_fade_buttom">
                                                            <a href="pricing-plans-v2.html" class="orange_btn">
                                                                <span class="orenge_text orangeglow_btn"
                                                                    data-hover="Purchase Now">
                                                                    Purchase Now
                                                                </span>
                                                                <span class="orenge_icon whiteglow_btn">
                                                                    <img src="{{ asset('/images/home/svgs/common_button_arrow.svg') }}"
                                                                        alt="common_button_arrow">
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-sm-6">
                                    <div class="pricing_plan_v2_box">
                                        <div class="pricing_plan_v2_head bg_white whiteglow">
                                            <h4 class="color_orange pb-20 reveal custom_zoom_in">Normal</h4>
                                            <p class="satoshi_fontfamily reveal custom_zoom_in">BILLED MONTHLY
                                            </p>
                                        </div>
                                        <div class="pricing_plan_v2_bottom bg_black">
                                            <div class="row">
                                                <div class="col-xl-8">
                                                    <ul
                                                        class="pricing_plan_v2_points satoshi_fontfamily reveal custom_zoom_in">
                                                        @foreach (['Personalized nutrition plan (adjustable).', 'Customized training program (home or gym).', 'Twice-monthly follow-ups via Zoom.', 'Responses to inquiries and solutions via WhatsApp within 48-72 hours.', 'Adaptive and personalized training plan (adjustable).', 'Tailored diet plan (adjustable).'] as $feature)
                                                            <x-home.package-feature>
                                                                {{ $feature }}
                                                            </x-home.package-feature>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <div class="col-xl-4">
                                                    <div class="pricing_plan_v2_button">
                                                        <h2 class="color_white mb-20 reveal custom_zoom_in">555 EGP
                                                        </h2>
                                                        <div class="purches_btn reveal custom_fade_buttom">
                                                            <a href="pricing-plans-v2.html" class="orange_btn">
                                                                <span class="orenge_text orangeglow_btn"
                                                                    data-hover="Purchase Now">
                                                                    Purchase Now
                                                                </span>
                                                                <span class="orenge_icon whiteglow_btn">
                                                                    <img src="{{ asset('/images/home/svgs/common_button_arrow.svg') }}"
                                                                        alt="common_button_arrow">
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-sm-6">
                                    <div class="pricing_plan_v2_box active">
                                        <div class="pricing_plan_v2_head bg_white whiteglow">
                                            <h3 class="d-none">hidden</h3>
                                            <h4 class="color_orange pb-20 reveal custom_zoom_in">ADVANCE</h4>
                                            <p class="satoshi_fontfamily reveal custom_zoom_in">BILLED MONTHLY
                                            </p>
                                        </div>
                                        <div class="pricing_plan_v2_bottom bg_black">
                                            <div class="row">
                                                <div class="col-xl-8">
                                                    <ul
                                                        class="pricing_plan_v2_points satoshi_fontfamily reveal custom_zoom_in">
                                                        @foreach (['Personalized nutrition plan (adjustable).', 'Healthy recipes to help you achieve your goals while enjoying your meals.', 'Weekly follow-ups via Zoom.', 'Daily responses to inquiries and solutions via WhatsApp.', 'Medical monitoring of your vital measurements.'] as $feature)
                                                            <x-home.package-feature>
                                                                {{ $feature }}
                                                            </x-home.package-feature>
                                                        @endforeach

                                                    </ul>
                                                </div>
                                                <div class="col-xl-4">
                                                    <div class="pricing_plan_v2_button">
                                                        <h2 class="color_white mb-20 reveal custom_zoom_in">888 EGP
                                                        </h2>
                                                        <div class="purches_btn reveal custom_fade_buttom">
                                                            <a href="pricing-plans-v2.html" class="orange_btn">
                                                                <span class="orenge_text orangeglow_btn"
                                                                    data-hover="Purchase Now">
                                                                    Purchase Now
                                                                </span>
                                                                <span class="orenge_icon whiteglow_btn">
                                                                    <img src="{{ asset('/images/home/svgs/common_button_arrow.svg') }}"
                                                                        alt="common_button_arrow">
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-sm-6">
                                    <div class="pricing_plan_v2_box">
                                        <div class="pricing_plan_v2_head bg_white whiteglow">
                                            <h3 class="d-none">hidden</h3>
                                            <h4 class="color_orange pb-20 reveal custom_zoom_in">PREMIUM</h4>
                                            <p class="satoshi_fontfamily reveal custom_zoom_in">BILLED MONTHLY
                                            </p>
                                        </div>
                                        <div class="pricing_plan_v2_bottom bg_black">
                                            <div class="row">
                                                <div class="col-xl-8">
                                                    <ul
                                                        class="pricing_plan_v2_points satoshi_fontfamily reveal custom_zoom_in">
                                                        @foreach (['Follow-up with a specialized doctor.', 'Personalized nutrition plan (adjustable).', 'Healthy recipes to help you achieve your goals while enjoying your meals.', 'Weekly follow-ups via Zoom.', 'Daily responses to inquiries and solutions via WhatsApp.', 'Medical monitoring of your vital measurements.'] as $feature)
                                                            <x-home.package-feature>
                                                                {{ $feature }}
                                                            </x-home.package-feature>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <div class="col-xl-4">
                                                    <div class="pricing_plan_v2_button">
                                                        <h2 class="color_white mb-20 reveal custom_zoom_in">1111 EGP
                                                        </h2>
                                                        <div class="purches_btn reveal custom_fade_buttom">
                                                            <a href="pricing-plans-v2.html" class="orange_btn">
                                                                <span class="orenge_text orangeglow_btn"
                                                                    data-hover="Purchase Now">
                                                                    Purchase Now
                                                                </span>
                                                                <span class="orenge_icon whiteglow_btn">
                                                                    <img src="{{ asset('/images/home/svgs/common_button_arrow.svg') }}"
                                                                        alt="common_button_arrow">
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
