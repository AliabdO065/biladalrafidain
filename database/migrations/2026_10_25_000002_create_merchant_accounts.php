<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20)->unique();
            $table->timestamps();
        });
        Schema::create('account_batches', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('file_hash', 64)->unique();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
        Schema::create('merchant_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('account_batches')->cascadeOnDelete();
            $table->foreignId('merchant_id')->constrained('merchants')->restrictOnDelete();
            $table->string('name');
            $table->string('phone', 20);
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3);
            $table->string('details', 500)->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('provider_message_id')->nullable();
            $table->string('last_error')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->unique(['batch_id', 'phone']);
        });
        if (! Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }
    }

    public function down(): void
    {
        // The shared queue may contain unrelated application jobs.
        Schema::dropIfExists('merchant_statements');
        Schema::dropIfExists('account_batches');
        Schema::dropIfExists('merchants');
    }
};
