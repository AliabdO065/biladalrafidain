<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // MySQL DDL is not transactional; also support resuming a partial run.
        if (! Schema::hasColumn('merchant_statements', 'source_account_key')) {
            Schema::table('merchant_statements', fn (Blueprint $table) => $table->string('source_account_key', 64)->nullable());
        }
        if (! $this->hasIndex('statements_batch_account_currency_unique')) {
            Schema::table('merchant_statements', fn (Blueprint $table) => $table->unique(['batch_id', 'source_account_key', 'currency'], 'statements_batch_account_currency_unique'));
        }
        // Install the replacement index first: MySQL also uses its batch_id
        // prefix to maintain the existing foreign key.
        if ($this->hasIndex('merchant_statements_batch_id_phone_unique')) {
            Schema::table('merchant_statements', fn (Blueprint $table) => $table->dropUnique(['batch_id', 'phone']));
        }
    }

    public function down(): void
    {
        if (! $this->hasIndex('statements_batch_reference_index')) {
            Schema::table('merchant_statements', fn (Blueprint $table) => $table->index('batch_id', 'statements_batch_reference_index'));
        }
        Schema::table('merchant_statements', function (Blueprint $table) {
            $table->dropUnique('statements_batch_account_currency_unique');
            $table->dropColumn('source_account_key');
        });
        // Do not reintroduce the old phone constraint: valid mixed-currency
        // statements can share a phone number and must remain available.
    }

    private function hasIndex(string $name): bool
    {
        $sqlite = DB::connection()->getDriverName() === 'sqlite';
        $rows = DB::select($sqlite ? "PRAGMA index_list('merchant_statements')" : 'SHOW INDEX FROM merchant_statements');
        foreach ($rows as $row) {
            if (($sqlite ? $row->name : $row->Key_name) === $name) {
                return true;
            }
        }

        return false;
    }
};
