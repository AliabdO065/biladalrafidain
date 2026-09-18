<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingSetting extends Model
{
    use HasFactory, HasTranslations;

    protected $translatable = [
        'alert_banner_text', 'hero_headline', 'hero_subheadline', 'hero_cta_label',
        'hero_badge_text', 'hero_secondary_cta_label',
        'about_story', 'about_eyebrow', 'about_heading',
        'trust_1_title', 'trust_1_text', 'trust_2_title', 'trust_2_text',
        'trust_3_title', 'trust_3_text', 'trust_4_title', 'trust_4_text',
        'company_name', 'certifications_text',
        'services_eyebrow', 'services_heading', 'services_subheading',
        'steps_eyebrow', 'steps_heading',
        'comparison_eyebrow', 'comparison_heading', 'comparison_subheading',
        'reviews_eyebrow', 'reviews_heading',
        'faq_eyebrow', 'faq_heading',
        'callback_eyebrow', 'callback_heading', 'callback_subtext',
        'navbar_brand_text', 'footer_description',
    ];

    protected $fillable = [
        'alert_banner_active', 'alert_banner_text',
        'navbar_show_brand_text', 'navbar_brand_text', 'footer_description',
        'nav_show_services', 'nav_show_steps', 'nav_show_about',
        'nav_show_comparison', 'nav_show_reviews', 'nav_show_faq', 'nav_show_callback',
        'nav_show_tracking', 'nav_show_policies',
        'logo_image', 'phone_display', 'phone_href',
        'hero_headline', 'hero_subheadline', 'hero_cta_label', 'hero_image', 'hero_badge_text', 'hero_secondary_cta_label',
        'rating_value', 'rating_count',
        'about_owner_name', 'about_owner_photo', 'about_story', 'about_eyebrow', 'about_heading',
        'trust_1_title', 'trust_1_text',
        'trust_2_title', 'trust_2_text',
        'trust_3_title', 'trust_3_text',
        'trust_4_title', 'trust_4_text',
        'company_name', 'company_address', 'company_email', 'certifications_text',
        'impressum_url', 'privacy_url', 'terms_url',
        'facebook_url', 'instagram_url', 'twitter_url', 'youtube_url', 'whatsapp_url',
        'services_eyebrow', 'services_heading', 'services_subheading',
        'steps_eyebrow', 'steps_heading',
        'comparison_eyebrow', 'comparison_heading', 'comparison_subheading',
        'reviews_eyebrow', 'reviews_heading',
        'faq_eyebrow', 'faq_heading',
        'callback_eyebrow', 'callback_heading', 'callback_subtext',
    ];

    protected $casts = [
        'alert_banner_active' => 'boolean',
        'navbar_show_brand_text' => 'boolean',
        'nav_show_services' => 'boolean',
        'nav_show_steps' => 'boolean',
        'nav_show_about' => 'boolean',
        'nav_show_comparison' => 'boolean',
        'nav_show_reviews' => 'boolean',
        'nav_show_faq' => 'boolean',
        'nav_show_callback' => 'boolean',
        'nav_show_tracking' => 'boolean',
        'nav_show_policies' => 'boolean',
        'rating_value' => 'float',
        'rating_count' => 'integer',
    ];
}
