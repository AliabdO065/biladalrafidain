<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shipment_imports', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('file_hash', 64)->unique();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('shipments_created')->default(0);
            $table->unsignedInteger('shipments_skipped')->default(0);
            $table->unsignedInteger('trips_created')->default(0);
            $table->timestamps();
        });
        Schema::table('landing_shipments', function (Blueprint $table) {
            $table->foreignId('import_id')->nullable()->constrained('shipment_imports')->nullOnDelete();
            $table->string('source_fingerprint', 64)->nullable()->unique();
            $table->unsignedInteger('source_row')->nullable();
            $table->string('report_sequence')->nullable();
            $table->string('receipt_number')->nullable()->index();
            $table->date('receipt_date')->nullable();
            $table->string('factory_number')->nullable()->index();
            $table->string('merchant_number')->nullable()->index();
            $table->string('receiver_phone')->nullable()->index();
            $table->string('city')->nullable()->index();
            $table->unsignedInteger('packages_count')->nullable();
            $table->decimal('weight_kg', 15, 3)->nullable();
            $table->decimal('item_price', 18, 4)->nullable();
            foreach (['shipping_fee', 'paid_amount', 'balance', 'total'] as $field) {
                $table->decimal($field, 18, 2)->nullable();
            }
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('landing_shipments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('import_id');
            $table->dropUnique(['source_fingerprint']);
            foreach (['receipt_number', 'factory_number', 'merchant_number', 'receiver_phone', 'city'] as $field) {
                $table->dropIndex([$field]);
            }
            $table->dropColumn(['source_fingerprint', 'source_row', 'report_sequence', 'receipt_number', 'receipt_date', 'factory_number', 'merchant_number', 'receiver_phone', 'city', 'packages_count', 'weight_kg', 'item_price', 'shipping_fee', 'paid_amount', 'balance', 'total', 'notes']);
        });
        Schema::dropIfExists('shipment_imports');
    }
};
