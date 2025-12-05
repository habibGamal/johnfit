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
                                    <h3 class="pt-10 reveal custom_lightSpeedInLeft color_white">CALORIE CALCULATOR
                                    </h3>
                                    <h4 class="d-none">hidden</h4>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="BMI_calculater_form pt-60">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="BMI_calculater_form_box reveal custom_zoom_in">
                                                <input type="number" placeholder="Height (cm)" name="height" id="height"
                                                    class="satoshi_fontfamily fw_500 line_height_24 color_lightblack w-100"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="BMI_calculater_form_box reveal custom_zoom_in">
                                                <input type="number" placeholder="Weight (kg)" name="weight" id="weight"
                                                    class="satoshi_fontfamily fw_500 line_height_24 color_lightblack w-100"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="BMI_calculater_form_box reveal custom_zoom_in">
                                                <input type="number" placeholder="Age" name="age" id="age"
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
                                                    <option value="male">Male</option>
                                                    <option value="female">Female</option>
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
                                                    <option value="1">Basal Metabolic Rate (BMR)</option>
                                                    <option value="1.2">Sedentary: little or no exercise</option>
                                                    <option value="1.375">Light: exercise 1-3 times/week</option>
                                                    <option value="1.55">Moderate: exercise 4-5 times/week</option>
                                                    <option value="1.725">Active: daily exercise or intense exercise 3-4
                                                        times/week</option>
                                                    <option value="1.9">Very Active: intense exercise 6-7 times/week
                                                    </option>
                                                    <option value="2.0">Extra Active: very intense exercise daily, or
                                                        physical job</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 text-lg-start text-center">
                                            <button type="button" onclick="calculateCalories()"
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
                        <div class="table-responsive" id="result-container" style="display: none;">
                            <table class="table mb-0 reveal custom_zoom">
                                <thead>
                                    <tr class="calculater_table_title">
                                        <th scope="col" class=" ps-0">GOAL</th>
                                        <th scope="col">CALORIES / DAY</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="satoshi_fontfamily fw_500 color_lightblack line_height_normal ps-0">
                                            Maintain weight</td>
                                        <td class="satoshi_fontfamily fw_500 color_lightblack line_height_normal"
                                            id="maintain-cals">
                                            -</td>
                                    </tr>
                                    <tr>
                                        <td class="satoshi_fontfamily fw_500 color_lightblack line_height_normal ps-0">
                                            Mild weight loss <br><span style="font-size: 0.8em; color: #666;">(0.25
                                                kg/week)</span></td>
                                        <td class="satoshi_fontfamily fw_500 color_lightblack line_height_normal"
                                            id="mild-loss-cals">
                                            -</td>
                                    </tr>
                                    <tr>
                                        <td class="satoshi_fontfamily fw_500 color_lightblack line_height_normal ps-0">
                                            Weight loss <br><span style="font-size: 0.8em; color: #666;">(0.5
                                                kg/week)</span></td>
                                        <td class="satoshi_fontfamily fw_500 color_lightblack line_height_normal"
                                            id="loss-cals">
                                            -</td>
                                    </tr>
                                    <tr>
                                        <td
                                            class="satoshi_fontfamily fw_500 color_lightblack line_height_normal border-bottom-0 ps-0">
                                            Extreme weight loss <br><span style="font-size: 0.8em; color: #666;">(1
                                                kg/week)</span></td>
                                        <td class="satoshi_fontfamily fw_500 color_lightblack line_height_normal border-bottom-0"
                                            id="extreme-loss-cals">
                                            -</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="initial-message">
                            <p
                                class="satoshi_fontfamily fw_500 color_lightblack line_height_normal pt-40 text-lg-start text-center reveal custom_fade_buttom">
                                <span class="satoshi_fontfamily_bold fw_700 color_black">BMR</span> Basal Metabolic Rate
                                /
                                <span class="satoshi_fontfamily_bold fw_700 color_black">TDEE</span> Total Daily Energy
                                Expenditure
                            </p>
                            <p id="result-placeholder">
                                Enter Your Data To Show Results
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    const calculateCalories = () => {
        const height = parseFloat(document.getElementById('height').value);
        const weight = parseFloat(document.getElementById('weight').value);
        const age = parseFloat(document.getElementById('age').value);
        const gender = document.getElementById('gender').value;
        const activity = parseFloat(document.getElementById('activity-gym').value);

        if (!height || !weight || !age || !gender || !activity) {
            alert("Please fill in all fields.");
            return;
        }

        // Mifflin-St Jeor Equation
        let bmr;
        if (gender === 'male') {
            bmr = (10 * weight) + (6.25 * height) - (5 * age) + 5;
        } else {
            bmr = (10 * weight) + (6.25 * height) - (5 * age) - 161;
        }

        const tdee = bmr * activity;

        // Calorie Deficits
        const maintain = Math.round(tdee);
        const mildLoss = Math.round(tdee - 250);
        const loss = Math.round(tdee - 500);
        const extremeLoss = Math.round(tdee - 1000);

        // Update UI
        document.getElementById('maintain-cals').innerText = `${maintain} Calories/day`;
        document.getElementById('mild-loss-cals').innerText = `${mildLoss} Calories/day`;
        document.getElementById('loss-cals').innerText = `${loss} Calories/day`;
        document.getElementById('extreme-loss-cals').innerText = `${extremeLoss} Calories/day`;

        document.getElementById('result-container').style.display = 'block';
        document.getElementById('initial-message').style.display = 'none';
    }
</script>