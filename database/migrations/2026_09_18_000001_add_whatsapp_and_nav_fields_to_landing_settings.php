<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_settings', function (Blueprint $table) {
            $table->string('whatsapp_url')->nullable()->after('youtube_url');
            $table->boolean('nav_show_tracking')->default(true)->after('nav_show_callback');
            $table->boolean('nav_show_policies')->default(true)->after('nav_show_tracking');
        });
    }

    public function down(): void
    {
        Schema::table('landing_settings', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_url', 'nav_show_tracking', 'nav_show_policies']);
        });
    }
};
