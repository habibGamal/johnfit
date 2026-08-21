<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => '<h2>About John Fit</h2><p>John Fit helps clients stay consistent with structured workout and nutrition plans built around real goals and trackable progress.</p>',
                'is_active' => true,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '<h2>Privacy Policy</h2><p>We only use your data to deliver coaching services, improve your fitness experience, and provide support. We never sell your personal data.</p>',
                'is_active' => true,
            ],
            [
                'title' => 'Return Policy',
                'slug' => 'return-policy',
                'content' => '<h2>Return Policy</h2><p>If a plan was purchased in error, contact us quickly and we will review the request according to our refund terms.</p>',
                'is_active' => true,
            ],
            [
                'title' => 'Replacement Policy',
                'slug' => 'replacement-policy',
                'content' => '<h2>Replacement Policy</h2><p>In case of plan or content issues, we can replace the assigned plan with a corrected one after review.</p>',
                'is_active' => true,
            ],
            [
                'title' => 'Delivery Policy',
                'slug' => 'delivery-policy',
                'content' => '<h2>Delivery Policy</h2><p>Digital workout and meal plans are delivered through your account dashboard after assignment.</p>',
                'is_active' => true,
            ],
            [
                'title' => 'Shipping Policy',
                'slug' => 'shipping-policy',
                'content' => '<h2>Shipping Policy</h2><p>John Fit services are provided digitally. No physical shipment is required unless explicitly stated.</p>',
                'is_active' => true,
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'content' => '<h2>Terms of Service</h2><p>By using John Fit, you agree to follow plan usage guidelines and maintain account security for your own progress tracking.</p>',
                'is_active' => true,
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => '<h2>Contact Us</h2><p>Need help? Reach us at coach.john.info@gmail.com or call +20 12 25390914 for support.</p>',
                'is_active' => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::firstOrCreate(
                ['slug' => $page['slug']],
                $page,
            );
        }
    }
}
