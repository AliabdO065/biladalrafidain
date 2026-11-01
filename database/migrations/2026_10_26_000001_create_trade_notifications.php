<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trading_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('number', 100)->unique();
            $table->string('name');
            $table->string('phone', 20)->nullable()->index();
            $table->boolean('opt_in')->default(false);
            $table->boolean('phone_conflict')->default(false);
            $table->timestamps();
        });
        Schema::create('trade_imports', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 20);
            $table->string('file_hash', 64);
            $table->string('currency', 3);
            $table->json('payload')->nullable();
            $table->unsignedInteger('excluded')->default(0);
            $table->unsignedInteger('created_count')->default(0);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->unique(['kind', 'file_hash', 'currency']);
        });
        Schema::create('trade_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('source', 20);
            $table->string('reference', 100);
            $table->string('custom_reference', 100)->nullable();
            $table->date('occurred_at');
            $table->string('kind', 30);
            $table->foreignId('contact_id')->constrained('trading_contacts')->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3);
            $table->string('supplier')->nullable();
            $table->string('trip_reference', 100)->nullable();
            $table->string('description', 2000)->nullable();
            $table->string('fingerprint', 64);
            $table->foreignId('import_id')->constrained('trade_imports')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['source', 'reference']);
        });
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 30)->unique();
            $table->text('body');
            $table->string('provider_name', 100)->nullable();
            $table->string('language', 10)->default('ar');
            $table->boolean('auto_send')->default(false);
            $table->string('verified_hash', 64)->nullable();
            $table->timestamps();
        });
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->nullable()->constrained('trading_contacts')->restrictOnDelete();
            $table->string('phone', 20)->nullable()->index();
            $table->string('direction', 3)->default('out');
            $table->string('kind', 30);
            $table->string('source_type', 20)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('dedupe_key', 64)->nullable()->unique();
            $table->text('body')->nullable();
            $table->json('parameters')->nullable();
            $table->string('source_version', 64)->nullable();
            $table->string('provider_name', 100)->nullable();
            $table->string('language', 10)->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->string('provider_id', 191)->nullable()->unique();
            $table->string('error', 191)->nullable();
            $table->timestamp('provider_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['whatsapp_messages', 'notification_templates', 'trade_vouchers', 'trade_imports', 'trading_contacts'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
