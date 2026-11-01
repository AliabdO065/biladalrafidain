<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('languages')->whereNotIn('code', ['ar', 'en'])->update(['is_enabled' => false, 'is_default' => false, 'updated_at' => now()]);
        foreach (['ar' => ['Arabic', 'العربية'], 'en' => ['English', 'English']] as $code => [$name, $native]) {
            DB::table('languages')->insertOrIgnore([
                'code' => $code, 'name' => $name, 'native_name' => $native, 'is_enabled' => true,
                'is_default' => false, 'sort_order' => $code === 'ar' ? 1 : 2, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        DB::table('languages')->whereIn('code', ['ar', 'en'])->update(['is_enabled' => true, 'updated_at' => now()]);
        if (! DB::table('languages')->whereIn('code', ['ar', 'en'])->where('is_default', true)->exists()) {
            DB::table('languages')->where('code', 'ar')->update(['is_default' => true]);
        }
    }

    public function down(): void
    {
        // Keep language preferences and historical translations when rolling back.
    }
};
