<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_settings', function (Blueprint $table) {
            $table->id();

            $table->boolean('alert_banner_active')->default(true);
            $table->json('alert_banner_text')->nullable();

            $table->boolean('navbar_show_brand_text')->default(true);
            $table->json('navbar_brand_text')->nullable();
            $table->boolean('nav_show_services')->default(true);
            $table->boolean('nav_show_steps')->default(true);
            $table->boolean('nav_show_about')->default(true);
            $table->boolean('nav_show_comparison')->default(true);
            $table->boolean('nav_show_reviews')->default(true);
            $table->boolean('nav_show_faq')->default(true);
            $table->boolean('nav_show_callback')->default(true);

            $table->string('logo_image')->nullable();
            $table->string('phone_display')->default('+20 100 000 0000');
            $table->string('phone_href')->default('+201000000000');

            $table->json('hero_badge_text')->nullable();
            $table->json('hero_headline')->nullable();
            $table->json('hero_subheadline')->nullable();
            $table->json('hero_cta_label')->nullable();
            $table->json('hero_secondary_cta_label')->nullable();
            $table->string('hero_image')->nullable();

            $table->decimal('rating_value', 2, 1)->default(4.9);
            $table->unsignedInteger('rating_count')->default(512);

            $table->json('about_eyebrow')->nullable();
            $table->json('about_heading')->nullable();
            $table->string('about_owner_name')->default('صدام');
            $table->string('about_owner_photo')->nullable();
            $table->json('about_story')->nullable();

            $table->json('trust_1_title')->nullable();
            $table->json('trust_1_text')->nullable();
            $table->json('trust_2_title')->nullable();
            $table->json('trust_2_text')->nullable();
            $table->json('trust_3_title')->nullable();
            $table->json('trust_3_text')->nullable();
            $table->json('trust_4_title')->nullable();
            $table->json('trust_4_text')->nullable();

            $table->json('services_eyebrow')->nullable();
            $table->json('services_heading')->nullable();
            $table->json('services_subheading')->nullable();

            $table->json('steps_eyebrow')->nullable();
            $table->json('steps_heading')->nullable();

            $table->json('comparison_eyebrow')->nullable();
            $table->json('comparison_heading')->nullable();
            $table->json('comparison_subheading')->nullable();

            $table->json('reviews_eyebrow')->nullable();
            $table->json('reviews_heading')->nullable();

            $table->json('faq_eyebrow')->nullable();
            $table->json('faq_heading')->nullable();

            $table->json('callback_eyebrow')->nullable();
            $table->json('callback_heading')->nullable();
            $table->json('callback_subtext')->nullable();

            $table->json('company_name')->nullable();
            $table->json('footer_description')->nullable();
            $table->string('company_address')->nullable();
            $table->string('company_email')->nullable();
            $table->json('certifications_text')->nullable();
            $table->string('impressum_url')->nullable();
            $table->string('privacy_url')->nullable();
            $table->string('terms_url')->nullable();

            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('youtube_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_settings');
    }
};
