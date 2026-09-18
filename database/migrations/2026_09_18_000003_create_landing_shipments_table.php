<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code')->unique();
            $table->string('sender_name')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->string('status')->default('received');
            $table->date('estimated_delivery')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_shipments');
    }
};
