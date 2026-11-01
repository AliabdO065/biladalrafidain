<?php

use App\Support\ShipmentAccountIdentity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('landing_shipments', function (Blueprint $table) {
            $table->string('account_key', 64)->nullable();
            $table->string('currency', 3)->nullable();
            $table->index(['account_key', 'currency']);
        });
        DB::table('landing_shipments')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $row) {
                DB::table('landing_shipments')->where('id', $row->id)->update(['account_key' => ShipmentAccountIdentity::key((array) $row)]);
            }
        });
        Schema::table('account_batches', function (Blueprint $table) {
            $table->string('source')->default('legacy');
            $table->json('source_filters')->nullable();
        });
        Schema::table('merchant_statements', function (Blueprint $table) {
            $table->unsignedInteger('shipment_count')->nullable();
            $table->decimal('shipping_fee', 20, 2)->nullable();
            $table->decimal('paid_amount', 20, 2)->nullable();
            $table->string('source_version', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('merchant_statements', fn (Blueprint $table) => $table->dropColumn(['shipment_count', 'shipping_fee', 'paid_amount', 'source_version']));
        Schema::table('account_batches', fn (Blueprint $table) => $table->dropColumn(['source', 'source_filters']));
        Schema::table('landing_shipments', function (Blueprint $table) {
            $table->dropIndex(['account_key', 'currency']);
            $table->dropColumn(['account_key', 'currency']);
        });
    }
};
