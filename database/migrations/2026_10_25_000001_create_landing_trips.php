<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('landing_trips', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('origin');
            $table->string('destination');
            $table->string('status')->default('received');
            $table->date('departure_date')->nullable();
            $table->date('estimated_delivery')->nullable();
            $table->timestamps();
        });
        Schema::create('landing_trip_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('landing_trips')->cascadeOnDelete();
            $table->string('status');
            $table->timestamp('happened_at');
            $table->unique(['trip_id', 'status']);
        });
        Schema::table('landing_shipments', function (Blueprint $table) {
            $table->foreignId('trip_id')->nullable()->constrained('landing_trips')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('landing_shipments', fn (Blueprint $table) => $table->dropConstrainedForeignId('trip_id'));
        Schema::dropIfExists('landing_trip_events');
        Schema::dropIfExists('landing_trips');
    }
};
