<?php

namespace Database\Seeders;

use App\Enums\AssessmentType;
use App\Models\Assessment;
use Illuminate\Database\Seeder;

class AssessmentSeeder extends Seeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Assessment::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $questions = [
            [
                'question' => 'Select your age',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => '40-49'],
                    ['label' => '50-59'],
                    ['label' => '60-69'],
                    ['label' => '70+'],
                ],
                'order' => 1,
            ],
            [
                'question' => 'What is your main goal?',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'Lose weight'],
                    ['label' => 'Improve long-term health'],
                    ['label' => 'Increase energy'],
                    ['label' => 'Improve mobility'],
                ],
                'order' => 2,
            ],
            [
                'question' => 'What is your current body type?',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'Slim'],
                    ['label' => 'Regular'],
                    ['label' => 'Overweight'],
                ],
                'order' => 3,
            ],
            [
                'question' => 'What is your target body type?',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'A few pounds less'],
                    ['label' => 'Fit'],
                    ['label' => 'Sculpted'],
                ],
                'order' => 4,
            ],
            [
                'question' => 'Select areas to improve',
                'type' => AssessmentType::MultipleSelect,
                'options' => [
                    ['label' => 'Beer belly'],
                    ['label' => 'Narrow shoulders'],
                    ['label' => 'Chest'],
                    ['label' => 'Thin arms'],
                    ['label' => 'Slim legs'],
                ],
                'order' => 5,
            ],
            [
                'question' => 'How long since you\'ve felt good in your body?',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'Less than 1 year'],
                    ['label' => '1-3 years'],
                    ['label' => 'More than 3 years'],
                    ['label' => "I can't remember"],
                    ['label' => 'I feel good now'],
                ],
                'order' => 6,
            ],
            [
                'question' => 'What is your activity level?',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'Beginner'],
                    ['label' => 'Intermediate'],
                    ['label' => 'Advanced'],
                ],
                'order' => 7,
            ],
            [
                'question' => 'How often do you go for walks?',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'Almost every day'],
                    ['label' => '3-4 times per week'],
                    ['label' => '1-2 times per week'],
                    ['label' => 'Not at all'],
                ],
                'order' => 8,
            ],
            [
                'question' => 'What\'s your ideal daily walking duration?',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'Less than 10 mins'],
                    ['label' => '10-30 mins'],
                    ['label' => 'More than 30 mins'],
                    ['label' => 'Let the coach decide'],
                ],
                'order' => 9,
            ],
            [
                'question' => 'Do you have a sedentary lifestyle?',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'Yes'],
                    ['label' => 'No'],
                ],
                'order' => 10,
            ],
            [
                'question' => 'Do you have issues with any of the following?',
                'type' => AssessmentType::MultipleSelect,
                'options' => [
                    ['label' => 'Back / Knee / Joints'],
                    ['label' => 'None'],
                ],
                'order' => 11,
            ],
            [
                'question' => 'What are your food cravings?',
                'type' => AssessmentType::MultipleSelect,
                'options' => [
                    ['label' => 'Anything sweet'],
                    ['label' => 'Salty foods'],
                    ['label' => 'Soda'],
                    ['label' => 'None of the above'],
                ],
                'order' => 12,
            ],
            [
                'question' => 'How much water do you drink daily?',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'Only coffee, tea or soda'],
                    ['label' => 'Less than 2 glasses'],
                    ['label' => '2-6 glasses'],
                    ['label' => '7-10 glasses'],
                ],
                'order' => 13,
            ],
            [
                'question' => 'How often do you consume alcohol?',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'Every day'],
                    ['label' => 'A few days per week'],
                    ['label' => 'Once a week'],
                    ['label' => 'Rarely or never'],
                ],
                'order' => 14,
            ],
            [
                'question' => 'How\'s your energy throughout the day?',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'High and stable'],
                    ['label' => 'Some ups and downs'],
                    ['label' => 'Low and sluggish'],
                ],
                'order' => 15,
            ],
            [
                'question' => 'Is it harder to lose weight now?',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'No, it\'s about the same'],
                    ['label' => 'Some ups and downs'],
                    ['label' => 'Much harder'],
                ],
                'order' => 16,
            ],
            [
                'question' => 'How many hours of sleep do you get?',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'Fewer than 5 hours'],
                    ['label' => 'Between 5 and 6 hours'],
                    ['label' => 'Between 6 and 7 hours'],
                    ['label' => 'Between 7 and 8 hours'],
                    ['label' => 'Over 8 hours'],
                ],
                'order' => 17,
            ],
            [
                'question' => 'What is your height?',
                'type' => AssessmentType::Text,
                'options' => null,
                'order' => 18,
            ],
            [
                'question' => 'What is your current weight?',
                'type' => AssessmentType::Text,
                'options' => null,
                'order' => 19,
            ],
            [
                'question' => 'What is your desired weight?',
                'type' => AssessmentType::Text,
                'options' => null,
                'order' => 20,
            ],
            [
                'question' => 'How old are you?',
                'type' => AssessmentType::Text,
                'options' => null,
                'order' => 21,
            ],
        ];

        foreach ($questions as $question) {
            Assessment::create($question);
        }
    }
}
