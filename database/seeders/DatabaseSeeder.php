<?php

namespace Database\Seeders;

use App\Models\ContentItem;
use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        SiteSetting::updateOrCreate(
            ['id' => 1],
            [
                'company_name' => 'Foodservice Product Showcase',
                'tagline' => 'Foodservice Equipment',
                'logo_path' => null,
                'email' => 'service@example.com',
                'phone' => '(02) 8-812-3574',
                'mobile' => '09178439375',
                'address' => '#8005 G/F Harmonia Bldg., Ortigas Ave. cor. Wilson St., Greenhills, San Juan',
                'map_embed_url' => 'https://www.google.com/maps?q=Greenhills%20San%20Juan%20Metro%20Manila&output=embed',
            ]
        );

        $pages = [
            ['home', 'HOME', 'Foodservice Product Showcase', 'Foodservice Product Showcase', 'Foodservice Equipment', 'Built for food and beverage operations that need dependable equipment, reliable brands, and responsive after-sales support.'],
            ['about-us', 'ABOUT US', 'About Us', 'About Us', 'A curated product showcase for food and beverage equipment, built for stores, cafes, kiosks, and retail service counters.', 'Explore practical equipment solutions for refrigeration, beverage service, display, preparation, and daily foodservice operations.'],
            ['partners', 'PARTNERS', 'Partners', 'Our Business Partners', 'We are proud to offer the brands known in the market for reliability and the ability to customize according to your business needs and style.', null],
            ['brands', 'BRANDS', 'Brands', 'Brand Partners', 'We are proud to offer the brands known in the market for reliability and the ability to customize according to your business needs and style.', null],
            ['products', 'PRODUCTS', 'Products', 'Our Products', 'Browse a selection of foodservice equipment for beverage, refrigeration, display, preparation, and service operations.', null],
            ['services', 'SERVICES', 'Services', 'Services', 'With the number of machines we have and the number of branches our partners have nationwide we make sure to provide top notch services.', null],
            ['contact-us', 'CONTACT US', 'Contact Us', 'Connect With Us', 'Send us a message or visit our office for equipment inquiries, after-sales support, and partnership concerns.', null],
        ];

        foreach ($pages as $order => [$slug, $nav, $title, $section, $summary, $body]) {
            Page::updateOrCreate(
                ['slug' => $slug],
                [
                    'nav_label' => $nav,
                    'title' => $title,
                    'section_title' => $section,
                    'summary' => $summary,
                    'body' => $body,
                    'button_label' => $slug === 'about-us' ? 'Read More...' : null,
                    'button_url' => $slug === 'about-us' ? '/about-us' : null,
                    'sort_order' => $order,
                    'is_published' => true,
                ]
            );
        }

        $items = [
            ['hero_slide', 'Foodservice Product Showcase', 'Foodservice Equipment', 'Reliable equipment for food, beverage, refrigeration, and service operations.', 1],
            ['hero_slide', 'Trainings', 'Technical Support', 'Hands-on support and training for partner branches and foodservice teams.', 2],
            ['hero_slide', 'Special Events', 'Store Set-Up', 'Project support for launches, store openings, and brand activations.', 3],
            ['partner', '7-Eleven', 'Convenience Store', 'A national retail partner using dependable foodservice and beverage equipment.', 1],
            ['partner', 'All Day', 'Convenience Store', 'Retail location support for beverage and food equipment operations.', 2],
            ['partner', 'Lawson', 'Convenience Store', 'Branch-ready foodservice equipment and after-sales support.', 3],
            ['brand', 'Berjaya', 'Chillers & Warmers', 'Quality, variety, and reliability for commercial foodservice.', 1],
            ['brand', 'BUNN', 'Beverage Equipment', 'Trusted brewing and beverage systems since 1840.', 2],
            ['brand', 'Donper', 'Soft Serve & Frozen Drink Machines', 'Equipment for soft-serve, frozen drinks, and desserts.', 3],
            ['brand', 'Flymax-Coffee', 'Automatic Coffee Dispensing Machine', 'Coffee dispensing systems for high-volume service.', 4],
            ['product', 'Upright Chiller', 'Commercial Refrigeration', 'Heavy-duty refrigeration cabinet for food and beverage storage.', 1],
            ['product', 'Display Freezer', 'Merchandising Equipment', 'Glass-door display unit for retail and front-of-house spaces.', 2],
            ['product', 'Nitron Dispenser', 'Beverage Equipment', 'High-output dispenser for busy beverage stations.', 3],
            ['service', 'Preventive Maintenance', 'On-site Support', 'Scheduled inspections to keep equipment running reliably.', 1],
            ['service', 'Equipment Repair', 'Technical Service', 'Troubleshooting and repair for foodservice and refrigeration units.', 2],
            ['service', 'Installation & Training', 'Operations Support', 'Setup, commissioning, and handover training for branch teams.', 3],
            ['contact', 'Email', 'service@example.com', 'service@example.com', 1],
            ['contact', 'Office', '#8005 G/F Harmonia Bldg., Ortigas Ave. cor. Wilson St., Greenhills, San Juan', '#8005 G/F Harmonia Bldg., Ortigas Ave. cor. Wilson St., Greenhills, San Juan', 2],
            ['contact', 'Phone', '(02) 8-812-3574', '(02) 8-812-3574', 3],
            ['vision', 'Requirement-Fit Equipment', null, 'We assist you with selecting the right equipment for your requirement, provide a competitively priced top quality equipment that meet specific customer quality assurance specifications.', 1],
            ['vision', 'Service Solutions', null, 'We always provide first-rate service solutions to address your needs and maximize your equipment and machine capabilities.', 2],
            ['vision', 'Business Relations', null, 'We value and integrate good relations with you and principals to support your business growth and development.', 3],
            ['vision', 'Technical Support', null, 'Our knowledgeable technical support specialists at our disposal are promptly deployed to assist in your needs wherever your business is in the country.', 4],
            ['vision', 'Hospitality Selection', null, 'We offer you a wide selection of food service equipment to cater to the hospitality industry.', 5],
            ['gallery', 'Store Set-Up No. 1', 'Store Set-Up', 'Convenience store equipment setup and installation.', 1],
            ['gallery', 'Store Set-Up No. 2', 'Store Set-Up', 'Foodservice equipment layout for retail operations.', 2],
            ['gallery', 'Store Set-Up No. 3', 'Store Set-Up', 'Beverage and display equipment branch setup.', 3],
            ['gallery', 'Store Set-Up No. 4', 'Store Set-Up', 'Hot food display and store equipment deployment.', 4],
            ['gallery', 'Store Set-Up No. 5', 'Store Set-Up', 'Commercial refrigeration setup and support.', 5],
            ['gallery', 'Store Set-Up No. 6', 'Store Set-Up', 'Full equipment line installation for branch rollout.', 6],
            ['gallery', 'Store Set-Up No. 7', 'Store Set-Up', 'Counter and front-of-house equipment setup.', 7],
            ['gallery', 'Store Set-Up No. 8', 'Store Set-Up', 'Storefront and retail foodservice project.', 8],
            ['gallery', 'Store Set-Up No. 9', 'Store Set-Up', 'Retail beverage and chiller installation.', 9],
            ['gallery', 'Store Set-Up No. 10', 'Store Set-Up', 'Convenience store project completion.', 10],
            ['contact_cta', 'Get in Touch with Us!', 'Contact us today and let us know how we can assist you!', 'Our team is ready to help with product inquiries, equipment recommendations, maintenance planning, installations, and support for foodservice operations of any size.', 1],
            ['review', 'Sofia', 'Unknown', '"The equipment selection and service guidance helped us keep our store operations running smoothly. The team was responsive, practical, and easy to work with from inquiry to after-sales support."', 1],
            ['review', 'Andrew', 'Unknown', '"From product selection to installation support, the experience was efficient and reliable. It made daily foodservice operations easier for our branch team."', 2],
        ];

        foreach ($items as [$type, $title, $subtitle, $description, $order]) {
            ContentItem::updateOrCreate(
                ['type' => $type, 'title' => $title],
                [
                    'subtitle' => $subtitle,
                    'description' => $description,
                    'sort_order' => $order,
                    'is_published' => true,
                ]
            );
        }
    }
}
