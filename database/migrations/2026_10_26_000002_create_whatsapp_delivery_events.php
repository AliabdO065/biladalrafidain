<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_delivery_events', function (Blueprint $table) {
            $table->string('provider_id', 191)->primary();
            $table->string('status', 20);
            $table->string('error', 191)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_delivery_events');
    }
};
