<?php

namespace Database\Seeders;

use App\Enums\AssessmentType;
use App\Models\Assessment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssessmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Assessment::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $questions = [
            [
                'question' => 'إيه هدفك الرئيسي دلوقتي؟',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'خسارة دهون 🔥'],
                    ['label' => 'بناء عضلات 💪'],
                    ['label' => 'تحسين اللياقة العامة 🏃'],
                    ['label' => 'خسارة دهون + بناء عضلات 🎯'],
                ],
                'order' => 1,
            ],
            [
                'question' => 'من قبل اشتغلت على جسمك بجدية؟',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'لأ، ده أول مرة 🆕'],
                    ['label' => 'اشتغلت بس وقفت ⏸️'],
                    ['label' => 'بشتغل بانتظام 💯'],
                    ['label' => 'أحياناً بدون نظام 😅'],
                ],
                'order' => 2,
            ],
            [
                'question' => 'عندك أي حالة صحية أو إصابة؟',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'لأ، بصحة كاملة ✅'],
                    ['label' => 'مشكلة ظهر / مفاصل 🦴'],
                    ['label' => 'ضغط / سكر / قلب 🏥'],
                    ['label' => 'في حاجة تانية 📝'],
                ],
                'order' => 3,
            ],
            [
                'question' => 'طبيعة شغلك إيه؟',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'مكتب طول اليوم 💻'],
                    ['label' => 'واقف / بتحرك 🚶'],
                    ['label' => 'مجهود جسدي عالي 🔨'],
                    ['label' => 'مش منتظم 🔄'],
                ],
                'order' => 4,
            ],
            [
                'question' => 'بتنام كام ساعة في اليوم؟',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'أقل من 5 ساعات 😴'],
                    ['label' => '5 لـ 6 ساعات 🌙'],
                    ['label' => '6 لـ 8 ساعات ⭐'],
                    ['label' => 'أكتر من 8 ساعات 🛏️'],
                ],
                'order' => 5,
            ],
            [
                'question' => 'مستوى الضغط النفسي عندك؟',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'منخفض 😊'],
                    ['label' => 'متوسط 😐'],
                    ['label' => 'عالي 😤'],
                    ['label' => 'متقلب 🎢'],
                ],
                'order' => 6,
            ],
            [
                'question' => 'بتاكل كام وجبة في اليوم؟',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'وجبة أو اتنين 😬'],
                    ['label' => '3 وجبات 🍽️'],
                    ['label' => '4-5 وجبات ✅'],
                    ['label' => 'ما بتاكلش بنظام 🤷'],
                ],
                'order' => 7,
            ],
            [
                'question' => 'علاقتك بالبروتين إيه؟',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'بعيد عنه 🚫'],
                    ['label' => 'أحياناً 😐'],
                    ['label' => 'في كل وجبة 💪'],
                    ['label' => 'مكملات بروتين 🥤'],
                ],
                'order' => 8,
            ],
            [
                'question' => 'بتشرب كام لتر ميه في اليوم؟',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'أقل من لتر 😬'],
                    ['label' => '1-2 لتر 💧'],
                    ['label' => '2-3 لتر 💧💧'],
                    ['label' => 'أكتر من 3 لتر 🏆'],
                ],
                'order' => 9,
            ],
            [
                'question' => 'في أكل بتتجنبه؟',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'لأ، بياكل كل حاجة 😋'],
                    ['label' => 'بتجنب اللاكتوز 🥛'],
                    ['label' => 'بتجنب الجلوتين 🌾'],
                    ['label' => 'في حاجات تانية 📝'],
                ],
                'order' => 10,
            ],
            [
                'question' => 'إيه مصادر البروتين المفضلة لك؟',
                'type' => AssessmentType::MultipleSelect,
                'options' => [
                    ['label' => 'فراخ 🍗'],
                    ['label' => 'كبدة بلدي 🥩'],
                    ['label' => 'لحم أحمر 🥩'],
                    ['label' => 'سلمون 🐟'],
                    ['label' => 'سمك 🐟'],
                    ['label' => 'تونة 🐟'],
                    ['label' => 'جمبري 🦐'],
                    ['label' => 'بيض 🥚'],
                    ['label' => 'جبنة قريش 🧀'],
                    ['label' => 'رومي 🦃'],
                    ['label' => 'رومي مدخن 🥓'],
                    ['label' => 'زبادي يوناني 🥣'],
                    ['label' => 'زبادي عادي 🥛'],
                    ['label' => 'جبنة فيتا 🧀'],
                ],
                'order' => 11,
            ],
            [
                'question' => 'إيه مصادر الكارب المفضلة لك؟',
                'type' => AssessmentType::MultipleSelect,
                'options' => [
                    ['label' => 'بطاطا 🍠'],
                    ['label' => 'بطاطس 🥔'],
                    ['label' => 'أرز أبيض 🍚'],
                    ['label' => 'أرز بسمتي 🍚'],
                    ['label' => 'عدس 🍲'],
                    ['label' => 'فاصوليا 🫘'],
                    ['label' => 'فول 🫘'],
                    ['label' => 'توست بني 🍞'],
                    ['label' => 'شوفان 🌾'],
                    ['label' => 'رايس كيك 🍘'],
                    ['label' => 'تورتيلا بني 🫓'],
                ],
                'order' => 12,
            ],
            [
                'question' => 'إيه مصادر الدهون الصحية المفضلة لك؟',
                'type' => AssessmentType::MultipleSelect,
                'options' => [
                    ['label' => 'فول سوداني 🥜'],
                    ['label' => 'لوز 🌰'],
                    ['label' => 'كاجو 🥜'],
                    ['label' => 'زبدة فول سوداني 🥜'],
                    ['label' => 'سمن بلدي 🧈'],
                    ['label' => 'زيت زيتون 🫒'],
                    ['label' => 'زيت جوز الهند 🥥'],
                    ['label' => 'أفوكادو 🥑'],
                    ['label' => 'سلمون 🐟'],
                ],
                'order' => 13,
            ],
            [
                'question' => 'كام يوم في الأسبوع تقدر تتمرن؟',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'يوم أو اتنين 📅'],
                    ['label' => '3 أيام 👍'],
                    ['label' => '4 أو 5 أيام 🔥'],
                    ['label' => 'كل يوم 🚀'],
                ],
                'order' => 14,
            ],
            [
                'question' => 'التمرين الواحد كام وقت؟',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'أقل من 30 دقيقة ⚡'],
                    ['label' => '30-45 دقيقة ⏱️'],
                    ['label' => 'ساعة ⏰'],
                    ['label' => 'أكتر من ساعة 🏋️'],
                ],
                'order' => 15,
            ],
            [
                'question' => 'بتتمرن فين؟',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'في الجيم 🏢'],
                    ['label' => 'بيت بدون معدات 🏠'],
                    ['label' => 'بيت عندي معدات 💪'],
                    ['label' => 'برة / جري 🌿'],
                ],
                'order' => 16,
            ],
            [
                'question' => 'إيه أكبر عائق بيمنعك؟',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'مش عارف أبدأ منين 🤷'],
                    ['label' => 'ضغط الشغل ⏳'],
                    ['label' => 'الانتظام 🔄'],
                    ['label' => 'معلومات غلط 😵'],
                ],
                'order' => 17,
            ],
            [
                'question' => 'مستعد تلتزم لمدة كام؟',
                'type' => AssessmentType::Select,
                'options' => [
                    ['label' => 'شهر 👀'],
                    ['label' => '3 شهور 📆'],
                    ['label' => '6 شهور 💪'],
                    ['label' => 'سنة أو أكتر 🏆'],
                ],
                'order' => 18,
            ],
        ];

        foreach ($questions as $question) {
            Assessment::create($question);
        }
    }
}
