<section class="BMI_calculater_sec" id="calculator">
    <div class="BMI_calculater_area">
        <div class="container-fluid p-0">
            <div class="row m-0 align-items-center">
                <div class="col-lg-6 bg_black">
                    <div class="BMI_calculater_form_area sec_padding">
                        <div class="row m-0">
                            <div class="col-12">
                                <div class="common_title_area BMI_calculater_form_title text-lg-start text-center">
                                    <h5
                                        class="satoshi_fontfamily fw_500 line_height_normal color_orange reveal custom_fade_top">
                                        Check Your Health</h5>
                                    <h3 class="pt-10 reveal custom_lightSpeedInLeft color_white">CALCULATE BMI
                                    </h3>
                                    <h4 class="d-none">hidden</h4>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="BMI_calculater_form pt-60">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="BMI_calculater_form_box reveal custom_zoom_in">
                                                <input type="text" placeholder="Height (cm)"
                                                    name="height" id="height"
                                                    class="satoshi_fontfamily fw_500 line_height_24 color_lightblack w-100"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="BMI_calculater_form_box reveal custom_zoom_in">
                                                <input type="text" placeholder="Weight (kg)"
                                                    name="weight" id="weight"
                                                    class="satoshi_fontfamily fw_500 line_height_24 color_lightblack w-100"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="BMI_calculater_form_box reveal custom_zoom_in">
                                                <input type="text" placeholder="Age"
                                                    name="age" id="age"
                                                    class="satoshi_fontfamily fw_500 line_height_24 color_lightblack w-100"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="BMI_calculater_form_box reveal custom_zoom_in">
                                                <select name="gender_type" id="gender"
                                                    class="form-select satoshi_fontfamily fw_500 line_height_24 color_lightblack w-100"
                                                    required>
                                                    <option value="" selected disabled>Gender</option>
                                                    <option value="">Male</option>
                                                    <option value="">Female</option>
                                                    <option value="">other</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="BMI_calculater_form_box reveal custom_zoom_in">
                                                <select name="gym_activity" id="activity-gym"
                                                    class="form-select satoshi_fontfamily fw_500 line_height_24 color_lightblack w-100"
                                                    required>
                                                    <option value="" selected disabled>Select an activity factor
                                                    </option>
                                                    <option value="">Fitness</option>
                                                    <option value="">GYM</option>
                                                    <option value="">Yoga</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 text-lg-start text-center">
                                            <button type="submit" onclick="calculate()"
                                                class="orange_btn border-0 p-0 bg_transparent reveal custom_fade_buttom">
                                                <span class="orenge_text whiteglow_btn" data-hover="Calculate">
                                                    Calculate
                                                </span>
                                                <span class="orenge_icon whiteglow_btn">
                                                    <img src="{{ asset('/images/home/svgs/common_button_arrow.svg') }}"
                                                        alt="common_button_arrow">
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="BMI_calculater_table sec_padding_top">
                        <div class="table-responsive">
                            <table class="table mb-0 reveal custom_zoom">
                                <thead>
                                    <tr class="calculater_table_title">
                                        <th scope="col" class=" ps-0">BMI</th>
                                        <th scope="col">WEIGHT STATUS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="satoshi_fontfamily fw_500 color_lightblack line_height_normal ps-0">
                                            Below 18.5</td>
                                        <td class="satoshi_fontfamily fw_500 color_lightblack line_height_normal">
                                            Underweight</td>
                                    </tr>
                                    <tr>
                                        <td class="satoshi_fontfamily fw_500 color_lightblack line_height_normal ps-0">
                                            18.5 - 24.9</td>
                                        <td class="satoshi_fontfamily fw_500 color_lightblack line_height_normal">
                                            Healthy</td>
                                    </tr>
                                    <tr>
                                        <td class="satoshi_fontfamily fw_500 color_lightblack line_height_normal ps-0">
                                            25.0 - 29.9</td>
                                        <td class="satoshi_fontfamily fw_500 color_lightblack line_height_normal">
                                            Overweight</td>
                                    </tr>
                                    <tr>
                                        <td
                                            class="satoshi_fontfamily fw_500 color_lightblack line_height_normal border-bottom-0 ps-0">
                                            30.0 - and Above</td>
                                        <td
                                            class="satoshi_fontfamily fw_500 color_lightblack line_height_normal border-bottom-0">
                                            Obese</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p
                            class="satoshi_fontfamily fw_500 color_lightblack line_height_normal pt-40 text-lg-start text-center reveal custom_fade_buttom">
                            <span class="satoshi_fontfamily_bold fw_700 color_black">BMR</span> metabolic rate /
                            <span class="satoshi_fontfamily_bold fw_700 color_black">BMI</span> body mass index
                        </p>
                        <p id="result">
                            Enter Your Data To Show Results
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    const calculate = () => {
        const height = document.getElementById('height').value;
        const weight = document.getElementById('weight').value;
        const age = document.getElementById('age').value;
        const bmi = weight / (height / 100) ** 2;
        const bmr = 10 * weight + 6.25 * height - 5 * age + 5;
        document.getElementById('result').innerHTML = `Your BMI is ${bmi.toFixed(2)} and your BMR is ${bmr.toFixed(2)}`;
    }
</script>
