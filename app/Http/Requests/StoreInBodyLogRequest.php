<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInBodyLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Core required measurements with realistic constraints
            'weight' => [
                'required',
                'numeric',
                'min:20',      // Minimum realistic body weight (kg)
                'max:350',     // Maximum realistic body weight (kg)
            ],
            'smm' => [
                'required',
                'numeric',
                'min:5',       // Minimum skeletal muscle mass (kg)
                'max:80',      // Maximum realistic SMM (kg)
            ],
            'pbf' => [
                'required',
                'numeric',
                'min:3',       // Essential body fat minimum (%)
                'max:60',      // Maximum realistic body fat (%)
            ],
            'bmi' => [
                'required',
                'numeric',
                'min:10',      // Minimum realistic BMI
                'max:60',      // Maximum realistic BMI
            ],
            'bmr' => [
                'required',
                'numeric',
                'min:800',     // Minimum realistic BMR (kcal)
                'max:4000',    // Maximum realistic BMR (kcal)
            ],

            // Optional extended measurements
            'body_water' => [
                'nullable',
                'numeric',
                'min:20',      // Minimum body water (L)
                'max:70',      // Maximum body water (L)
            ],
            'lean_body_mass' => [
                'nullable',
                'numeric',
                'min:25',      // Minimum LBM (kg)
                'max:120',     // Maximum LBM (kg)
            ],
            'visceral_fat' => [
                'nullable',
                'numeric',
                'min:1',       // Minimum visceral fat level
                'max:60',      // Maximum visceral fat level
            ],
            'waist_hip_ratio' => [
                'nullable',
                'numeric',
                'min:0.5',     // Minimum realistic WHR
                'max:1.5',     // Maximum realistic WHR
            ],

            // Measurement date - no future dates allowed
            'measured_at' => [
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:'.now()->subYears(5)->format('Y-m-d'), // Limit to last 5 years
            ],

            // Optional notes
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'weight.min' => 'Weight must be at least 20 kg.',
            'weight.max' => 'Weight cannot exceed 350 kg.',
            'smm.min' => 'Skeletal muscle mass must be at least 5 kg.',
            'smm.max' => 'Skeletal muscle mass cannot exceed 80 kg.',
            'pbf.min' => 'Body fat percentage must be at least 3% (essential fat).',
            'pbf.max' => 'Body fat percentage cannot exceed 60%.',
            'bmi.min' => 'BMI must be at least 10.',
            'bmi.max' => 'BMI cannot exceed 60.',
            'bmr.min' => 'Basal metabolic rate must be at least 800 kcal.',
            'bmr.max' => 'Basal metabolic rate cannot exceed 4000 kcal.',
            'measured_at.before_or_equal' => 'Measurement date cannot be in the future.',
            'measured_at.after_or_equal' => 'Measurement date cannot be more than 5 years ago.',
            'body_water.min' => 'Body water must be at least 20 liters.',
            'body_water.max' => 'Body water cannot exceed 70 liters.',
            'lean_body_mass.min' => 'Lean body mass must be at least 25 kg.',
            'lean_body_mass.max' => 'Lean body mass cannot exceed 120 kg.',
            'visceral_fat.min' => 'Visceral fat level must be at least 1.',
            'visceral_fat.max' => 'Visceral fat level cannot exceed 60.',
            'waist_hip_ratio.min' => 'Waist-hip ratio must be at least 0.5.',
            'waist_hip_ratio.max' => 'Waist-hip ratio cannot exceed 1.5.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'smm' => 'skeletal muscle mass',
            'pbf' => 'body fat percentage',
            'bmi' => 'body mass index',
            'bmr' => 'basal metabolic rate',
            'body_water' => 'body water',
            'lean_body_mass' => 'lean body mass',
            'visceral_fat' => 'visceral fat level',
            'waist_hip_ratio' => 'waist-hip ratio',
            'measured_at' => 'measurement date',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize decimal inputs
        $this->merge([
            'weight' => $this->normalizeDecimal($this->weight),
            'smm' => $this->normalizeDecimal($this->smm),
            'pbf' => $this->normalizeDecimal($this->pbf),
            'bmi' => $this->normalizeDecimal($this->bmi),
            'bmr' => $this->normalizeDecimal($this->bmr),
            'body_water' => $this->normalizeDecimal($this->body_water),
            'lean_body_mass' => $this->normalizeDecimal($this->lean_body_mass),
            'visceral_fat' => $this->normalizeDecimal($this->visceral_fat),
            'waist_hip_ratio' => $this->normalizeDecimal($this->waist_hip_ratio),
        ]);
    }

    /**
     * Normalize a decimal value by converting comma to dot.
     */
    private function normalizeDecimal(mixed $value): mixed
    {
        if (is_string($value)) {
            return str_replace(',', '.', $value);
        }

        return $value;
    }
}
