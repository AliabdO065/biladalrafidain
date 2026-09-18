<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_comparisons', function (Blueprint $table) {
            $table->id();
            $table->json('criterion');
            $table->json('us_value');
            $table->boolean('us_is_positive')->default(true);
            $table->json('them_value');
            $table->boolean('them_is_positive')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_comparisons');
    }
};
