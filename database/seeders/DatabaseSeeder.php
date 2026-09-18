<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        if (env('SEED_ADMIN_PASSWORD')) {
            User::updateOrCreate(
                ['email' => 'Saddam@biladalrafidain.com'],
                ['name' => 'Saddam', 'password' => Hash::make(env('SEED_ADMIN_PASSWORD'))]
            );
        }

        $this->call([
            LandingSeeder::class,
        ]);


    }
}