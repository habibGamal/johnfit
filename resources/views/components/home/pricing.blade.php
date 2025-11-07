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
                                <!-- BASIC Plan -->
                                <div class="col-lg-12 col-sm-6 mb-4">
                                    <div class="pricing_plan_v2_box" style="border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 16px; overflow: hidden; transition: all 0.3s ease;">
                                        <div class="pricing_plan_v2_head bg_white whiteglow" style="padding: 30px;">
                                            <h4 class="color_orange pb-2 reveal custom_zoom_in" style="font-size: 28px; font-weight: 700; margin-bottom: 8px;">BASIC</h4>
                                            <p class="satoshi_fontfamily reveal custom_zoom_in" style="color: #666; font-size: 14px; margin-bottom: 0;">Perfect for getting started</p>
                                        </div>
                                        <div class="pricing_plan_v2_bottom bg_black" style="padding: 30px;">
                                            <div class="row">
                                                <div class="col-xl-7 mb-4 mb-xl-0">
                                                    <ul class="pricing_plan_v2_points satoshi_fontfamily reveal custom_zoom_in" style="margin-bottom: 0;">
                                                        @foreach (['Customized diet plan.', 'Customized workout plan.', 'Monthly follow-up.'] as $feature)
                                                            <x-home.package-feature>
                                                                {{ $feature }}
                                                            </x-home.package-feature>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <div class="col-xl-5">
                                                    <div class="pricing_plan_v2_button">
                                                        <div class="mb-4 reveal custom_zoom_in" style="background: rgba(255, 255, 255, 0.05); border-radius: 12px; padding: 20px;">
                                                            <div style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                                                <div class="d-flex align-items-baseline justify-content-between mb-1">
                                                                    <span class="color_white satoshi_fontfamily" style="font-size: 13px; font-weight: 500;">1 Month</span>
                                                                    <div class="d-flex align-items-baseline" style="gap: 4px;">
                                                                        <h3 class="color_orange mb-0" style="font-size: 24px; font-weight: 700;">400</h3>
                                                                        <span class="color_white satoshi_fontfamily" style="font-size: 14px; opacity: 0.7;">EGP</span>
                                                                    </div>
                                                                </div>
                                                                <p class="satoshi_fontfamily" style="font-size: 11px; color: rgba(255, 255, 255, 0.5); margin: 0; text-align: right;">133 EGP/month</p>
                                                            </div>
                                                            <div style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                                                <div class="d-flex align-items-baseline justify-content-between mb-1">
                                                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                                                        <span class="color_white satoshi_fontfamily" style="font-size: 13px; font-weight: 500;">3 Months</span>
                                                                        <span style="background: rgba(255, 107, 0, 0.15); color: #ff6b00; font-size: 10px; padding: 2px 8px; border-radius: 4px; font-weight: 600;">SAVE 17%</span>
                                                                    </div>
                                                                    <div class="d-flex align-items-baseline" style="gap: 4px;">
                                                                        <h3 class="color_orange mb-0" style="font-size: 24px; font-weight: 700;">1000</h3>
                                                                        <span class="color_white satoshi_fontfamily" style="font-size: 14px; opacity: 0.7;">EGP</span>
                                                                    </div>
                                                                </div>
                                                                <p class="satoshi_fontfamily" style="font-size: 11px; color: rgba(255, 255, 255, 0.5); margin: 0; text-align: right;">333 EGP/month</p>
                                                            </div>
                                                            <div>
                                                                <div class="d-flex align-items-baseline justify-content-between mb-1">
                                                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                                                        <span class="color_white satoshi_fontfamily" style="font-size: 13px; font-weight: 500;">6 Months</span>
                                                                        <span style="background: rgba(255, 107, 0, 0.15); color: #ff6b00; font-size: 10px; padding: 2px 8px; border-radius: 4px; font-weight: 600;">SAVE 25%</span>
                                                                    </div>
                                                                    <div class="d-flex align-items-baseline" style="gap: 4px;">
                                                                        <h3 class="color_orange mb-0" style="font-size: 24px; font-weight: 700;">1800</h3>
                                                                        <span class="color_white satoshi_fontfamily" style="font-size: 14px; opacity: 0.7;">EGP</span>
                                                                    </div>
                                                                </div>
                                                                <p class="satoshi_fontfamily" style="font-size: 11px; color: rgba(255, 255, 255, 0.5); margin: 0; text-align: right;">300 EGP/month</p>
                                                            </div>
                                                        </div>
                                                        <div class="purches_btn reveal custom_fade_buttom">
                                                            <a href="pricing-plans-v2.html" class="orange_btn" style="width: 100%; border-radius: 8px;">
                                                                <span class="orenge_text orangeglow_btn" data-hover="Get Started">Get Started</span>
                                                                <span class="orenge_icon whiteglow_btn">
                                                                    <img src="{{ asset('/images/home/svgs/common_button_arrow.svg') }}" alt="common_button_arrow">
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ADVANCED Plan -->
                                <div class="col-lg-12 col-sm-6 mb-4">
                                    <div class="pricing_plan_v2_box active" style="border: 3px solid #ff6b00; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 40px rgba(255, 107, 0, 0.3); transform: scale(1.02); transition: all 0.3s ease; position: relative;">
                                        <div style="position: absolute; top: 0; right: 30px; background: linear-gradient(135deg, #ff6b00, #ff8c00); color: white; padding: 6px 20px; font-size: 12px; font-weight: 700; border-radius: 0 0 8px 8px; box-shadow: 0 4px 12px rgba(255, 107, 0, 0.4);">MOST POPULAR</div>
                                        <div class="pricing_plan_v2_head bg_white whiteglow" style="padding: 30px; padding-top: 50px;">
                                            <h4 class="color_orange pb-2 reveal custom_zoom_in" style="font-size: 28px; font-weight: 700; margin-bottom: 8px;">ADVANCED</h4>
                                            <p class="satoshi_fontfamily reveal custom_zoom_in" style="color: #666; font-size: 14px; margin-bottom: 0;">Recommended for best results</p>
                                        </div>
                                        <div class="pricing_plan_v2_bottom bg_black" style="padding: 30px;">
                                            <div class="row">
                                                <div class="col-xl-7 mb-4 mb-xl-0">
                                                    <ul class="pricing_plan_v2_points satoshi_fontfamily reveal custom_zoom_in" style="margin-bottom: 0;">
                                                        @foreach (['Personalized nutrition plan (adjustable).', 'Customized training program (home or gym).', 'Twice-monthly follow-ups via Zoom.', 'Responses to inquiries and solutions via WhatsApp within 48-72 hours.', 'Adaptive and personalized training plan (adjustable).', 'Tailored diet plan (adjustable).'] as $feature)
                                                            <x-home.package-feature>
                                                                {{ $feature }}
                                                            </x-home.package-feature>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <div class="col-xl-5">
                                                    <div class="pricing_plan_v2_button">
                                                        <div class="mb-4 reveal custom_zoom_in" style="background: linear-gradient(135deg, rgba(255, 107, 0, 0.1), rgba(255, 140, 0, 0.05)); border: 1px solid rgba(255, 107, 0, 0.3); border-radius: 12px; padding: 20px;">
                                                            <div style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid rgba(255, 107, 0, 0.2);">
                                                                <div class="d-flex align-items-baseline justify-content-between mb-1">
                                                                    <span class="color_white satoshi_fontfamily" style="font-size: 13px; font-weight: 500;">1 Month</span>
                                                                    <div class="d-flex align-items-baseline" style="gap: 4px;">
                                                                        <h3 class="color_orange mb-0" style="font-size: 24px; font-weight: 700;">555</h3>
                                                                        <span class="color_white satoshi_fontfamily" style="font-size: 14px; opacity: 0.7;">EGP</span>
                                                                    </div>
                                                                </div>
                                                                <p class="satoshi_fontfamily" style="font-size: 11px; color: rgba(255, 255, 255, 0.5); margin: 0; text-align: right;">185 EGP/month</p>
                                                            </div>
                                                            <div style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid rgba(255, 107, 0, 0.2);">
                                                                <div class="d-flex align-items-baseline justify-content-between mb-1">
                                                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                                                        <span class="color_white satoshi_fontfamily" style="font-size: 13px; font-weight: 500;">3 Months</span>
                                                                        <span style="background: #ff6b00; color: white; font-size: 10px; padding: 2px 8px; border-radius: 4px; font-weight: 600;">SAVE 16%</span>
                                                                    </div>
                                                                    <div class="d-flex align-items-baseline" style="gap: 4px;">
                                                                        <h3 class="color_orange mb-0" style="font-size: 24px; font-weight: 700;">1400</h3>
                                                                        <span class="color_white satoshi_fontfamily" style="font-size: 14px; opacity: 0.7;">EGP</span>
                                                                    </div>
                                                                </div>
                                                                <p class="satoshi_fontfamily" style="font-size: 11px; color: rgba(255, 255, 255, 0.5); margin: 0; text-align: right;">467 EGP/month</p>
                                                            </div>
                                                            <div>
                                                                <div class="d-flex align-items-baseline justify-content-between mb-1">
                                                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                                                        <span class="color_white satoshi_fontfamily" style="font-size: 13px; font-weight: 500;">6 Months</span>
                                                                        <span style="background: #ff6b00; color: white; font-size: 10px; padding: 2px 8px; border-radius: 4px; font-weight: 600;">SAVE 28%</span>
                                                                    </div>
                                                                    <div class="d-flex align-items-baseline" style="gap: 4px;">
                                                                        <h3 class="color_orange mb-0" style="font-size: 24px; font-weight: 700;">2400</h3>
                                                                        <span class="color_white satoshi_fontfamily" style="font-size: 14px; opacity: 0.7;">EGP</span>
                                                                    </div>
                                                                </div>
                                                                <p class="satoshi_fontfamily" style="font-size: 11px; color: rgba(255, 255, 255, 0.5); margin: 0; text-align: right;">400 EGP/month</p>
                                                            </div>
                                                        </div>
                                                        <div class="purches_btn reveal custom_fade_buttom">
                                                            <a href="pricing-plans-v2.html" class="orange_btn" style="width: 100%; border-radius: 8px;">
                                                                <span class="orenge_text orangeglow_btn" data-hover="Get Started">Get Started</span>
                                                                <span class="orenge_icon whiteglow_btn">
                                                                    <img src="{{ asset('/images/home/svgs/common_button_arrow.svg') }}" alt="common_button_arrow">
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- PREMIUM Plan -->
                                <div class="col-lg-12 col-sm-6 mb-4">
                                    <div class="pricing_plan_v2_box" style="border: 2px solid rgba(255, 255, 255, 0.1); border-radius: 16px; overflow: hidden; transition: all 0.3s ease;">
                                        <div class="pricing_plan_v2_head bg_white whiteglow" style="padding: 30px;">
                                            <h4 class="color_orange pb-2 reveal custom_zoom_in" style="font-size: 28px; font-weight: 700; margin-bottom: 8px;">PREMIUM</h4>
                                            <p class="satoshi_fontfamily reveal custom_zoom_in" style="color: #666; font-size: 14px; margin-bottom: 0;">Ultimate transformation package</p>
                                        </div>
                                        <div class="pricing_plan_v2_bottom bg_black" style="padding: 30px;">
                                            <div class="row">
                                                <div class="col-xl-7 mb-4 mb-xl-0">
                                                    <ul class="pricing_plan_v2_points satoshi_fontfamily reveal custom_zoom_in" style="margin-bottom: 0;">
                                                        @foreach (['Follow-up with a specialized doctor.', 'Personalized nutrition plan (adjustable).', 'Healthy recipes to help you achieve your goals while enjoying your meals.', 'Weekly follow-ups via Zoom.', 'Daily responses to inquiries and solutions via WhatsApp.', 'Medical monitoring of your vital measurements.'] as $feature)
                                                            <x-home.package-feature>
                                                                {{ $feature }}
                                                            </x-home.package-feature>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                <div class="col-xl-5">
                                                    <div class="pricing_plan_v2_button">
                                                        <div class="mb-4 reveal custom_zoom_in" style="background: rgba(255, 255, 255, 0.05); border-radius: 12px; padding: 20px;">
                                                            <div style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                                                <div class="d-flex align-items-baseline justify-content-between mb-1">
                                                                    <span class="color_white satoshi_fontfamily" style="font-size: 13px; font-weight: 500;">1 Month</span>
                                                                    <div class="d-flex align-items-baseline" style="gap: 4px;">
                                                                        <h3 class="color_orange mb-0" style="font-size: 24px; font-weight: 700;">800</h3>
                                                                        <span class="color_white satoshi_fontfamily" style="font-size: 14px; opacity: 0.7;">EGP</span>
                                                                    </div>
                                                                </div>
                                                                <p class="satoshi_fontfamily" style="font-size: 11px; color: rgba(255, 255, 255, 0.5); margin: 0; text-align: right;">267 EGP/month</p>
                                                            </div>
                                                            <div style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                                                <div class="d-flex align-items-baseline justify-content-between mb-1">
                                                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                                                        <span class="color_white satoshi_fontfamily" style="font-size: 13px; font-weight: 500;">3 Months</span>
                                                                        <span style="background: rgba(255, 107, 0, 0.15); color: #ff6b00; font-size: 10px; padding: 2px 8px; border-radius: 4px; font-weight: 600;">SAVE 13%</span>
                                                                    </div>
                                                                    <div class="d-flex align-items-baseline" style="gap: 4px;">
                                                                        <h3 class="color_orange mb-0" style="font-size: 24px; font-weight: 700;">2100</h3>
                                                                        <span class="color_white satoshi_fontfamily" style="font-size: 14px; opacity: 0.7;">EGP</span>
                                                                    </div>
                                                                </div>
                                                                <p class="satoshi_fontfamily" style="font-size: 11px; color: rgba(255, 255, 255, 0.5); margin: 0; text-align: right;">700 EGP/month</p>
                                                            </div>
                                                            <div>
                                                                <div class="d-flex align-items-baseline justify-content-between mb-1">
                                                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                                                        <span class="color_white satoshi_fontfamily" style="font-size: 13px; font-weight: 500;">6 Months</span>
                                                                        <span style="background: rgba(255, 107, 0, 0.15); color: #ff6b00; font-size: 10px; padding: 2px 8px; border-radius: 4px; font-weight: 600;">SAVE 19%</span>
                                                                    </div>
                                                                    <div class="d-flex align-items-baseline" style="gap: 4px;">
                                                                        <h3 class="color_orange mb-0" style="font-size: 24px; font-weight: 700;">3900</h3>
                                                                        <span class="color_white satoshi_fontfamily" style="font-size: 14px; opacity: 0.7;">EGP</span>
                                                                    </div>
                                                                </div>
                                                                <p class="satoshi_fontfamily" style="font-size: 11px; color: rgba(255, 255, 255, 0.5); margin: 0; text-align: right;">650 EGP/month</p>
                                                            </div>
                                                        </div>
                                                        <div class="purches_btn reveal custom_fade_buttom">
                                                            <a href="pricing-plans-v2.html" class="orange_btn" style="width: 100%; border-radius: 8px;">
                                                                <span class="orenge_text orangeglow_btn" data-hover="Get Started">Get Started</span>
                                                                <span class="orenge_icon whiteglow_btn">
                                                                    <img src="{{ asset('/images/home/svgs/common_button_arrow.svg') }}" alt="common_button_arrow">
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
