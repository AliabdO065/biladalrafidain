<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factory_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone', 30);
            $table->string('city', 100)->nullable();
            $table->string('activity', 191)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->string('review_note', 500)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('catalog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar', 100);
            $table->string('name_en', 100);
            $table->foreignId('parent_id')->nullable()->constrained('catalog_categories')->restrictOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        Schema::create('catalog_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factory_id')->constrained('factory_accounts')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('catalog_categories')->restrictOnDelete();
            $table->string('kind', 10)->default('product');
            $table->string('title', 191);
            $table->text('description');
            $table->decimal('price', 15, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->unsignedInteger('minimum_order')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->string('review_note', 500)->nullable();
            $table->timestamps();
            $table->index(['factory_id', 'status']);
            $table->index(['category_id', 'status']);
        });
        Schema::create('catalog_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('catalog_items')->cascadeOnDelete();
            $table->string('path')->unique();
            $table->unsignedInteger('bytes');
            $table->timestamps();
        });
        foreach ([['ملابس ومنسوجات', 'Clothing & textiles'], ['أثاث ومفروشات', 'Furniture & furnishings'], ['أدوات منزلية', 'Homeware'], ['منتجات صناعية', 'Industrial products'], ['أخرى', 'Other']] as $names) {
            \Illuminate\Support\Facades\DB::table('catalog_categories')->insert(['name_ar' => $names[0], 'name_en' => $names[1], 'active' => true, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        foreach (['catalog_photos', 'catalog_items', 'catalog_categories', 'factory_accounts'] as $table) {
            if ($table === 'catalog_categories') {
                \Illuminate\Support\Facades\DB::table($table)->whereNotNull('parent_id')->update(['parent_id' => null]);
            }
            Schema::dropIfExists($table);
        }
    }
};
