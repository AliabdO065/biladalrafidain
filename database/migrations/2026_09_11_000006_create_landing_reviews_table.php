<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('author_name');
            $table->string('author_photo')->nullable();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->date('review_date')->nullable();
            $table->json('review_text');
            $table->boolean('is_placeholder')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_reviews');
    }
};
